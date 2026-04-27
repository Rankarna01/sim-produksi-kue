<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_resep');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read_products':
            // PERBAIKAN: Ambil juga status apakah produk ini sedang punya pengajuan pending
            $stmt = $pdo->query("
                SELECT p.id, p.name, p.category, 
                (SELECT COUNT(id) FROM bom WHERE product_id = p.id) as total_bahan,
                (SELECT status FROM bom_requests WHERE product_id = p.id AND status = 'pending' LIMIT 1) as pending_status
                FROM products p 
                ORDER BY p.name ASC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'get_materials':
            $stmt = $pdo->query("SELECT id, material_name as name, unit FROM materials_stocks ORDER BY material_name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'read_bom':
            $product_id = $_GET['product_id'] ?? 0;
            // PERBAIKAN: Menambahkan ms.id as material_id agar JS bisa mendeteksi ID bahan untuk dimasukkan ke draft awal
            $stmt = $pdo->prepare("
                SELECT b.id, ms.id as material_id, ms.material_name as name, b.quantity_needed, b.unit_used 
                FROM bom b 
                JOIN materials_stocks ms ON b.material_id = ms.id 
                WHERE b.product_id = ?
                ORDER BY ms.material_name ASC
            ");
            $stmt->execute([$product_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ==============================================================
        // FITUR BARU: SIMPAN DRAFT MENJADI PENGAJUAN KE OWNER
        // ==============================================================
        case 'submit_bom_request':
            $product_id = $_POST['product_id'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $drafts = json_decode($_POST['drafts'], true);
            $user_id = $_SESSION['user_id'] ?? 1;

            if (empty($product_id) || empty($drafts)) {
                echo json_encode(['status' => 'error', 'message' => 'Resep tidak boleh kosong!']); exit;
            }

            if (empty($notes)) {
                echo json_encode(['status' => 'error', 'message' => 'Catatan perubahan resep wajib diisi!']); exit;
            }

            // 1. Cek apakah masih ada pengajuan yang belum diproses oleh Owner
            $cek = $pdo->prepare("SELECT id FROM bom_requests WHERE product_id = ? AND status = 'pending'");
            $cek->execute([$product_id]);
            if($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal! Anda sudah mengajukan perubahan resep ini dan sedang menunggu persetujuan Owner.']); exit;
            }

            $pdo->beginTransaction();

            // 2. Buat Header Pengajuan
            $req_no = "BOM-" . date('ymd') . "-" . strtoupper(substr(uniqid(), -4));
            $stmtHead = $pdo->prepare("INSERT INTO bom_requests (request_no, product_id, user_id, status, notes) VALUES (?, ?, ?, 'pending', ?)");
            $stmtHead->execute([$req_no, $product_id, $user_id, $notes]);
            $request_id = $pdo->lastInsertId();

            // 3. Masukkan Detail Komposisinya
            $stmtDet = $pdo->prepare("INSERT INTO bom_request_details (request_id, material_id, quantity_needed, unit_used) VALUES (?, ?, ?, ?)");
            foreach($drafts as $d) {
                $stmtDet->execute([$request_id, $d['material_id'], $d['quantity_needed'], $d['unit_used']]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Pengajuan resep berhasil dikirim ke Owner!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action!']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>