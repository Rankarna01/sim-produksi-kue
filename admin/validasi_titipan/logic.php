<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. PROSES SCAN (AMBIL HEADER & LIST DETAIL PRODUK TITIPAN)
    if ($action === 'scan') {
        $barcode = trim($_POST['barcode'] ?? '');
        if (empty($barcode)) {
            echo json_encode(['status' => 'error', 'message' => 'Input kosong!']); exit;
        }

        $invoice_no_to_search = $barcode; 
        $parts = explode('-', $barcode);
        
        if (count($parts) >= 4 && $parts[0] === 'TTP') {
            $invoice_no_to_search = $parts[0] . '-' . $parts[1] . '-' . $parts[2]; 
        }

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

        if ($header['status'] === 'received' || $header['status'] === 'masuk_gudang') {
            echo json_encode(['status' => 'warning', 'message' => "Invoice ini SUDAH PERNAH divalidasi dan barang titipan sudah di Gudang!"]); exit;
        }
        if ($header['status'] === 'ditolak') {
            echo json_encode(['status' => 'warning', 'message' => "Invoice ini SEDANG DITOLAK. Menunggu revisi dari Dapur."]); exit;
        }
        if ($header['status'] === 'dibatalkan' || $header['status'] === 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => "Invoice ini telah DIBATALKAN. Data sudah tidak berlaku."]); exit;
        }

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

        $pdo->beginTransaction();

        // Update status di tabel transaksi
        $update = $pdo->prepare("UPDATE titipan_productions SET status = ? WHERE id = ?");
        $update->execute([$status_baru, $prod_id]);

        $pesan = "Barang Titipan Ditolak! Dikembalikan untuk direvisi.";

        // LOGIKA PENAMBAHAN STOK KE STORE JIKA DITERIMA
        if ($status_baru === 'received') {
            // Ambil ID Store/Warehouse tujuannya
            $stmtWH = $pdo->prepare("SELECT warehouse_id FROM titipan_productions WHERE id = ?");
            $stmtWH->execute([$prod_id]);
            $warehouse_id = $stmtWH->fetchColumn();

            // Ambil daftar barangnya
            $stmtDet = $pdo->prepare("SELECT titipan_id, quantity FROM titipan_production_details WHERE titipan_production_id = ?");
            $stmtDet->execute([$prod_id]);
            $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            // Masukkan ke tabel store_titipan_stocks
            foreach ($details as $d) {
                $t_id = $d['titipan_id'];
                $qty = $d['quantity'];

                $cekStok = $pdo->prepare("SELECT id FROM store_titipan_stocks WHERE warehouse_id = ? AND titipan_id = ?");
                $cekStok->execute([$warehouse_id, $t_id]);
                $stokExist = $cekStok->fetch(PDO::FETCH_ASSOC);

                if ($stokExist) {
                    // Update jika sudah ada
                    $pdo->prepare("UPDATE store_titipan_stocks SET stock = stock + ? WHERE id = ?")->execute([$qty, $stokExist['id']]);
                } else {
                    // Insert jika barang baru pertama kali masuk store tersebut
                    $pdo->prepare("INSERT INTO store_titipan_stocks (warehouse_id, titipan_id, stock) VALUES (?, ?, ?)")->execute([$warehouse_id, $t_id, $qty]);
                }
            }
            $pesan = "Barang Titipan Valid & Masuk ke Etalase Store!";
        }

        $pdo->commit();
        
        echo json_encode([
            'status' => 'success', 
            'message' => $pesan,
            'status_type' => $status_baru
        ]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>