<?php
ob_start(); // Tahan output agar tidak ada spasi bocor
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/database.php';
session_start();

header('Content-Type: application/json');
ob_clean(); // Bersihkan semua spasi kosong sebelum print JSON

$action = $_REQUEST['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 8; 
        $offset = ($page - 1) * $limit;

        $stmtCount = $pdo->query("SELECT COUNT(DISTINCT s.id) FROM sales_pos s INNER JOIN sale_details_pos sd ON s.id = sd.sale_id WHERE sd.is_custom = 1 AND s.production_status != 'selesai'");
        $total_rows = $stmtCount->fetchColumn();
        $total_pages = ceil($total_rows / $limit);
        if($total_pages < 1) $total_pages = 1;

        // Menggunakan bindValue agar aman dari syntax error PDO
        $stmt = $pdo->prepare("
            SELECT s.id, s.invoice_no, s.created_at, s.production_status, s.notes, s.order_type, s.channel, s.pickup_date, s.pickup_time, s.customer_id, c.name as customer_name
            FROM sales_pos s
            LEFT JOIN customers_pos c ON s.customer_id = c.id
            WHERE s.id IN (SELECT sale_id FROM sale_details_pos WHERE is_custom = 1)
            AND s.production_status != 'selesai'
            ORDER BY s.pickup_date ASC, s.pickup_time ASC, s.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($orders as $order) {
            $stmtDetail = $pdo->prepare("SELECT custom_name, qty, price FROM sale_details_pos WHERE sale_id = ? AND is_custom = 1");
            $stmtDetail->execute([$order['id']]);
            $order['custom_items'] = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
            $data[] = $order;
        }

        echo json_encode(['status' => 'success', 'data' => $data, 'pagination' => ['current_page' => $page, 'total_pages' => $total_pages]]);
        exit;
    }

    if ($action === 'get_customers') {
        $stmt = $pdo->query("SELECT id, name FROM customers_pos ORDER BY name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); 
        exit;
    }

    if ($action === 'update_status') {
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? ''; 
        $pdo->prepare("UPDATE sales_pos SET production_status = ? WHERE id = ?")->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diperbarui!']);
        exit;
    }

    if ($action === 'update_po') {
        $id = $_POST['id'];
        $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
        $channel = $_POST['channel'];
        $pickup_date = !empty($_POST['pickup_date']) ? $_POST['pickup_date'] : null;
        $pickup_time = !empty($_POST['pickup_time']) ? $_POST['pickup_time'] : null;
        $notes = !empty($_POST['notes']) ? $_POST['notes'] : null;

        $stmt = $pdo->prepare("UPDATE sales_pos SET customer_id = ?, channel = ?, pickup_date = ?, pickup_time = ?, notes = ? WHERE id = ?");
        $stmt->execute([$customer_id, $channel, $pickup_date, $pickup_time, $notes, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Data pesanan berhasil diupdate!']);
        exit;
    }

    if ($action === 'save_to_catalog') {
    $name = $_POST['name'];
    $price = (float)$_POST['price'];

    try {
        // Simpan ke tabel khusus custom item, BUKAN ke products
        $stmt = $pdo->prepare("INSERT INTO saved_custom_items_pos (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
        echo json_encode(['status' => 'success', 'message' => "$name berhasil disimpan ke Template Kasir!"]);
    } catch (PDOException $e) { 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); 
    }
    exit;
}

} catch (\Throwable $e) {
    // TANGKAP SEMUA ERROR (Bahkan Fatal Error PHP sekalipun)
    echo json_encode([
        'status' => 'error', 
        'message' => 'CRASH PHP: ' . $e->getMessage() . ' (Baris: ' . $e->getLine() . ')'
    ]);
    exit;
}
?>