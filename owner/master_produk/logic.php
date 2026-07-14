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
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID produk tidak valid!']); exit;
            }

            // Ambil nama gambar sebelum dihapus
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $img = $stmt->fetchColumn();

            if ($img === false) {
                echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan!']); exit;
            }

            // ── Hapus semua data relasi yang tidak punya ON DELETE CASCADE ──
            // (FK tanpa CASCADE harus dihapus manual sebelum hapus parent row)
            $pdo->beginTransaction();

            // 1. Hapus detail produksi yang merujuk ke produk ini
            $pdo->prepare("DELETE FROM production_details WHERE product_id = ?")->execute([$id]);

            // 2. Hapus stok per gudang (product_warehouse_stocks)
            //    Tabel ini dibuat di database.php — jaga agar tidak orphan
            $pdo->prepare("DELETE FROM product_warehouse_stocks WHERE product_id = ?")->execute([$id]);

            // 3. Hapus produk (bom akan ikut terhapus karena ON DELETE CASCADE)
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

            $pdo->commit();

            // Hapus file fisik gambar dari folder assets/img
            if (!empty($img) && $img !== 'no-image.png' && file_exists($uploadDir . $img)) {
                unlink($uploadDir . $img);
            }

            echo json_encode(['status' => 'success', 'message' => 'Produk beserta semua data terkait berhasil dihapus!']);
            break;
        case 'read_custom_pos':
            header('Content-Type: application/json');
            $stmt = $pdo->query("
                SELECT sci.id, sci.name, sci.price, sci.created_at,
                       COUNT(bc.id) as total_resep
                FROM saved_custom_items_pos sci
                LEFT JOIN bom_custom bc ON bc.custom_item_id = sci.id
                GROUP BY sci.id, sci.name, sci.price, sci.created_at
                ORDER BY sci.created_at DESC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'delete_custom_pos':
            header('Content-Type: application/json');
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']); exit;
            }

            // Cek item ada
            $cek = $pdo->prepare("SELECT id FROM saved_custom_items_pos WHERE id = ?");
            $cek->execute([$id]);
            if (!$cek->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Item custom tidak ditemukan!']); exit;
            }

            $pdo->beginTransaction();
            // 1. Hapus resep BOM custom (bom_custom)
            $pdo->prepare("DELETE FROM bom_custom WHERE custom_item_id = ?")->execute([$id]);
            // 2. Hapus item custom dari POS
            $pdo->prepare("DELETE FROM saved_custom_items_pos WHERE id = ?")->execute([$id]);
            $pdo->commit();

            echo json_encode(['status' => 'success', 'message' => 'Item custom POS berhasil dihapus!']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>