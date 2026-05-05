<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_produk');

$action = $_GET['action'] ?? '';
// Path yang benar menuju folder assets/img
$uploadDir = '../../assets/img/';

// Pastikan folder tersedia
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    switch ($action) {
        case 'download_template':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=Template_Import_Produk.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Kode Produk', 'Nama Produk', 'Kategori', 'Harga Modal', 'Harga Jual Offline', 'Harga Jual Online']);
            fputcsv($output, ['RCK-01', 'Roti Coklat Keju', 'Roti Manis', '3500', '5000', '6500']);
            fclose($output);
            exit;

        case 'import':
            header('Content-Type: application/json');
            checkPermission('edit_master_produk');
            if (!isset($_FILES['file_import']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan!']); exit;
            }
            $handle = fopen($_FILES['file_import']['tmp_name'], "r");
            $sukses = 0; $row = 0;
            $pdo->beginTransaction();
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++; if ($row == 1) continue; 
                $code = strtoupper(trim($data[0]));
                $name = trim($data[1]);
                if (empty($code) || empty($name)) continue;
                
                $stmt = $pdo->prepare("INSERT INTO products (code, name, category, modal_price, price, online_price) 
                                      VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE 
                                      name=VALUES(name), category=VALUES(category), modal_price=VALUES(modal_price), 
                                      price=VALUES(price), online_price=VALUES(online_price)");
                $stmt->execute([$code, $name, $data[2] ?? '', $data[3] ?? 0, $data[4] ?? 0, $data[5] ?? 0]);
                $sukses++;
            }
            fclose($handle);
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => "$sukses data berhasil diproses!"]);
            break;

        case 'read':
            header('Content-Type: application/json');
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'save':
            header('Content-Type: application/json');
            checkPermission('edit_master_produk');
            $id = $_POST['id'] ?? '';
            $imageName = $_POST['old_image'] ?? '';

            // PROSES UPLOAD GAMBAR BARU KE ASSETS/IMG
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newImageName = time() . '_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImageName)) {
                    // Jika berhasil upload dan ada gambar lama, hapus gambar lama agar storage tidak penuh
                    if (!empty($imageName) && $imageName !== 'no-image.png' && file_exists($uploadDir . $imageName)) {
                        unlink($uploadDir . $imageName);
                    }
                    $imageName = $newImageName; // Timpa nama gambar untuk disimpan ke DB
                }
            }

            $params = [
                strtoupper($_POST['code']), 
                $_POST['name'], 
                $_POST['category'], 
                $_POST['modal_price'] ?: 0, 
                $_POST['price'] ?: 0, 
                $_POST['online_price'] ?: 0, 
                $imageName
            ];

            if (empty($id)) {
                $stmt = $pdo->prepare("INSERT INTO products (code, name, category, modal_price, price, online_price, image) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute($params);
            } else {
                $params[] = $id;
                $stmt = $pdo->prepare("UPDATE products SET code=?, name=?, category=?, modal_price=?, price=?, online_price=?, image=? WHERE id=?");
                $stmt->execute($params);
            }
            echo json_encode(['status' => 'success', 'message' => 'Data produk berhasil disimpan!']);
            break;

        case 'delete':
            header('Content-Type: application/json');
            checkPermission('hapus_master_produk');
            $id = $_POST['id'] ?? '';
            
            // Ambil nama gambar sebelum baris dihapus
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();

            // Hapus baris data
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);

            // Hapus file fisik gambar dari folder assets/img
            if(!empty($img) && $img !== 'no-image.png' && file_exists($uploadDir . $img)) { 
                unlink($uploadDir . $img); 
            }

            echo json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus!']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>