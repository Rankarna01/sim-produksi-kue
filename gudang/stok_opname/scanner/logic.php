<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

// Export/Download Template & Import CSV tidak butuh JSON Header
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // ==========================================
    // 1. EXPORT TEMPLATE BERDASARKAN RAK
    // ==========================================
    if ($action === 'download_template') {
        $rack_id = $_GET['rack_id'] ?? '';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_Opname_Gudang_' . date('Ymd') . '.csv');
        $output = fopen('php://output', 'w');
        
        // Header Kolom CSV
        fputcsv($output, ['SKU / Barcode', 'Nama Barang', 'Satuan', 'Stok Sistem Saat Ini', 'Stok Fisik Aktual (Isi Disini)', 'Catatan (Opsional)']);

        $where = "status = 'active'";
        $params = [];
        if (!empty($rack_id)) {
            $where .= " AND rack_id = ?";
            $params[] = $rack_id;
        }

        $stmt = $pdo->prepare("SELECT sku_code, material_name, unit, stock FROM materials_stocks WHERE $where ORDER BY material_name ASC");
        $stmt->execute($params);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Kolom Stok Fisik dan Catatan dibiarkan KOSONG agar diisi manual oleh staf gudang
            fputcsv($output, [$row['sku_code'], $row['material_name'], $row['unit'], $row['stock'], '', '']);
        }
        fclose($output);
        exit;
    }

    // ==========================================
    // 2. IMPORT CSV & KONVERSI KE DRAFT
    // ==========================================
    if ($action === 'import_csv') {
        header('Content-Type: application/json');
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            $header = fgetcsv($handle, 1000, ","); // Skip baris pertama (Header)
            
            $parsedData = [];
            $errors = [];

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Pastikan format kolom sesuai template (minimal 5 kolom terisi)
                if (count($data) >= 5) {
                    $sku = trim($data[0]);
                    $phys_stock = trim($data[4]); // Kolom "Stok Fisik Aktual"
                    $notes = isset($data[5]) ? trim($data[5]) : '';

                    // HANYA proses baris yang stok fisiknya benar-benar diisi oleh user
                    if ($phys_stock !== '') { 
                        $stmt = $pdo->prepare("SELECT id, material_name, unit, stock FROM materials_stocks WHERE sku_code = ? AND status = 'active'");
                        $stmt->execute([$sku]);
                        $mat = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($mat) {
                            $parsedData[] = [
                                'material_id' => $mat['id'],
                                'material_name' => $mat['material_name'],
                                'unit' => $mat['unit'],
                                'system_stock' => (float)$mat['stock'],
                                'physical_stock' => (float)$phys_stock,
                                'difference' => (float)$phys_stock - (float)$mat['stock'],
                                'notes' => $notes
                            ];
                        } else {
                            $errors[] = "SKU '$sku' tidak valid atau arsip.";
                        }
                    }
                }
            }
            fclose($handle);
            echo json_encode(['status' => 'success', 'data' => $parsedData, 'errors' => $errors]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membaca file CSV!']);
        }
        exit;
    }


    // ==========================================
    // 3. API STANDAR MENGGUNAKAN JSON HEADER
    // ==========================================
    header('Content-Type: application/json');

    // VERIFIKASI KODE PIN
    if ($action === 'verify_pin') {
        $pin = $_POST['pin'] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM stok_opname_keys WHERE access_code = ? AND status = 'active' AND valid_until > NOW()");
        $stmt->execute([$pin]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Akses Diberikan!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kode PIN tidak valid atau sudah kadaluarsa!']);
        }
        exit;
    }

    // INIT DATA BARANG & LOKASI RAK
    if ($action === 'init_data') {
        $materials = $pdo->query("SELECT id, material_name, sku_code, unit, stock FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $racks = $pdo->query("SELECT id, name FROM racks ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'materials' => $materials, 'racks' => $racks]);
        exit;
    }

    // SIMPAN HASIL OPNAME KE DATABASE (Final)
    if ($action === 'save_opname') {
        $drafts = json_decode($_POST['drafts'], true);
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($drafts)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada data untuk disimpan!']); exit;
        }

        $pdo->beginTransaction();

        $opname_no = "SO-GDG-" . date('YmdHis') . "-" . rand(100,999);
        $opname_date = date('Y-m-d H:i:s');

        $stmtHeader = $pdo->prepare("INSERT INTO gudang_stok_opnames (opname_no, opname_date, status, created_by) VALUES (?, ?, 'approved', ?)");
        $stmtHeader->execute([$opname_no, $opname_date, $user_id]);
        $opname_id = $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare("INSERT INTO gudang_stok_opname_details (opname_id, material_id, system_stock, physical_stock, difference, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtGetStock = $pdo->prepare("SELECT stock FROM materials_stocks WHERE id = ? FOR UPDATE");
        $stmtUpdStock = $pdo->prepare("UPDATE materials_stocks SET stock = ? WHERE id = ?");

        foreach ($drafts as $item) {
            $mat_id = $item['material_id'];
            $phys_qty = (float)$item['physical_stock'];
            $notes = $item['notes'] ?? '';

            $stmtGetStock->execute([$mat_id]);
            $sys_qty = (float)$stmtGetStock->fetchColumn();
            $diff = $phys_qty - $sys_qty;

            $stmtDetail->execute([$opname_id, $mat_id, $sys_qty, $phys_qty, $diff, $notes]);
            $stmtUpdStock->execute([$phys_qty, $mat_id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok Gudang berhasil disesuaikan berdasarkan fisik!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>