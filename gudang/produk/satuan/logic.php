<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
// Akses role/permission dilepas sesuai permintaan

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

        // Hitung total data untuk pagination
        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM units $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Ambil data
        $sql = "SELECT * FROM units $whereClause ORDER BY id ASC LIMIT $limit OFFSET $offset";
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

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Satuan wajib diisi!']); exit;
        }

        if (empty($id)) {
            // INSERT BARU
            $cek = $pdo->prepare("SELECT id FROM units WHERE name = ?");
            $cek->execute([$name]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Satuan sudah ada!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO units (name) VALUES (?)");
            $stmt->execute([$name]);
            echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil ditambahkan!']);
        } else {
            // UPDATE
            $cek = $pdo->prepare("SELECT id FROM units WHERE name = ? AND id != ?");
            $cek->execute([$name, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Satuan sudah dipakai!']); exit;
            }

            $stmt = $pdo->prepare("UPDATE units SET name=? WHERE id=?");
            $stmt->execute([$name, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        try {
            $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil dihapus!']);
        } catch (PDOException $e) {
            // Error 23000 = Integrity constraint violation (Foreign Key error)
            if ($e->getCode() == '23000') {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Satuan ini sedang digunakan pada data Master Barang atau Resep.']);
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