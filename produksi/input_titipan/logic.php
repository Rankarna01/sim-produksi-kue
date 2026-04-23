<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']); 

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'init_form') {
        // Ambil data dari tabel barang_titipan, alias code ke nama umkm untuk tampilan
        $products = $pdo->query("SELECT id, nama_barang as name, nama_umkm as code, stok FROM barang_titipan WHERE stok > 0 ORDER BY nama_barang ASC")->fetchAll(PDO::FETCH_ASSOC);
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtUser = $pdo->prepare("SELECT kitchen_id FROM users WHERE id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $userKitchenId = $stmtUser->fetchColumn();

        if ($userKitchenId) {
            $stmtEmp = $pdo->prepare("SELECT e.id, e.name as emp_name, k.name as kitchen_name FROM employees e LEFT JOIN kitchens k ON e.kitchen_id = k.id WHERE e.kitchen_id = ? ORDER BY e.name ASC");
            $stmtEmp->execute([$userKitchenId]);
        } else {
            $stmtEmp = $pdo->query("SELECT e.id, e.name as emp_name, k.name as kitchen_name FROM employees e LEFT JOIN kitchens k ON e.kitchen_id = k.id ORDER BY e.name ASC");
        }
        
        $employees = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'products' => $products, 'warehouses' => $warehouses, 'employees' => $employees]);
        exit;
    }

    if ($action === 'save') {
        $user_id = $_SESSION['user_id'];
        $warehouse_id = $_POST['warehouse_id']; 
        $employee_id = $_POST['employee_id']; 
        $pin_input = trim($_POST['pin'] ?? ''); 
        $notes = trim($_POST['notes'] ?? ''); 
        
        $product_ids = $_POST['product_id'] ?? []; 
        $quantities = $_POST['quantity'] ?? [];

        if (empty($employee_id) || empty($pin_input) || count($product_ids) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Lengkapi form & otorisasi PIN!']); exit;
        }

        // Cek Karyawan & PIN
        $stmtEmp = $pdo->prepare("SELECT id, pin, name FROM employees WHERE id = ?");
        $stmtEmp->execute([$employee_id]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$emp || (string)$emp['pin'] !== (string)$pin_input) {
            echo json_encode(['status' => 'error', 'message' => 'PIN Salah! Otorisasi ditolak.']); exit;
        }

        $pdo->beginTransaction();

        // 1. CEK KETERSEDIAAN STOK BARANG TITIPAN
        foreach ($product_ids as $idx => $titipan_id) {
            $qty = (int)$quantities[$idx];
            if (empty($titipan_id) || $qty <= 0) continue;

            $cekStok = $pdo->prepare("SELECT nama_barang, stok FROM barang_titipan WHERE id = ? FOR UPDATE");
            $cekStok->execute([$titipan_id]);
            $brg = $cekStok->fetch(PDO::FETCH_ASSOC);

            if (!$brg || $brg['stok'] < $qty) {
                $pdo->rollBack();
                $sisa = $brg ? $brg['stok'] : 0;
                echo json_encode(['status' => 'error', 'message' => "Stok {$brg['nama_barang']} tidak cukup! (Sisa: $sisa)"]); exit;
            }
        }

        // 2. GENERATE INVOICE (Prefix TTP = Titipan)
        $prefix = "TTP-" . date('ymd') . "-"; 
        $stmtCek = $pdo->prepare("SELECT invoice_no FROM titipan_productions WHERE invoice_no LIKE ? ORDER BY id DESC LIMIT 1");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();

        $nextUrut = $lastInvoice ? ((int) substr($lastInvoice, -3)) + 1 : 1;
        $invoice_no = $prefix . str_pad($nextUrut, 3, '0', STR_PAD_LEFT);

        // 3. INSERT HEADER
        $prod_stmt = $pdo->prepare("INSERT INTO titipan_productions (invoice_no, user_id, employee_id, warehouse_id, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
        $prod_stmt->execute([$invoice_no, $user_id, $employee_id, $warehouse_id, $notes]);
        $production_id = $pdo->lastInsertId();

        // 4. POTONG STOK & INSERT DETAIL
        for ($i = 0; $i < count($product_ids); $i++) {
            $titipan_id = $product_ids[$i];
            $quantity = (int)$quantities[$i];
            if (empty($titipan_id) || $quantity <= 0) continue;

            // Potong stok utama
            $updStok = $pdo->prepare("UPDATE barang_titipan SET stok = stok - ? WHERE id = ?");
            $updStok->execute([$quantity, $titipan_id]);

            $barcode = $invoice_no . "-" . ($i + 1);
            $detail_stmt = $pdo->prepare("INSERT INTO titipan_production_details (titipan_production_id, titipan_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $titipan_id, $quantity, $barcode]);
        }

        $pdo->commit();

        echo json_encode(['status' => 'success', 'production_id' => $production_id]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>