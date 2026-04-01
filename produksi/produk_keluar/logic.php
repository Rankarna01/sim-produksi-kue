<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['produksi']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    // ROUTE BARU: Ambil Daftar Pegawai
    if ($action === 'get_employees') {
        $emp = $pdo->query("SELECT id, name FROM employees ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $emp]);
        exit;
    }

    if ($action === 'read') {
        $stmt = $pdo->query("
            SELECT o.id, o.invoice_no, o.origin_invoice, o.created_at, o.quantity, o.reason, o.notes, 
                   pr.name as product_name, pr.code, COALESCE(e.name, u.name) as karyawan
            FROM product_outs o
            JOIN products pr ON o.product_id = pr.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN employees e ON o.employee_id = e.id
            ORDER BY o.id DESC LIMIT 100
        ");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'search_invoice') {
        $inv = trim($_GET['inv'] ?? '');
        $stmt = $pdo->prepare("
            SELECT p.invoice_no, pr.id as product_id, pr.name as product_name, d.quantity as original_qty, p.status, d.barcode
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            WHERE (p.invoice_no = ? OR d.barcode = ?)
            LIMIT 1
        ");
        $stmt->execute([$inv, $inv]);
        $data = $stmt->fetch();

        if (!$data) {
            echo json_encode(['status' => 'error', 'message' => 'Struk / Barcode tidak ditemukan di database!']); exit;
        }
        if ($data['status'] === 'expired') {
            echo json_encode(['status' => 'error', 'message' => 'Seluruh produk di struk ini SUDAH HABIS ditarik (Status: Expired)!']); exit;
        }
        if ($data['status'] !== 'masuk_gudang') {
            echo json_encode(['status' => 'error', 'message' => 'Gagal: Struk ini berstatus "'.$data['status'].'", belum valid masuk gudang.']); exit;
        }

        $cek_out = $pdo->prepare("SELECT SUM(quantity) as total_out FROM product_outs WHERE origin_invoice = ? AND product_id = ?");
        $cek_out->execute([$data['invoice_no'], $data['product_id']]);
        $total_out = $cek_out->fetchColumn() ?: 0;

        $sisa = $data['original_qty'] - $total_out;
        if ($sisa <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Produk ini dari struk tersebut sudah habis ditarik!']); exit;
        }

        $data['sisa'] = $sisa;
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($action === 'save') {
        $origin_invoice = $_POST['origin_invoice'];
        $product_id = $_POST['product_id'];
        $employee_id = $_POST['employee_id']; // TANGKAP ID PEGAWAI
        $quantity_to_pull = (int)$_POST['quantity'];
        $reason = $_POST['reason'];
        $notes = trim($_POST['notes'] ?? '');

        if (empty($origin_invoice) || empty($product_id) || $quantity_to_pull <= 0 || empty($reason) || empty($employee_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap! Pastikan nama pegawai dipilih.']);
            exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT d.quantity as original_qty
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            WHERE p.invoice_no = ? AND d.product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$origin_invoice, $product_id]);
        $prod = $stmt->fetch();

        $cek_out = $pdo->prepare("SELECT SUM(quantity) as total_out FROM product_outs WHERE origin_invoice = ? AND product_id = ?");
        $cek_out->execute([$origin_invoice, $product_id]);
        $total_out = $cek_out->fetchColumn() ?: 0;

        $sisa = $prod['original_qty'] - $total_out;

        if ($quantity_to_pull > $sisa) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => "GAGAL! Anda mencoba menarik {$quantity_to_pull} Pcs, padahal sisa yang belum ditarik tinggal {$sisa} Pcs."]);
            exit;
        }

        $out_invoice_no = "OUT-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));
        
        // INSERT DENGAN EMPLOYEE_ID
        $insert = $pdo->prepare("INSERT INTO product_outs (invoice_no, origin_invoice, user_id, employee_id, product_id, quantity, reason, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$out_invoice_no, $origin_invoice, $user_id, $employee_id, $product_id, $quantity_to_pull, $reason, $notes]);

        $update_stok = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
        $update_stok->execute([$quantity_to_pull, $product_id]);

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
        echo json_encode(['status' => 'success', 'message' => 'Penarikan berhasil dicatat!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Sistem Error: ' . $e->getMessage()]);
}
?>