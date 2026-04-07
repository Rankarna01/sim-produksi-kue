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
        $employees = $pdo->query("SELECT id, name FROM employees ORDER BY name ASC")->fetchAll();
        
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
        $notes = trim($_POST['notes'] ?? ''); 
        
        $product_ids = $_POST['product_id']; 
        $quantities = $_POST['quantity'];

        if (empty($employee_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Pilih Karyawan terlebih dahulu!']);
            exit;
        }

        if (empty($product_ids) || count($product_ids) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Harap tambahkan minimal 1 produk!']);
            exit;
        }

        $pdo->beginTransaction();

        $arr_bulan = [
            1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F',
            7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L'
        ];
        $bulan_sekarang = (int)date('n'); 
        $kode_bulan = $arr_bulan[$bulan_sekarang]; 
        
        $tgl_hari_ini = date('d'); 
        $tahun = date('y'); 

        $prefix = "{$kode_bulan}{$tgl_hari_ini}{$tahun}-"; 
        
        $stmtCek = $pdo->prepare("SELECT invoice_no FROM productions WHERE invoice_no LIKE ? ORDER BY invoice_no DESC LIMIT 1");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();

        if ($lastInvoice) {
            $lastUrut = (int) substr($lastInvoice, -3);
            $nextUrut = $lastUrut + 1;
        } else {
            $nextUrut = 1;
        }
        
        $urutan_str = str_pad($nextUrut, 3, '0', STR_PAD_LEFT); 
        $invoice_no = $prefix . $urutan_str;

        $prod_stmt = $pdo->prepare("INSERT INTO productions (invoice_no, user_id, employee_id, warehouse_id, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
        $prod_stmt->execute([$invoice_no, $user_id, $employee_id, $warehouse_id, $notes]);
        $production_id = $pdo->lastInsertId();

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = (int)$quantities[$i];

            if (empty($product_id) || $quantity <= 0) continue; 

            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll();

            if (count($bom_list) === 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Gagal: Ada produk yang belum memiliki resep (BOM). Hubungi Owner!']);
                exit;
            }

            foreach ($bom_list as $bom) {
                $stok_stmt = $pdo->prepare("SELECT name, stock, unit FROM materials WHERE id = ? FOR UPDATE"); 
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $total_needed_in_bom_unit = floatval($bom['quantity_needed']) * $quantity;
                $total_deducted = $total_needed_in_bom_unit;
                
                $bom_u = strtolower(trim($bom['unit_used'])); 
                $mat_u = strtolower(trim($material['unit'])); 

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
                $update_stok->execute([$total_deducted, $bom['material_id']]);
            }

            // PERBAIKAN BARCODE: Dibuat sangat pendek agar mudah di-scan (Cth: D0426-001-1)
            $barcode = $invoice_no . "-" . ($i + 1);

            $detail_stmt = $pdo->prepare("INSERT INTO production_details (production_id, product_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $product_id, $quantity, $barcode]);
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Produksi dicatat dan bahan dipotong.',
            'production_id' => $production_id
        ]);
        exit;
    }

    // ==============================================================
    // LOGIKA REVISI (AUTO REFUND STOK LAMA -> POTONG STOK BARU)
    // Fitur ini bisa dipanggil dari form Edit/Revisi nantinya
    // ==============================================================
    if ($action === 'revisi') {
        $production_id = $_POST['production_id'];
        $product_ids = $_POST['product_id']; 
        $quantities = $_POST['quantity'];

        $pdo->beginTransaction();

        // 1. Ambil detail lama untuk di-Refund (Kembalikan Stok)
        $old_details = $pdo->prepare("SELECT product_id, quantity FROM production_details WHERE production_id = ?");
        $old_details->execute([$production_id]);
        $old_items = $old_details->fetchAll();

        foreach ($old_items as $old) {
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$old['product_id']]);
            $bom_list = $bom_stmt->fetchAll();

            foreach ($bom_list as $bom) {
                $stok_stmt = $pdo->prepare("SELECT unit FROM materials WHERE id = ? FOR UPDATE"); 
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $total_refund = floatval($bom['quantity_needed']) * $old['quantity'];
                $bom_u = strtolower(trim($bom['unit_used'])); 
                $mat_u = strtolower(trim($material['unit'])); 

                if ($bom_u !== $mat_u) {
                    if ($bom_u === 'gram' && $mat_u === 'kg') $total_refund = $total_refund / 1000;
                    else if ($bom_u === 'kg' && $mat_u === 'gram') $total_refund = $total_refund * 1000;
                    // ... (Konversi lainnya menyesuaikan seperti saat save)
                }

                // REFUND (+) KEMBALIKAN STOK LAMA
                $update_stok = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
                $update_stok->execute([$total_refund, $bom['material_id']]);
            }
        }

        // 2. Hapus detail lama
        $del_stmt = $pdo->prepare("DELETE FROM production_details WHERE production_id = ?");
        $del_stmt->execute([$production_id]);

        // 3. Masukkan detail baru dan POTONG (-) STOK BARU (Sama seperti logika save)
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
                $stok_stmt = $pdo->prepare("SELECT unit FROM materials WHERE id = ? FOR UPDATE"); 
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $total_deducted = floatval($bom['quantity_needed']) * $quantity;
                $bom_u = strtolower(trim($bom['unit_used'])); 
                $mat_u = strtolower(trim($material['unit'])); 

                if ($bom_u !== $mat_u) {
                    if ($bom_u === 'gram' && $mat_u === 'kg') $total_deducted = $total_deducted / 1000;
                    else if ($bom_u === 'kg' && $mat_u === 'gram') $total_deducted = $total_deducted * 1000;
                }

                // POTONG (-) STOK BARU
                $update_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                $update_stok->execute([$total_deducted, $bom['material_id']]);
            }

            $barcode = $invoice_no . "-" . ($i + 1);
            $detail_stmt = $pdo->prepare("INSERT INTO production_details (production_id, product_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $product_id, $quantity, $barcode]);
        }

        // 4. Update status kembali menjadi Pending (Siap divalidasi ulang)
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