<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. PROSES SCAN (AMBIL HEADER & LIST DETAIL PRODUK TITIPAN)
    if ($action === 'scan') {
        $barcode = trim($_POST['barcode']);
        if (empty($barcode)) {
            echo json_encode(['status' => 'error', 'message' => 'Input kosong!']); exit;
        }

        // LOGIKA KECERDASAN SCANNER UNTUK BARANG TITIPAN (Format TTP-ymd-001)
        $invoice_no_to_search = $barcode; 
        $parts = explode('-', $barcode);
        
        // Jika formatnya TTP-260423-001-1 (ada 4 bagian dari scanner barcode tiap barang)
        if (count($parts) >= 4 && $parts[0] === 'TTP') {
            // Ambil invoice-nya saja: TTP-260423-001
            $invoice_no_to_search = $parts[0] . '-' . $parts[1] . '-' . $parts[2]; 
        }

        // A. Cari Data Header Titipan
        $stmtHead = $pdo->prepare("
            SELECT p.id as prod_id, p.invoice_no, p.status, 
                   COALESCE(e.name, u.name) as karyawan, w.name as gudang
            FROM titipan_productions p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            JOIN warehouses w ON p.warehouse_id = w.id 
            WHERE p.invoice_no = ?
            LIMIT 1
        ");
        $stmtHead->execute([$invoice_no_to_search]);
        $header = $stmtHead->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            echo json_encode(['status' => 'error', 'message' => "Data dengan Barcode / Invoice [{$barcode}] tidak ditemukan di sistem Titipan UMKM!"]);
            exit;
        }

        // Filter Status
        if ($header['status'] === 'received' || $header['status'] === 'masuk_gudang') {
            echo json_encode(['status' => 'warning', 'message' => "Invoice ini SUDAH PERNAH divalidasi dan barang titipan sudah di Gudang!"]); exit;
        }
        if ($header['status'] === 'ditolak') {
            echo json_encode(['status' => 'warning', 'message' => "Invoice ini SEDANG DITOLAK. Menunggu revisi."]); exit;
        }
        if ($header['status'] === 'dibatalkan' || $header['status'] === 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => "Invoice ini telah DIBATALKAN. Data sudah tidak berlaku."]); exit;
        }

        // B. Cari Detail Barang Titipan
        $stmtDetail = $pdo->prepare("
            SELECT b.nama_barang as produk, b.nama_umkm, d.quantity
            FROM titipan_production_details d
            JOIN barang_titipan b ON d.titipan_id = b.id
            WHERE d.titipan_production_id = ?
        ");
        $stmtDetail->execute([$header['prod_id']]);
        $details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'need_confirmation', 
            'header' => $header,
            'details' => $details
        ]);
        exit;
    }

    // 2. EKSEKUSI TOMBOL DARI MODAL (TERIMA / TOLAK)
    if ($action === 'execute_validasi') {
        $prod_id = $_POST['prod_id'];
        $status_baru = $_POST['status']; // 'received' atau 'ditolak'

        $update = $pdo->prepare("UPDATE titipan_productions SET status = ? WHERE id = ?");
        $update->execute([$status_baru, $prod_id]);

        $pesan = ($status_baru === 'received') ? "Barang Titipan Valid & Masuk Gudang!" : "Barang Titipan Ditolak! Dikembalikan untuk direvisi.";
        
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