<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']); 

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'init_form') {
        $products = $pdo->query("SELECT id, name, code FROM products ORDER BY name ASC")->fetchAll();
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll();
        
        // PERBAIKAN: Ambil nama dapur sekalian agar bisa ditampilkan di dropdown (e.g., Randy - Dapur 1)
        $employees = $pdo->query("
            SELECT e.id, e.name as emp_name, k.name as kitchen_name 
            FROM employees e 
            LEFT JOIN kitchens k ON e.kitchen_id = k.id 
            ORDER BY e.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success', 
            'products' => $products, 
            'warehouses' => $warehouses,
            'employees' => $employees
        ]);
        exit;
    }

    if ($action === 'save') {
        $user_id = $_SESSION['user_id'];
        $warehouse_id = $_POST['warehouse_id']; 
        $employee_id = $_POST['employee_id']; 
        $pin_input = trim($_POST['pin'] ?? ''); 
        $notes = trim($_POST['notes'] ?? ''); 
        
        $product_ids = $_POST['product_id']; 
        $quantities = $_POST['quantity'];

        if (empty($employee_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Pilih Karyawan terlebih dahulu!']); exit;
        }
        if (empty($pin_input)) {
            echo json_encode(['status' => 'error', 'message' => 'PIN Otorisasi wajib diisi!']); exit;
        }
        if (empty($product_ids) || count($product_ids) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Harap tambahkan minimal 1 produk!']); exit;
        }

        // =========================================================
        // VALIDASI PIN & LOKASI (DIPERKETAT)
        // =========================================================
        $stmtEmp = $pdo->prepare("SELECT id, pin, kitchen_id, name FROM employees WHERE id = ?");
        $stmtEmp->execute([$employee_id]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        if (!$emp) {
            echo json_encode(['status' => 'error', 'message' => 'Karyawan tidak ditemukan!']); exit;
        }

        // Paksa menjadi string agar tidak ada anomali tipe data angka
        if ((string)$emp['pin'] !== (string)$pin_input) {
            echo json_encode(['status' => 'error', 'message' => 'PIN Salah! Otorisasi produksi ditolak.']); exit;
        }
        
        if (empty($emp['kitchen_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Karyawan ini belum diatur lokasi dapurnya oleh Owner.']); exit;
        }

        $kitchen_id = $emp['kitchen_id']; 

        $pdo->beginTransaction();

        // GENERATE INVOICE
        $arr_bulan = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L'];
        $kode_bulan = $arr_bulan[(int)date('n')]; 
        $prefix = "{$kode_bulan}" . date('d') . date('y') . "-"; 
        
        $stmtCek = $pdo->prepare("SELECT invoice_no FROM productions WHERE invoice_no LIKE ? ORDER BY invoice_no DESC LIMIT 1");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();

        $nextUrut = $lastInvoice ? ((int) substr($lastInvoice, -3)) + 1 : 1;
        $invoice_no = $prefix . str_pad($nextUrut, 3, '0', STR_PAD_LEFT);

        $prod_stmt = $pdo->prepare("INSERT INTO productions (invoice_no, user_id, employee_id, warehouse_id, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
        $prod_stmt->execute([$invoice_no, $user_id, $employee_id, $warehouse_id, $notes]);
        $production_id = $pdo->lastInsertId();

        // POTONG STOK (ALLOW NEGATIVE & AUTO CREATE)
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = (int)$quantities[$i];

            if (empty($product_id) || $quantity <= 0) continue; 

            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll();

            if (count($bom_list) === 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Gagal: Ada produk yang belum memiliki resep (BOM). Hubungi Owner!']); exit;
            }

            foreach ($bom_list as $bom) {
                $master_stmt = $pdo->prepare("SELECT material_name, sku_code, unit FROM materials_stocks WHERE id = ?");
                $master_stmt->execute([$bom['material_id']]);
                $masterMat = $master_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$masterMat) {
                    $pdo->rollBack();
                    echo json_encode(['status' => 'error', 'message' => 'Bahan Master tidak ditemukan di database.']); exit;
                }

                $stokDapur_stmt = $pdo->prepare("SELECT id, unit FROM materials WHERE code = ? AND warehouse_id = ? FOR UPDATE");
                $stokDapur_stmt->execute([$masterMat['sku_code'], $kitchen_id]);
                $dapurMat = $stokDapur_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$dapurMat) {
                    $insDapur = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock, warehouse_id) VALUES (?, ?, ?, 0, 10, ?)");
                    $insDapur->execute([
                        $masterMat['sku_code'],
                        $masterMat['material_name'],
                        $masterMat['unit'],
                        $kitchen_id
                    ]);
                    $dapurMatId = $pdo->lastInsertId();
                    $mat_u = strtolower(trim($masterMat['unit']));
                } else {
                    $dapurMatId = $dapurMat['id'];
                    $mat_u = strtolower(trim($dapurMat['unit']));
                }

                $total_needed_in_bom_unit = floatval($bom['quantity_needed']) * $quantity;
                $total_deducted = $total_needed_in_bom_unit;
                $bom_u = strtolower(trim($bom['unit_used'])); 

                if ($bom_u !== $mat_u) {
                    if ($bom_u === 'gram' && $mat_u === 'kg') $total_deducted = $total_needed_in_bom_unit / 1000;
                    else if ($bom_u === 'kg' && $mat_u === 'gram') $total_deducted = $total_needed_in_bom_unit * 1000;
                    else if ($bom_u === 'ons' && $mat_u === 'kg') $total_deducted = $total_needed_in_bom_unit / 10;
                    else if ($bom_u === 'ons' && $mat_u === 'gram') $total_deducted = $total_needed_in_bom_unit * 100;
                    else if ($bom_u === 'gram' && $mat_u === 'ons') $total_deducted = $total_needed_in_bom_unit / 100;
                    else if ($bom_u === 'kg' && $mat_u === 'ons') $total_deducted = $total_needed_in_bom_unit * 10;
                    else if ($bom_u === 'ml' && $mat_u === 'liter') $total_deducted = $total_needed_in_bom_unit / 1000;
                    else if ($bom_u === 'liter' && $mat_u === 'ml') $total_deducted = $total_needed_in_bom_unit * 1000;
                }

                $update_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                $update_stok->execute([$total_deducted, $dapurMatId]);
            }

            $barcode = $invoice_no . "-" . ($i + 1);
            $detail_stmt = $pdo->prepare("INSERT INTO production_details (production_id, product_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $product_id, $quantity, $barcode]);
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Produksi dicatat dan bahan baku berhasil dipotong dari dapur Anda.',
            'production_id' => $production_id
        ]);
        exit;
    }

    // LOGIKA REVISI (AUTO REFUND & RE-DEDUCT)
    if ($action === 'revisi') {
        $production_id = $_POST['production_id'];
        $product_ids = $_POST['product_id']; 
        $quantities = $_POST['quantity'];

        $pdo->beginTransaction();

        $prodInfo = $pdo->prepare("SELECT employee_id FROM productions WHERE id = ?");
        $prodInfo->execute([$production_id]);
        $empId = $prodInfo->fetchColumn();

        $empInfo = $pdo->prepare("SELECT kitchen_id FROM employees WHERE id = ?");
        $empInfo->execute([$empId]);
        $kitchen_id = $empInfo->fetchColumn();

        // 1. REFUND LAMA
        $old_details = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE production_id = ?");
        $old_details->execute([$production_id]);
        $old_items = $old_details->fetchAll();

        foreach ($old_items as $old) {
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$old['product_id']]);
            $bom_list = $bom_stmt->fetchAll();

            foreach ($bom_list as $bom) {
                $master_stmt = $pdo->prepare("SELECT sku_code FROM materials_stocks WHERE id = ?");
                $master_stmt->execute([$bom['material_id']]);
                $sku_code = $master_stmt->fetchColumn();

                $stokDapur_stmt = $pdo->prepare("SELECT id, unit FROM materials WHERE code = ? AND warehouse_id = ? FOR UPDATE");
                $stokDapur_stmt->execute([$sku_code, $kitchen_id]);
                $dapurMat = $stokDapur_stmt->fetch(PDO::FETCH_ASSOC);

                if ($dapurMat) {
                    $total_refund = floatval($bom['quantity_needed']) * $old['quantity'];
                    $bom_u = strtolower(trim($bom['unit_used'])); 
                    $mat_u = strtolower(trim($dapurMat['unit'])); 

                    if ($bom_u !== $mat_u) {
                        if ($bom_u === 'gram' && $mat_u === 'kg') $total_refund = $total_refund / 1000;
                        else if ($bom_u === 'kg' && $mat_u === 'gram') $total_refund = $total_refund * 1000;
                    }
                    $update_stok = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
                    $update_stok->execute([$total_refund, $dapurMat['id']]);
                }
            }
        }

        $del_stmt = $pdo->prepare("DELETE FROM production_details WHERE production_id = ?");
        $del_stmt->execute([$production_id]);

        // 2. INSERT BARU DAN POTONG LAGI
        $invoice_stmt = $pdo->prepare("SELECT invoice_no FROM productions WHERE id = ?");
        $invoice_stmt->execute([$production_id]);
        $invoice_no = $invoice_stmt->fetchColumn();

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = (int)$quantities[$i];

            if (empty($product_id) || $quantity <= 0) continue; 

            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll();

            foreach ($bom_list as $bom) {
                $master_stmt = $pdo->prepare("SELECT material_name, sku_code, unit FROM materials_stocks WHERE id = ?");
                $master_stmt->execute([$bom['material_id']]);
                $masterMat = $master_stmt->fetch(PDO::FETCH_ASSOC);

                $stokDapur_stmt = $pdo->prepare("SELECT id, unit FROM materials WHERE code = ? AND warehouse_id = ? FOR UPDATE");
                $stokDapur_stmt->execute([$masterMat['sku_code'], $kitchen_id]);
                $dapurMat = $stokDapur_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$dapurMat) {
                    $insDapur = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock, warehouse_id) VALUES (?, ?, ?, 0, 10, ?)");
                    $insDapur->execute([$masterMat['sku_code'], $masterMat['material_name'], $masterMat['unit'], $kitchen_id]);
                    $dapurMatId = $pdo->lastInsertId();
                    $mat_u = strtolower(trim($masterMat['unit']));
                } else {
                    $dapurMatId = $dapurMat['id'];
                    $mat_u = strtolower(trim($dapurMat['unit']));
                }

                $total_deducted = floatval($bom['quantity_needed']) * $quantity;
                $bom_u = strtolower(trim($bom['unit_used'])); 

                if ($bom_u !== $mat_u) {
                    if ($bom_u === 'gram' && $mat_u === 'kg') $total_deducted = $total_deducted / 1000;
                    else if ($bom_u === 'kg' && $mat_u === 'gram') $total_deducted = $total_deducted * 1000;
                }

                $update_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                $update_stok->execute([$total_deducted, $dapurMatId]);
            }

            $barcode = $invoice_no . "-" . ($i + 1);
            $detail_stmt = $pdo->prepare("INSERT INTO production_details (production_id, product_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $product_id, $quantity, $barcode]);
        }

        $upd_status = $pdo->prepare("UPDATE productions SET status = 'pending' WHERE id = ?");
        $upd_status->execute([$production_id]);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Revisi berhasil. Stok telah disesuaikan ulang.']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>