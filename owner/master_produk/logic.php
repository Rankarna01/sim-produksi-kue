<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // 1. TAMPILKAN DATA
        case 'read':
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        // 2. SIMPAN DATA (TAMBAH / EDIT)
        case 'save':
            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $category = $_POST['category'];
            $price = $_POST['price'] ?? 0;

            if (empty($code) || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode dan Nama wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                // Proses TAMBAH
                // Cek kode unik
                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ?");
                $cek->execute([$code]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Produk sudah digunakan!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO products (code, name, category, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $name, $category, $price]);
                echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!']);
            } else {
                // Proses EDIT
                // Cek kode unik selain ID ini
                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ? AND id != ?");
                $cek->execute([$code, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Produk sudah digunakan produk lain!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE products SET code=?, name=?, category=?, price=? WHERE id=?");
                $stmt->execute([$code, $name, $category, $price, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Produk berhasil diperbarui!']);
            }
            break;

        // 3. HAPUS DATA
        case 'delete':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>