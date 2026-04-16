<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Ambil ID Dapur dari Admin yang sedang login
$stmtUser = $pdo->prepare("SELECT kitchen_id FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$userKitchenId = $stmtUser->fetchColumn();

try {
    if ($action === 'get_employees') {
        if ($userKitchenId) {
            $stmt = $pdo->prepare("SELECT id, name FROM employees WHERE kitchen_id = ? ORDER BY name ASC");
            $stmt->execute([$userKitchenId]);
            $emp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $emp = $pdo->query("SELECT id, name FROM employees ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(['status' => 'success', 'data' => $emp]);
        exit;
    }

    if ($action === 'read') {
        $whereClause = "";
        $params = [];
        if ($userKitchenId) {
            $whereClause = "WHERE e.kitchen_id = ?";
            $params[] = $userKitchenId;
        }

        $sql = "
            SELECT o.id, o.invoice_no as out_id, o.origin_invoice, o.created_at, o.quantity, o.reason, o.notes, 
                   pr.name as product_name, pr.code, COALESCE(e.name, u.name) as karyawan,
                   k.name as kitchen_name
            FROM product_outs o
            JOIN products pr ON o.product_id = pr.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN employees e ON o.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            $whereClause
            ORDER BY o.id DESC LIMIT 100
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'search_invoice') {
        $inv = trim($_GET['inv'] ?? '');
        
        $stmtHead = $pdo->prepare("
            SELECT p.invoice_no, p.status, p.created_at as tgl_produksi, e.kitchen_id 
            FROM productions p 
            JOIN production_details d ON p.id = d.production_id 
            LEFT JOIN employees e ON p.employee_id = e.id
            WHERE (p.invoice_no = ? OR d.barcode = ?) 
            LIMIT 1
        ");
        $stmtHead->execute([$inv, $inv]);
        $header = $stmtHead->fetch();

        if (!$header) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan! Pastikan nomor invoice / barcode valid.']); exit;
        }
        
        // PROTEKSI: Pastikan Invoice berasal dari Dapur si peminjam (jika akun login terikat dapur)
        if ($userKitchenId && $header['kitchen_id'] != $userKitchenId) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal: Invoice ini BUKAN produksi dari dapur Anda!']); exit;
        }

        if ($header['status'] === 'expired') {
            echo json_encode(['status' => 'error', 'message' => 'Seluruh produk di invoice ini SUDAH HABIS ditarik (Expired)!']); exit;
        }
        if ($header['status'] !== 'masuk_gudang') {
            echo json_encode(['status' => 'error', 'message' => 'Gagal: Invoice ini berstatus "'.$header['status'].'", belum divalidasi masuk Store.']); exit;
        }

        $real_invoice_no = $header['invoice_no'];
        $tgl_produksi = $header['tgl_produksi'];

        $stmtDetail = $pdo->prepare("
            SELECT pr.id as product_id, pr.name as product_name, pr.code, d.quantity as original_qty
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            WHERE p.invoice_no = ?
        ");
        $stmtDetail->execute([$real_invoice_no]);
        $details_raw = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        $details_final = [];
        $total_sisa_semua = 0; 
        
        foreach ($details_raw as $d) {
            $cek_out = $pdo->prepare("SELECT SUM(quantity) FROM product_outs WHERE origin_invoice = ? AND product_id = ?");
            $cek_out->execute([$real_invoice_no, $d['product_id']]);
            $total_out = $cek_out->fetchColumn() ?: 0;

            $sisa = $d['original_qty'] - $total_out;
            if ($sisa > 0) {
                $d['sisa'] = $sisa;
                $details_final[] = $d;
                $total_sisa_semua += $sisa;
            }
        }

        if ($total_sisa_semua <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Seluruh produk di dalam invoice tersebut sudah habis ditarik!']); exit;
        }

        echo json_encode(['status' => 'success', 'invoice_no' => $real_invoice_no, 'tgl_produksi' => $tgl_produksi, 'details' => $details_final]);
        exit;
    }

    if ($action === 'save') {
        $origin_invoice = $_POST['origin_invoice'];
        $employee_id = $_POST['employee_id']; 
        $reason = $_POST['reason'];
        $notes = trim($_POST['notes'] ?? '');

        $product_ids = $_POST['product_id'] ?? []; 
        $quantities = $_POST['quantity'] ?? []; 

        if (empty($origin_invoice) || empty($employee_id) || empty($product_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Data form tidak lengkap!']); exit;
        }

        $pdo->beginTransaction();
        
        $out_invoice_no = "OUT-" . date('YmdHis') . "-" . strtoupper(substr(uniqid(), -4));
        $has_valid_item = false;

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity_to_pull = (int)($quantities[$i] ?? 0);

            if ($quantity_to_pull <= 0) continue; 

            $stmt = $pdo->prepare("
                SELECT d.quantity as original_qty
                FROM productions p
                JOIN production_details d ON p.id = d.production_id
                WHERE p.invoice_no = ? AND d.product_id = ?
                LIMIT 1
            ");
            $stmt->execute([$origin_invoice, $product_id]);
            $prod = $stmt->fetch();

            if (!$prod) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => "Produk ID $product_id tidak ditemukan di invoice ini."]); exit;
            }

            $cek_out = $pdo->prepare("SELECT SUM(quantity) FROM product_outs WHERE origin_invoice = ? AND product_id = ?");
            $cek_out->execute([$origin_invoice, $product_id]);
            $total_out = $cek_out->fetchColumn() ?: 0;
            $sisa = $prod['original_qty'] - $total_out;

            if ($quantity_to_pull > $sisa) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => "GAGAL! Anda mencoba menarik $quantity_to_pull Pcs produk, padahal sisanya tinggal $sisa Pcs."]); exit;
            }

            $insert = $pdo->prepare("INSERT INTO product_outs (invoice_no, origin_invoice, user_id, employee_id, product_id, quantity, reason, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$out_invoice_no, $origin_invoice, $user_id, $employee_id, $product_id, $quantity_to_pull, $reason, $notes]);

            // Potong Stok Produk Jadi (Store)
            $update_stok = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
            $update_stok->execute([$quantity_to_pull, $product_id]);

            $has_valid_item = true;
        }

        if (!$has_valid_item) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Anda belum mengisi jumlah penarikan (minimal 1 produk harus diisi angkanya)!']); exit;
        }

        $cek_total_awal_inv = $pdo->prepare("SELECT SUM(d.quantity) FROM productions p JOIN production_details d ON p.id = d.production_id WHERE p.invoice_no = ?");
        $cek_total_awal_inv->execute([$origin_invoice]);
        $total_awal_invoice = $cek_total_awal_inv->fetchColumn() ?: 0;

        $cek_total_tarik_inv = $pdo->prepare("SELECT SUM(quantity) FROM product_outs WHERE origin_invoice = ?");
        $cek_total_tarik_inv->execute([$origin_invoice]);
        $total_tarik_invoice = $cek_total_tarik_inv->fetchColumn() ?: 0;

        if ($total_tarik_invoice >= $total_awal_invoice) {
            $upd_prod = $pdo->prepare("UPDATE productions SET status = 'expired' WHERE invoice_no = ?");
            $upd_prod->execute([$origin_invoice]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Semua penarikan produk berhasil dicatat!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>