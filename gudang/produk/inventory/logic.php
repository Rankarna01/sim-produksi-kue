<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // INITIAL LOAD: Tarik Dropdown Master
    if ($action === 'init_form') {
        $categories = $pdo->query("SELECT id, name FROM material_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $units = $pdo->query("SELECT name FROM units ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC); // Pakai string namenya saja sesuai permintaan
        $racks = $pdo->query("SELECT id, name FROM racks ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success', 
            'categories' => $categories,
            'units' => $units,
            'racks' => $racks
        ]);
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

        // JOIN MENGAMBIL NAMA KATEGORI & RAK
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

    // ARCHIVE (NON-AKTIFKAN, BUKAN DELETE PERMANENT)
    if ($action === 'archive') {
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("UPDATE materials_stocks SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Barang berhasil diarsipkan!']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>