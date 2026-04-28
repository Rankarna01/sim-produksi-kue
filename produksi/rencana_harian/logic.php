<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id']; 

try {
    if ($action === 'init') {
        // 1. Cari tahu admin yang sedang login ini terikat ke Dapur mana
        $stmtUser = $pdo->prepare("SELECT kitchen_id FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $userKitchenId = $stmtUser->fetchColumn();

        // 2. Ambil Karyawan HANYA dari Dapur tersebut
        if ($userKitchenId) {
            $stmtEmp = $pdo->prepare("
                SELECT e.id, e.name, k.name as kitchen_name 
                FROM employees e 
                LEFT JOIN kitchens k ON e.kitchen_id = k.id 
                WHERE e.kitchen_id = ?
                ORDER BY e.name ASC
            ");
            $stmtEmp->execute([$userKitchenId]);
        } else {
            $stmtEmp = $pdo->query("
                SELECT e.id, e.name, k.name as kitchen_name 
                FROM employees e 
                LEFT JOIN kitchens k ON e.kitchen_id = k.id 
                ORDER BY e.name ASC
            ");
        }
        $karyawan = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

        // Ambil daftar produk aktif
        $stmtProd = $pdo->query("SELECT id, code, name FROM products ORDER BY name ASC");
        $produk = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'karyawan' => $karyawan, 'produk' => $produk]);
        exit;
    }

    if ($action === 'save_plan') {
        $karyawan_id = $_POST['karyawan_id'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $cart = json_decode($_POST['cart'], true);
        $today = date('Y-m-d');

        if (empty($karyawan_id) || empty($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Karyawan dan Target Produk wajib diisi!']); exit;
        }

        // Cek apakah karyawan ini sudah bikin rencana hari ini
        $cek = $pdo->prepare("SELECT id FROM production_plans WHERE karyawan_id = ? AND plan_date = ?");
        $cek->execute([$karyawan_id, $today]);
        if ($cek->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Karyawan ini sudah membuat Rencana Produksi hari ini!']); exit;
        }

        $pdo->beginTransaction();

        $stmtPlan = $pdo->prepare("INSERT INTO production_plans (user_id, karyawan_id, plan_date, notes) VALUES (?, ?, ?, ?)");
        $stmtPlan->execute([$user_id, $karyawan_id, $today, $notes]);
        $plan_id = $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare("INSERT INTO production_plan_details (plan_id, product_id, target_qty, est_adonan_kg) VALUES (?, ?, ?, ?)");
        foreach ($cart as $item) {
            $adonan = !empty($item['adonan']) ? (float)$item['adonan'] : 0;
            $stmtDetail->execute([$plan_id, $item['product_id'], $item['qty'], $adonan]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Rencana Harian berhasil disimpan. Karyawan sudah diizinkan input aktual!']);
        exit;
    }

    if ($action === 'read_today') {
        $today = date('Y-m-d');
        // Hanya tampilkan plan yang dibuat oleh user_id (Dapur) ini saja
        $sql = "SELECT p.id, p.created_at, e.name as karyawan_name, 
                       (SELECT COUNT(id) FROM production_plan_details WHERE plan_id = p.id) as total_item 
                FROM production_plans p 
                JOIN employees e ON p.karyawan_id = e.id 
                WHERE p.user_id = ? AND p.plan_date = ?
                ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $today]);
        
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>