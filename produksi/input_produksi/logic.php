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

        // 1. Array Konversi Bulan ke Alfabet
        $arr_bulan = [
            1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F',
            7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L'
        ];
        $bulan_sekarang = (int)date('n'); 
        $kode_bulan = $arr_bulan[$bulan_sekarang]; 
        
        // 2. Ambil Tanggal dan Tahun
        $tgl_hari_ini = date('d'); 
        $tahun = date('y'); 

        // =================================================================
        // PERBAIKAN: LOGIKA GENERATOR INVOICE NO YANG KEBAL DUPLIKAT
        // =================================================================
        $prefix = "{$kode_bulan}{$tgl_hari_ini}{$tahun}-"; // Contoh Prefix: D0426-
        
        // Cari nomor invoice TERBESAR di hari ini
        $stmtCek = $pdo->prepare("
            SELECT invoice_no 
            FROM productions 
            WHERE invoice_no LIKE ? 
            ORDER BY invoice_no DESC 
            LIMIT 1
        ");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();

        if ($lastInvoice) {
            // Jika sudah ada, ambil 3 digit terakhirnya, lalu tambah 1
            $lastUrut = (int) substr($lastInvoice, -3);
            $nextUrut = $lastUrut + 1;
        } else {
            // Jika hari ini belum ada produksi sama sekali, mulai dari 1
            $nextUrut = 1;
        }
        
        // Format agar selalu 3 digit (contoh: 001, 002, 015)
        $urutan_str = str_pad($nextUrut, 3, '0', STR_PAD_LEFT); 
        
        // HASIL INVOICE (Misal: D0426-003)
        $invoice_no = $prefix . $urutan_str;
        // =================================================================

        // ==============================================================
        // GENERATOR BARCODE SUPER AMAN (ANTI-DUPLIKAT)
        // Format: BRC-TahunBulanTglJamMenitDetik-Random4Angka
        // ==============================================================
        $base_barcode = "BRC-" . date('YmdHis') . "-" . rand(1000, 9999);

        // INSERT HEADER PRODUKSI
        $prod_stmt = $pdo->prepare("INSERT INTO productions (invoice_no, user_id, employee_id, warehouse_id, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
        $prod_stmt->execute([$invoice_no, $user_id, $employee_id, $warehouse_id, $notes]);
        $production_id = $pdo->lastInsertId();

        // Looping setiap produk yang diproduksi
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = (int)$quantities[$i];

            if (empty($product_id) || $quantity <= 0) continue; 

            // Cek Resep BOM
            $bom_stmt = $pdo->prepare("SELECT material_id, quantity_needed, unit_used FROM bom WHERE product_id = ?");
            $bom_stmt->execute([$product_id]);
            $bom_list = $bom_stmt->fetchAll();

            if (count($bom_list) === 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Gagal: Ada produk yang belum memiliki resep (BOM). Hubungi Owner!']);
                exit;
            }

            // Potong Stok Bahan Baku dengan Konversi Desimal
            foreach ($bom_list as $bom) {
                $stok_stmt = $pdo->prepare("SELECT name, stock, unit FROM materials WHERE id = ? FOR UPDATE"); 
                $stok_stmt->execute([$bom['material_id']]);
                $material = $stok_stmt->fetch();

                $total_needed_in_bom_unit = floatval($bom['quantity_needed']) * $quantity;
                $total_deducted_from_stock = $total_needed_in_bom_unit;
                
                $bom_u = strtolower(trim($bom['unit_used'])); 
                $mat_u = strtolower(trim($material['unit'])); 

                if ($bom_u !== $mat_u) {
                    if ($bom_u === 'gram' && $mat_u === 'kg') $total_deducted_from_stock = $total_needed_in_bom_unit / 1000;
                    else if ($bom_u === 'kg' && $mat_u === 'gram') $total_deducted_from_stock = $total_needed_in_bom_unit * 1000;
                    else if ($bom_u === 'ons' && $mat_u === 'kg') $total_deducted_from_stock = $total_needed_in_bom_unit / 10;
                    else if ($bom_u === 'ons' && $mat_u === 'gram') $total_deducted_from_stock = $total_needed_in_bom_unit * 100;
                    else if ($bom_u === 'gram' && $mat_u === 'ons') $total_deducted_from_stock = $total_needed_in_bom_unit / 100;
                    else if ($bom_u === 'kg' && $mat_u === 'ons') $total_deducted_from_stock = $total_needed_in_bom_unit * 10;
                    else if ($bom_u === 'ml' && $mat_u === 'liter') $total_deducted_from_stock = $total_needed_in_bom_unit / 1000;
                    else if ($bom_u === 'liter' && $mat_u === 'ml') $total_deducted_from_stock = $total_needed_in_bom_unit * 1000;
                }

                if (floatval($material['stock']) < $total_deducted_from_stock) {
                    $pdo->rollBack(); 
                    echo json_encode(['status' => 'error', 'message' => "Stok {$material['name']} tidak cukup! (Butuh: {$total_deducted_from_stock} {$material['unit']})"]);
                    exit;
                }

                $update_stok = $pdo->prepare("UPDATE materials SET stock = stock - ? WHERE id = ?");
                $update_stok->execute([$total_deducted_from_stock, $bom['material_id']]);
            }

            // Tambahkan index ($i) di akhir string agar tiap baris produk punya ID unik di tabel details
            $barcode = $base_barcode . "-" . $i;

            $detail_stmt = $pdo->prepare("INSERT INTO production_details (production_id, product_id, quantity, barcode) VALUES (?, ?, ?, ?)");
            $detail_stmt->execute([$production_id, $product_id, $quantity, $barcode]);
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Produksi dicatat dan bahan dipotong.',
            'production_id' => $production_id
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>