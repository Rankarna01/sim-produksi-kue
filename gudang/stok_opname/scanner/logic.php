<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('scanner_opname');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. VERIFIKASI KODE PIN (Menggunakan tabel PIN yang sudah ada)
    if ($action === 'verify_pin') {
        $pin = $_POST['pin'] ?? '';
        
        // Cek apakah PIN ada, statusnya active, dan belum expired
        $stmt = $pdo->prepare("SELECT id FROM stok_opname_keys WHERE access_code = ? AND status = 'active' AND valid_until > NOW()");
        $stmt->execute([$pin]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Akses Diberikan!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kode PIN tidak valid atau sudah kadaluarsa!']);
        }
        exit;
    }

    // 2. INIT DATA (Tarik Barang untuk Dropdown Scanner)
    if ($action === 'init_data') {
        $materials = $pdo->query("SELECT id, material_name, sku_code, unit, stock FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'materials' => $materials]);
        exit;
    }

    // 3. SIMPAN HASIL OPNAME (Ke Tabel Khusus Gudang)
    if ($action === 'save_opname') {
        $drafts = json_decode($_POST['drafts'], true);
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($drafts)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data untuk disimpan!']); exit;
        }

        $pdo->beginTransaction();

        // A. Generate Nomor Opname & Waktu Aktual
        $opname_no = "SO-GDG-" . date('YmdHis') . "-" . rand(100,999);
        $opname_date = date('Y-m-d H:i:s');

        // B. Insert Header ke gudang_stok_opnames
        // Karena ada otorisasi PIN, kita anggap otomatis 'approved'
        $stmtHeader = $pdo->prepare("INSERT INTO gudang_stok_opnames (opname_no, opname_date, status, created_by) VALUES (?, ?, 'approved', ?)");
        $stmtHeader->execute([$opname_no, $opname_date, $user_id]);
        $opname_id = $pdo->lastInsertId();

        // C. Siapkan Statement Detail & Update Stok Master
        $stmtDetail = $pdo->prepare("INSERT INTO gudang_stok_opname_details (opname_id, material_id, system_stock, physical_stock, difference, notes) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Lock Update (FOR UPDATE) agar tidak ada pergeseran stok saat proses iterasi
        $stmtGetStock = $pdo->prepare("SELECT stock FROM materials_stocks WHERE id = ? FOR UPDATE");
        $stmtUpdStock = $pdo->prepare("UPDATE materials_stocks SET stock = ? WHERE id = ?");

        foreach ($drafts as $item) {
            $mat_id = $item['material_id'];
            $phys_qty = (float)$item['physical_stock'];
            $notes = $item['notes'] ?? '';

            // Ambil stok sistem paling update di detik eksekusi ini
            $stmtGetStock->execute([$mat_id]);
            $sys_qty = (float)$stmtGetStock->fetchColumn();

            // Hitung selisih
            $diff = $phys_qty - $sys_qty;

            // Simpan Histori ke gudang_stok_opname_details
            $stmtDetail->execute([$opname_id, $mat_id, $sys_qty, $phys_qty, $diff, $notes]);

            // Timpa / Update master stok menjadi stok fisik
            $stmtUpdStock->execute([$phys_qty, $mat_id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok Gudang berhasil disesuaikan berdasarkan fisik!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>