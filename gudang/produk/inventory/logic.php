<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('master_inventory');

// Jangan set header JSON secara global karena fungsi export butuh header CSV
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // ==========================================
    // BAGIAN EXPORT & IMPORT (TIDAK PAKAI JSON)
    // ==========================================
    
    // 1. Download Template CSV
    if ($action === 'download_template') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Template_Import_Barang.csv');
        $output = fopen('php://output', 'w');
        // Kolom Header
        fputcsv($output, ['SKU/Barcode', 'Nama Barang', 'Satuan', 'Stok Awal', 'Batas Stok Menipis']);
        // Data Contoh
        fputcsv($output, ['BRG-001', 'Contoh Terigu', 'Kg', '100', '10']);
        fclose($output);
        exit;
    }

    // 2. Export Data ke CSV
    if ($action === 'export') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Data_Inventory_Gudang.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['SKU/Barcode', 'Nama Barang', 'Kategori', 'Satuan', 'Rak', 'Stok Aktual', 'Min Stok', 'Status']);
        
        $stmt = $pdo->query("
            SELECT m.sku_code, m.material_name, c.name as category_name, m.unit, r.name as rack_name, m.stock, m.min_stock, m.status 
            FROM materials_stocks m 
            LEFT JOIN material_categories c ON m.category_id = c.id 
            LEFT JOIN racks r ON m.rack_id = r.id
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    // 3. Proses Import CSV
    if ($action === 'import') {
        header('Content-Type: application/json');
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file, "r");
            $header = fgetcsv($handle, 1000, ","); // Skip baris pertama (Header)
            
            $berhasil = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Pastikan jumlah kolom sesuai template (minimal 5)
                if (count($data) >= 5) {
                    $sku = trim($data[0]);
                    $name = trim($data[1]);
                    $unit = trim($data[2]);
                    $stock = (float)$data[3];
                    $min_stock = (float)$data[4];

                    if (!empty($sku) && !empty($name)) {
                        // Cek apakah SKU sudah ada
                        $cek = $pdo->prepare("SELECT id FROM materials_stocks WHERE sku_code = ?");
                        $cek->execute([$sku]);
                        if ($cek->rowCount() == 0) {
                            $stmt = $pdo->prepare("INSERT INTO materials_stocks (sku_code, material_name, unit, stock, min_stock, status) VALUES (?, ?, ?, ?, ?, 'active')");
                            $stmt->execute([$sku, $name, $unit, $stock, $min_stock]);
                            $berhasil++;
                        }
                    }
                }
            }
            fclose($handle);
            echo json_encode(['status' => 'success', 'message' => "$berhasil data barang berhasil diimport!"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membaca file!']);
        }
        exit;
    }

    // ==========================================
    // KODE LAMA (TETAP AMAN) KEMBALI PAKAI JSON
    // ==========================================
    header('Content-Type: application/json');

    // INITIAL LOAD: Tarik Dropdown Master
    if ($action === 'init_form') {
        $categories = $pdo->query("SELECT id, name FROM material_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $units = $pdo->query("SELECT name FROM units ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $racks = $pdo->query("SELECT id, name FROM racks ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'categories' => $categories, 'units' => $units, 'racks' => $racks]);
        exit;
    }

    // READ DATA (Menampilkan Tabel)
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $tab = $_GET['tab'] ?? 'active'; // active, inactive, all
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($tab !== 'all') {
            $whereClause .= " AND m.status = ?";
            $params[] = $tab;
        }

        if (!empty($search)) {
            $whereClause .= " AND (m.material_name LIKE ? OR m.sku_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countStmt = $pdo->prepare("SELECT COUNT(m.id) FROM materials_stocks m $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT m.*, c.name as category_name, r.name as rack_name 
            FROM materials_stocks m
            LEFT JOIN material_categories c ON m.category_id = c.id
            LEFT JOIN racks r ON m.rack_id = r.id
            $whereClause 
            ORDER BY m.material_name ASC 
            LIMIT $limit OFFSET $offset
        ";
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

    // CREATE / UPDATE DATA
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $sku_code = trim($_POST['sku_code'] ?? '');
        $material_name = trim($_POST['material_name'] ?? '');
        $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
        $unit = trim($_POST['unit'] ?? '');
        $rack_id = !empty($_POST['rack_id']) ? $_POST['rack_id'] : null;
        $stock = (float)($_POST['stock'] ?? 0);
        $min_stock = (float)($_POST['min_stock'] ?? 0);
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $status = $_POST['status'] ?? 'active';

        if (empty($sku_code) || empty($material_name) || empty($unit)) {
            echo json_encode(['status' => 'error', 'message' => 'SKU, Nama Barang, dan Satuan wajib diisi!']); exit;
        }

        if (empty($id)) {
            $cek = $pdo->prepare("SELECT id FROM materials_stocks WHERE sku_code = ?");
            $cek->execute([$sku_code]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'SKU / Barcode sudah terpakai!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO materials_stocks (sku_code, material_name, category_id, unit, rack_id, stock, min_stock, expiry_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sku_code, $material_name, $category_id, $unit, $rack_id, $stock, $min_stock, $expiry_date, $status]);
            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil didaftarkan!']);
        } else {
            $cek = $pdo->prepare("SELECT id FROM materials_stocks WHERE sku_code = ? AND id != ?");
            $cek->execute([$sku_code, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'SKU / Barcode sudah dipakai barang lain!']); exit;
            }

            $stmt = $pdo->prepare("UPDATE materials_stocks SET sku_code=?, material_name=?, category_id=?, unit=?, rack_id=?, stock=?, min_stock=?, expiry_date=?, status=? WHERE id=?");
            $stmt->execute([$sku_code, $material_name, $category_id, $unit, $rack_id, $stock, $min_stock, $expiry_date, $status, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data Barang berhasil diperbarui!']);
        }
        exit;
    }

    // TOGGLE STATUS (ARCHIVE & UN-ARCHIVE)
    if ($action === 'toggle_status') {
        $id = $_POST['id'] ?? '';
        $new_status = $_POST['new_status'] ?? 'active';
        
        $stmt = $pdo->prepare("UPDATE materials_stocks SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        
        $msg = $new_status === 'inactive' ? 'Barang berhasil diarsipkan!' : 'Barang dikembalikan ke daftar Aktif!';
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>