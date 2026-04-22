<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('master_lokasi');

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
            $whereClause .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Hitung total data untuk pagination
        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM racks $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Ambil data
        $sql = "SELECT * FROM racks $whereClause ORDER BY name ASC LIMIT $limit OFFSET $offset";
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
        $name = strtoupper(trim($_POST['name'] ?? '')); // Otomatis jadikan huruf besar agar rapi (A-01)
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Kode / Nama Rak wajib diisi!']); exit;
        }

        if (empty($id)) {
            // INSERT BARU
            $cek = $pdo->prepare("SELECT id FROM racks WHERE name = ?");
            $cek->execute([$name]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Kode Rak sudah ada!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO racks (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            echo json_encode(['status' => 'success', 'message' => 'Lokasi Rak berhasil ditambahkan!']);
        } else {
            // UPDATE
            $cek = $pdo->prepare("SELECT id FROM racks WHERE name = ? AND id != ?");
            $cek->execute([$name, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Kode Rak sudah dipakai!']); exit;
            }

            $stmt = $pdo->prepare("UPDATE racks SET name=?, description=? WHERE id=?");
            $stmt->execute([$name, $description, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Lokasi Rak berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        try {
            $stmt = $pdo->prepare("DELETE FROM racks WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Lokasi Rak berhasil dihapus!']);
        } catch (PDOException $e) {
            // Error 23000 = Integrity constraint violation (Foreign Key error)
            if ($e->getCode() == '23000') {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Rak ini sedang digunakan oleh barang di Inventory. Silakan pindahkan barangnya dulu.']);
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