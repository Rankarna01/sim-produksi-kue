<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Pastikan ini sama dengan yang dicentang di Manajemen Role
// checkPermission('trx_keluar_titipan');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? ''; 

try {
    if ($action === 'init') {
        $items = $pdo->query("SELECT id, nama_barang, nama_umkm, stok FROM barang_titipan WHERE stok > 0 ORDER BY nama_barang ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'items' => $items]);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->query("SELECT COUNT(id) FROM barang_titipan_keluar");
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "SELECT k.*, t.nama_barang, t.nama_umkm, u.name as admin_name 
                FROM barang_titipan_keluar k 
                JOIN barang_titipan t ON k.titipan_id = t.id 
                JOIN users u ON k.user_id = u.id 
                ORDER BY k.created_at DESC LIMIT $limit OFFSET $offset";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data, 'total_pages' => $total_pages, 'current_page' => $page]);
        exit;
    }

    if ($action === 'save') {
        $titipan_id = $_POST['titipan_id'];
        $qty = (int)$_POST['qty'];
        $reason = $_POST['reason'];
        $notes = $_POST['notes'] ?? '';
        $user_id = $_SESSION['user_id'];

        if ($qty <= 0) { 
            echo json_encode(['status' => 'error', 'message' => 'Jumlah harus lebih dari 0!']); 
            exit; 
        }

        $pdo->beginTransaction();

        $stmtCek = $pdo->prepare("SELECT stok FROM barang_titipan WHERE id = ? FOR UPDATE");
        $stmtCek->execute([$titipan_id]);
        $stok = $stmtCek->fetchColumn();

        if ($qty > $stok) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => "Gagal! Stok tersisa hanya $stok."]); 
            exit;
        }

        $out_no = "OUT-TTP-" . date('ymdHis') . rand(10,99);
        
        $ins = $pdo->prepare("INSERT INTO barang_titipan_keluar (out_no, titipan_id, qty, reason, notes, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->execute([$out_no, $titipan_id, $qty, $reason, $notes, $user_id]);

        $upd = $pdo->prepare("UPDATE barang_titipan SET stok = stok - ? WHERE id = ?");
        $upd->execute([$qty, $titipan_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Barang keluar berhasil dicatat!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>