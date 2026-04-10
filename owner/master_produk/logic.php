<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_produk');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'download_template':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Template_Import_Produk.csv');
            $output = fopen('php://output', 'w');
            
            // Tulis Header Kolom
            fputcsv($output, ['Kode Produk', 'Nama Produk', 'Kategori', 'Harga']);
            // Tulis Contoh Data
            fputcsv($output, ['RCK-01', 'Roti Coklat Keju', 'Roti Manis', '5000']);
            fputcsv($output, ['RTW-02', 'Roti Tawar Gandum', 'Roti Tawar', '15000']);
            
            fclose($output);
            exit;

        case 'import':
            header('Content-Type: application/json');
            // SUNTIKAN 2A: GEMBOK IMPORT
            checkPermission('edit_master_produk');

            if (!isset($_FILES['file_import']['tmp_name']) || empty($_FILES['file_import']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'File CSV tidak ditemukan!']);
                exit;
            }

            $file = $_FILES['file_import']['tmp_name'];
            $handle = fopen($file, "r");
            $sukses = 0;
            $gagal = 0;
            $row = 0;

            $pdo->beginTransaction();

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row == 1) continue; // Lewati baris ke-1

                $code = strtoupper(trim($data[0] ?? ''));
                $name = trim($data[1] ?? '');
                $category = trim($data[2] ?? '');
                $price = (float)($data[3] ?? 0);

                if (empty($code) || empty($name)) {
                    $gagal++;
                    continue; 
                }

                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ?");
                $cek->execute([$code]);
                
                if ($cek->rowCount() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO products (code, name, category, price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$code, $name, $category, $price]);
                    $sukses++;
                } else {
                    $gagal++; 
                }
            }
            fclose($handle);
            $pdo->commit();

            echo json_encode([
                'status' => 'success', 
                'message' => "Selesai! $sukses produk baru berhasil disimpan. ($gagal baris dilewati/duplikat)"
            ]);
            break;

        case 'read':
            header('Content-Type: application/json');
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            header('Content-Type: application/json');
            // SUNTIKAN 2B: GEMBOK EDIT/TAMBAH
            checkPermission('edit_master_produk');

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

        case 'delete':
            header('Content-Type: application/json');
            // SUNTIKAN 2C: GEMBOK HAPUS
            checkPermission('hapus_master_produk');

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
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan!']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>