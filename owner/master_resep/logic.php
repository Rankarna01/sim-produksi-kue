<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_resep');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read_products':
            $stmt = $pdo->query("
                SELECT p.id, p.name, p.category, 
                (SELECT COUNT(id) FROM bom WHERE product_id = p.id) as total_bahan 
                FROM products p 
                ORDER BY p.id DESC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'get_materials':
            $stmt = $pdo->query("SELECT id, name, unit FROM materials ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        // TAMBAHAN BARU: AMBIL DATA SATUAN DARI MASTER
        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'read_bom':
            $product_id = $_GET['product_id'] ?? 0;
            $stmt = $pdo->prepare("
                SELECT b.id, m.name, b.quantity_needed, b.unit_used 
                FROM bom b 
                JOIN materials m ON b.material_id = m.id 
                WHERE b.product_id = ?
            ");
            $stmt->execute([$product_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'save_bom':
            $product_id = $_POST['product_id'];
            $material_id = $_POST['material_id'];
            $quantity = $_POST['quantity_needed'];
            $unit_used = $_POST['unit_used']; 

            // Cek apakah bahan ini sudah ada di resep produk ini
            $cek = $pdo->prepare("SELECT id FROM bom WHERE product_id = ? AND material_id = ?");
            $cek->execute([$product_id, $material_id]);
            
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Bahan ini sudah ada di dalam resep!']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO bom (product_id, material_id, quantity_needed, unit_used) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $material_id, $quantity, $unit_used]);
            echo json_encode(['status' => 'success', 'message' => 'Bahan berhasil ditambahkan ke resep!']);
            break;

        case 'delete_bom':
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM bom WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>