<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('persetujuan_owner');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. TAMPILKAN DAFTAR PENGAJUAN RESEP
    if ($action === 'read') {
        $sql = "
            SELECT r.id, r.request_no, p.name as product_name, u.name as user_name, 
                   r.status, r.notes, r.created_at
            FROM bom_requests r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            ORDER BY 
                CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END, 
                r.created_at DESC
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. BACA DETAIL BAHAN DARI PENGAJUAN
    if ($action === 'read_detail') {
        $id = $_GET['id'] ?? 0;
        
        // Ambil Header
        $stmtHead = $pdo->prepare("
            SELECT r.*, p.name as product_name 
            FROM bom_requests r 
            JOIN products p ON r.product_id = p.id 
            WHERE r.id = ?
        ");
        $stmtHead->execute([$id]);
        $header = $stmtHead->fetch(PDO::FETCH_ASSOC);

        // Ambil Detail Bahan
        $stmtDet = $pdo->prepare("
            SELECT d.quantity_needed, d.unit_used, m.material_name 
            FROM bom_request_details d
            JOIN materials_stocks m ON d.material_id = m.id
            WHERE d.request_id = ?
            ORDER BY m.material_name ASC
        ");
        $stmtDet->execute([$id]);
        $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'header' => $header, 'details' => $details]);
        exit;
    }

    // 3. PROSES APPROVE (SETUJUI)
    if ($action === 'approve') {
        $id = $_POST['id'] ?? 0;

        $pdo->beginTransaction();

        // Ambil product_id dari pengajuan ini
        $req = $pdo->prepare("SELECT product_id, status FROM bom_requests WHERE id = ?");
        $req->execute([$id]);
        $requestData = $req->fetch(PDO::FETCH_ASSOC);

        if (!$requestData || $requestData['status'] !== 'pending') {
            echo json_encode(['status' => 'error', 'message' => 'Pengajuan tidak valid atau sudah diproses!']); exit;
        }

        $product_id = $requestData['product_id'];

        // A. Hapus resep (BOM) lama untuk produk ini
        $pdo->prepare("DELETE FROM bom WHERE product_id = ?")->execute([$product_id]);

        // B. Pindahkan resep baru dari detail pengajuan ke tabel BOM utama
        $details = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom_request_details WHERE request_id = ?");
        $details->execute([$id]);
        $rows = $details->fetchAll(PDO::FETCH_ASSOC);

        $ins = $pdo->prepare("INSERT INTO bom (product_id, material_id, quantity_needed, unit_used) VALUES (?, ?, ?, ?)");
        foreach($rows as $row) {
            $ins->execute([$product_id, $row['material_id'], $row['quantity_needed'], $row['unit_used']]);
        }

        // C. Update status pengajuan menjadi approved
        $pdo->prepare("UPDATE bom_requests SET status = 'approved' WHERE id = ?")->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Resep baru berhasil disetujui dan diterapkan!']);
        exit;
    }

    // 4. PROSES REJECT (TOLAK)
    if ($action === 'reject') {
        $id = $_POST['id'] ?? 0;
        
        $pdo->prepare("UPDATE bom_requests SET status = 'rejected' WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan resep berhasil ditolak!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>