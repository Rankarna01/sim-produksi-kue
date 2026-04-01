<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. PROSES SCAN (AMBIL HEADER & LIST DETAIL PRODUK)
    if ($action === 'scan') {
        $barcode = trim($_POST['barcode']);
        if (empty($barcode)) {
            echo json_encode(['status' => 'error', 'message' => 'Barcode kosong!']);
            exit;
        }

        // A. Cari Data Header Produksi Berdasarkan Barcode
        // PERBAIKAN: Menambahkan kembali "JOIN warehouses w" yang sempat tertinggal
        $stmtHead = $pdo->prepare("
            SELECT p.id as prod_id, p.invoice_no, p.status, 
                   COALESCE(e.name, u.name) as karyawan, w.name as gudang
            FROM production_details d
            JOIN productions p ON d.production_id = p.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            JOIN warehouses w ON p.warehouse_id = w.id 
            WHERE d.barcode = ? 
            LIMIT 1
        ");
        $stmtHead->execute([$barcode]);
        $header = $stmtHead->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            echo json_encode(['status' => 'error', 'message' => "Struk dengan Barcode [{$barcode}] tidak ditemukan!"]);
            exit;
        }

        // Cek status sebelum memunculkan modal
        if ($header['status'] === 'masuk_gudang') {
            echo json_encode(['status' => 'warning', 'message' => "Struk ini SUDAH PERNAH divalidasi dan barang sudah di Gudang!"]);
            exit;
        }

        if ($header['status'] === 'ditolak') {
            echo json_encode(['status' => 'warning', 'message' => "Struk ini SEDANG DITOLAK. Menunggu revisi dari pihak Dapur."]);
            exit;
        }

        if ($header['status'] === 'expired') {
            echo json_encode(['status' => 'warning', 'message' => "Struk ini sudah kedaluwarsa (Expired)."]);
            exit;
        }

        // B. Cari Semua Detail Produk yang Berada di Bawah Invoice Tersebut
        $stmtDetail = $pdo->prepare("
            SELECT pr.name as produk, d.quantity
            FROM production_details d
            JOIN products pr ON d.product_id = pr.id
            WHERE d.production_id = ?
        ");
        $stmtDetail->execute([$header['prod_id']]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        // Jika status masih "pending", lempar data (Header + Details) ke modal konfirmasi
        echo json_encode([
            'status' => 'need_confirmation', 
            'header' => $header,
            'details' => $details
        ]);
        exit;
    }

    // 2. EKSEKUSI TOMBOL DARI MODAL (VALID / TOLAK)
    if ($action === 'execute_validasi') {
        $prod_id = $_POST['prod_id'];
        $status_baru = $_POST['status']; // 'masuk_gudang' atau 'ditolak'

        $update = $pdo->prepare("UPDATE productions SET status = ? WHERE id = ?");
        $update->execute([$status_baru, $prod_id]);

        $pesan = ($status_baru === 'masuk_gudang') ? "Barang Sesuai & Valid masuk Gudang!" : "Barang Ditolak! Dikembalikan ke Dapur untuk direvisi.";
        
        echo json_encode([
            'status' => 'success', 
            'message' => $pesan,
            'status_type' => $status_baru
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>