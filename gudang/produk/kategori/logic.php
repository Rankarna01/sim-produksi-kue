<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('master_kategori');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND name LIKE ?";
            $params[] = "%$search%";
        }

        // Tembak ke tabel material_categories
        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM material_categories $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Tembak ke tabel material_categories
        $sql = "SELECT * FROM material_categories $whereClause ORDER BY name ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }

    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Kategori wajib diisi!']); exit;
        }

        if (empty($id)) {
            // INSERT BARU
            $cek = $pdo->prepare("SELECT id FROM material_categories WHERE name = ?");
            $cek->execute([$name]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Kategori sudah ada!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO material_categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil ditambahkan!']);
        } else {
            // UPDATE
            $cek = $pdo->prepare("SELECT id FROM material_categories WHERE name = ? AND id != ?");
            $cek->execute([$name, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Kategori sudah dipakai!']); exit;
            }

            $stmt = $pdo->prepare("UPDATE material_categories SET name=?, description=? WHERE id=?");
            $stmt->execute([$name, $description, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        try {
            $stmt = $pdo->prepare("DELETE FROM material_categories WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus!']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Kategori ini sedang digunakan oleh barang di Inventory.']);
            } else {
                throw $e;
            }
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>