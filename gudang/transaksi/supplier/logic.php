<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_supplier');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. ANALITIK PERBANDINGAN HARGA
    if ($action === 'read_comparison') {
        // Ambil data harga riwayat PO yang sudah Diterima (Received) dan harganya > 0
        $sql = "
            SELECT 
                ms.id as material_id, ms.material_name,
                s.id as supplier_id, s.name as supplier_name,
                pod.price, po.updated_at as received_date
            FROM purchase_order_details pod
            JOIN purchase_orders po ON pod.po_id = po.id
            JOIN materials_stocks ms ON pod.material_id = ms.id
            JOIN suppliers s ON po.supplier_id = s.id
            WHERE po.status = 'received' AND pod.price > 0
            ORDER BY po.updated_at DESC
        ";
        
        $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        // Olah data dengan PHP untuk mendapatkan riwayat harga per supplier
        $comparison = [];
        foreach ($results as $row) {
            $mat_id = $row['material_id'];
            $sup_id = $row['supplier_id'];
            
            if (!isset($comparison[$mat_id])) {
                $comparison[$mat_id] = [
                    'material_name' => $row['material_name'],
                    'best_price' => null,
                    'best_supplier' => '',
                    'best_date' => '',
                    'suppliers' => []
                ];
            }
            
            // Karena diurutkan DESC (terbaru), jika supplier ini sudah ada, skip (ambil harga terbarunya saja)
            if (!isset($comparison[$mat_id]['suppliers'][$sup_id])) {
                $comparison[$mat_id]['suppliers'][$sup_id] = [
                    'supplier_name' => $row['supplier_name'],
                    'price' => (float)$row['price'],
                    'date' => $row['received_date']
                ];
            }
        }

        // Tentukan Harga Terbaik (Termurah)
        foreach ($comparison as &$item) {
            $min_price = PHP_FLOAT_MAX;
            foreach ($item['suppliers'] as $sup) {
                if ($sup['price'] < $min_price) {
                    $min_price = $sup['price'];
                    $item['best_price'] = $sup['price'];
                    $item['best_supplier'] = $sup['supplier_name'];
                    $item['best_date'] = $sup['date'];
                }
            }
            // Ubah format key array asositif suppliers menjadi indexed array
            $item['suppliers'] = array_values($item['suppliers']);
        }
        
        // Kembalikan array berindeks
        echo json_encode(['status' => 'success', 'data' => array_values($comparison)]);
        exit;
    }

    // 2. READ DAFTAR SUPPLIER UNTUK GRID (CRUD)
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $limit = 6; 
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (name LIKE ? OR address LIKE ? OR contact_person LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM suppliers $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Tambahkan Subquery untuk menghitung jumlah item barang yang disupply
        $sql = "
            SELECT s.*,
                   (SELECT COUNT(DISTINCT pod.material_id) 
                    FROM purchase_orders po 
                    JOIN purchase_order_details pod ON po.id = pod.po_id 
                    WHERE po.supplier_id = s.id AND po.status = 'received') as items_supplied
            FROM suppliers s 
            $whereClause 
            ORDER BY s.name ASC 
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

    // 3. CREATE / UPDATE
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $cp = trim($_POST['contact_person'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name) || empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan Nomor Telp wajib diisi!']); exit;
        }

        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $cp, $phone, $email, $address, $desc]);
            echo json_encode(['status' => 'success', 'message' => 'Supplier berhasil ditambahkan!']);
        } else {
            $stmt = $pdo->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=?, description=? WHERE id=?");
            $stmt->execute([$name, $cp, $phone, $email, $address, $desc, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Data supplier diperbarui!']);
        }
        exit;
    }

    // 4. DELETE
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        // Proteksi Data Berelasi (Jangan hapus jika ada history PO)
        $cek = $pdo->prepare("SELECT id FROM purchase_orders WHERE supplier_id = ?");
        $cek->execute([$id]);
        if($cek->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Supplier tidak bisa dihapus karena memiliki riwayat transaksi PO.']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Supplier telah dihapus!']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>