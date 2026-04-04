<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'read':
            $stmt = $pdo->query("SELECT * FROM materials ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $unit = $_POST['unit'];
            
            $raw_stock = $_POST['stock'] ?? 0;
            $raw_min_stock = $_POST['min_stock'] ?? 0;
            $stock = (float) str_replace(',', '.', $raw_stock);
            $min_stock = (float) str_replace(',', '.', $raw_min_stock);

            if (empty($code) || empty($name) || empty($unit)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode, Nama, dan Satuan wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ?");
                $cek->execute([$code]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Bahan sudah digunakan!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $name, $unit, $stock, $min_stock]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil ditambahkan!']);
            } else {
                $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND id != ?");
                $cek->execute([$code, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Bahan sudah digunakan bahan lain!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE materials SET code=?, name=?, unit=?, stock=?, min_stock=? WHERE id=?");
                $stmt->execute([$code, $name, $unit, $stock, $min_stock, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil diperbarui!']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil dihapus!']);
            break;

        // =======================================================
        // FITUR BARU: DOWNLOAD TEMPLATE & IMPORT CSV
        // =======================================================
        case 'download_template':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="Template_Import_Bahan_Baku.csv"');
            $output = fopen('php://output', 'w');
            // Menambahkan BOM untuk kompatibilitas MS Excel
            fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Judul Kolom (Baris 1)
            fputcsv($output, ['Kode Bahan', 'Nama Bahan', 'Satuan', 'Stok Awal', 'Min Stok']);
            
            // Contoh Pengisian (Baris 2 & 3)
            fputcsv($output, ['TPG-01', 'Tepung Terigu Segitiga Biru', 'Kg', '50.5', '10']);
            fputcsv($output, ['GL-01', 'Gula Pasir', 'Kg', '30', '5']);
            
            fclose($output);
            exit;

        case 'import_csv':
            if (!isset($_FILES['file_csv']['tmp_name'])) {
                echo json_encode(['status' => 'error', 'message' => 'File CSV tidak ditemukan!']);
                exit;
            }

            $file_ext = strtolower(pathinfo($_FILES['file_csv']['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'csv') {
                echo json_encode(['status' => 'error', 'message' => 'Format file salah. Wajib menggunakan format .CSV!']);
                exit;
            }

            $handle = fopen($_FILES['file_csv']['tmp_name'], "r");
            if (!$handle) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal membaca file CSV.']);
                exit;
            }

            // Skip baris pertama (Header)
            fgetcsv($handle, 1000, ","); 

            $pdo->beginTransaction();
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock) VALUES (?, ?, ?, ?, ?)");
                $stmtUpdate = $pdo->prepare("UPDATE materials SET name=?, unit=?, stock=?, min_stock=? WHERE code=?");
                $stmtCheck = $pdo->prepare("SELECT id FROM materials WHERE code=?");

                $row_count = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) < 5) continue; // Skip jika kolom tidak lengkap

                    $code = strtoupper(trim($data[0]));
                    $name = trim($data[1]);
                    $unit = trim($data[2]);
                    
                    // Antisipasi jika user pakai koma atau titik sebagai desimal
                    $stock = (float)str_replace(',', '.', trim($data[3]));
                    $min_stock = (float)str_replace(',', '.', trim($data[4]));

                    if (empty($code) || empty($name)) continue;

                    // Cek apakah Kode Bahan sudah ada di Database
                    $stmtCheck->execute([$code]);
                    if ($stmtCheck->rowCount() > 0) {
                        // Jika ada, UPDATE datanya
                        $stmtUpdate->execute([$name, $unit, $stock, $min_stock, $code]);
                    } else {
                        // Jika belum ada, INSERT baru
                        $stmtInsert->execute([$code, $name, $unit, $stock, $min_stock]);
                    }
                    $row_count++;
                }

                $pdo->commit();
                fclose($handle);
                echo json_encode(['status' => 'success', 'message' => "$row_count Data bahan baku berhasil diimpor/diupdate!"]);
                
            } catch(Exception $e) {
                $pdo->rollBack();
                fclose($handle);
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengimpor: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>