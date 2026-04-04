<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name']);

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Kategori wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                // Cek duplikat saat Insert
                $cek = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                $cek->execute([$name]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kategori ini sudah ada!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$name]);
                echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil ditambahkan!']);
            } else {
                // Cek duplikat saat Update
                $cek = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
                $cek->execute([$name, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kategori dengan nama ini sudah ada!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE categories SET name=? WHERE id=?");
                $stmt->execute([$name, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil diperbarui!']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            
            // Opsional: Kamu bisa mengecek apakah kategori ini sedang dipakai di tabel products
            // Tapi karena desain lama menggunakan teks, kita hapus saja langsung dari master list-nya
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Kategori berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>