<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_produk');

$action = $_GET['action'] ?? '';

// Pastikan folder upload gambar tersedia
$uploadDir = '../../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    switch ($action) {
        case 'download_template':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Template_Import_Produk.csv');
            $output = fopen('php://output', 'w');
            
            // Tulis Header Kolom
            fputcsv($output, ['Kode Produk', 'Nama Produk', 'Kategori', 'Harga Modal', 'Harga Jual']);
            // Tulis Contoh Data
            fputcsv($output, ['RCK-01', 'Roti Coklat Keju', 'Roti Manis', '3500', '5000']);
            fputcsv($output, ['RTW-02', 'Roti Tawar Gandum', 'Roti Tawar', '10000', '15000']);
            
            fclose($output);
            exit;

        case 'import':
            header('Content-Type: application/json');
            checkPermission('edit_master_produk');

            if (!isset($_FILES['file_import']['tmp_name']) || empty($_FILES['file_import']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'File CSV tidak ditemukan!']);
                exit;
            }

            $file = $_FILES['file_import']['tmp_name'];
            $handle = fopen($file, "r");
            $sukses = 0; $gagal = 0; $row = 0;

            $pdo->beginTransaction();

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row == 1) continue; 

                $code = strtoupper(trim($data[0] ?? ''));
                $name = trim($data[1] ?? '');
                $category = trim($data[2] ?? '');
                $modal_price = (float)($data[3] ?? 0);
                $price = (float)($data[4] ?? 0);

                if (empty($code) || empty($name)) { $gagal++; continue; }

                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ?");
                $cek->execute([$code]);
                
                if ($cek->rowCount() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO products (code, name, category, modal_price, price) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $name, $category, $modal_price, $price]);
                    $sukses++;
                } else {
                    $gagal++; 
                }
            }
            fclose($handle);
            $pdo->commit();

            echo json_encode(['status' => 'success', 'message' => "Selesai! $sukses produk baru berhasil disimpan. ($gagal baris dilewati/duplikat)"]);
            break;

        case 'read':
            header('Content-Type: application/json');
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            header('Content-Type: application/json');
            checkPermission('edit_master_produk');

            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $category = $_POST['category'];
            $modal_price = $_POST['modal_price'] ?? 0;
            $price = $_POST['price'] ?? 0;

            if (empty($code) || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode dan Nama wajib diisi!']); exit;
            }

            // PROSES UPLOAD GAMBAR
            $imageName = $_POST['old_image'] ?? null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }

            if (empty($id)) {
                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ?");
                $cek->execute([$code]);
                if ($cek->rowCount() > 0) { echo json_encode(['status' => 'error', 'message' => 'Kode Produk sudah digunakan!']); exit; }
                
                $stmt = $pdo->prepare("INSERT INTO products (code, name, category, modal_price, price, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$code, $name, $category, $modal_price, $price, $imageName]);
                echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!']);
            } else {
                $cek = $pdo->prepare("SELECT id FROM products WHERE code = ? AND id != ?");
                $cek->execute([$code, $id]);
                if ($cek->rowCount() > 0) { echo json_encode(['status' => 'error', 'message' => 'Kode Produk sudah digunakan produk lain!']); exit; }
                
                $stmt = $pdo->prepare("UPDATE products SET code=?, name=?, category=?, modal_price=?, price=?, image=? WHERE id=?");
                $stmt->execute([$code, $name, $category, $modal_price, $price, $imageName, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Produk berhasil diperbarui!']);
            }
            break;

        case 'delete':
            header('Content-Type: application/json');
            checkPermission('hapus_master_produk');

            $id = $_POST['id'] ?? '';
            if (empty($id)) { echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']); exit; }
            
            // Hapus gambar jika ada
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();
            if(!empty($img) && file_exists($uploadDir . $img)) { unlink($uploadDir . $img); }

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