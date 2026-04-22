<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_po');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. INIT FORM (Tarik PR Pending & Data Master)
    if ($action === 'init_form') {
        $sqlPR = "SELECT pr.*, ms.material_name, ms.unit, u.name as requested_by_name 
                  FROM purchase_requests pr 
                  JOIN materials_stocks ms ON pr.material_id = ms.id 
                  JOIN users u ON pr.user_id = u.id 
                  WHERE pr.status = 'pending' ORDER BY pr.created_at ASC";
        $prPending = $pdo->query($sqlPR)->fetchAll(PDO::FETCH_ASSOC);

        $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $materials = $pdo->query("SELECT id, material_name, unit, sku_code FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'pr_pending' => $prPending,
            'suppliers' => $suppliers,
            'materials' => $materials
        ]);
        exit;
    }

    // 2. SIMPAN PO BARU & UPDATE STATUS PR
    if ($action === 'save_po') {
        $supplier_id = $_POST['supplier_id'] ?? '';
        $shipping_date = $_POST['shipping_date'] ?? '';
        $cart = json_decode($_POST['cart'], true);
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($supplier_id) || empty($shipping_date) || empty($cart)) {
            echo json_encode(['status' => 'error', 'message' => 'Supplier, Tanggal, dan Item Barang wajib diisi!']); exit;
        }

        $pdo->beginTransaction();

        $stmtSetting = $pdo->query("SELECT req_approval_po FROM store_profile WHERE id = 1");
        $req_approval = $stmtSetting->fetchColumn() ?? 1;

        $status_po = ($req_approval == 1) ? 'waiting_approval' : 'approved';

        $po_no = "LC-" . date('dmY') . "-" . rand(1000, 9999);

        // Insert ke DB dengan status print awal "unlocked"
        $stmtPO = $pdo->prepare("INSERT INTO purchase_orders (po_no, supplier_id, shipping_date, status, created_by, print_po_status, print_terima_status) VALUES (?, ?, ?, ?, ?, 'unlocked', 'unlocked')");
        $stmtPO->execute([$po_no, $supplier_id, $shipping_date, $status_po, $user_id]);
        $po_id = $pdo->lastInsertId();

        $stmtDetail = $pdo->prepare("INSERT INTO purchase_order_details (po_id, material_id, qty, price) VALUES (?, ?, ?, 0)"); 
        $stmtUpPR = $pdo->prepare("UPDATE purchase_requests SET status = 'processing', po_id = ? WHERE id = ?");

        foreach ($cart as $item) {
            $stmtDetail->execute([$po_id, $item['material_id'], $item['qty']]);
            
            if (!empty($item['pr_id'])) {
                $stmtUpPR->execute([$po_id, $item['pr_id']]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'PO Berhasil diterbitkan dan dikirim ke Manager untuk persetujuan.']);
        exit;
    }

    // 3. BACA DATA PO (HALAMAN LIST)
    if ($action === 'read_po') {
        $tab = $_GET['tab'] ?? 'semua';
        $search = $_GET['search'] ?? '';
        $where = "WHERE 1=1";
        $params = [];
        
        if ($tab === 'belum_terima') {
            $where .= " AND p.status IN ('waiting_approval', 'approved', 'processing')";
        } elseif ($tab === 'sudah_terima') {
            $where .= " AND p.status = 'received'";
        } elseif ($tab === 'dibatalkan') {
            $where .= " AND p.status IN ('rejected', 'cancelled')";
        }

        if (!empty($search)) {
            $where .= " AND (p.po_no LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql = "
            SELECT p.*, s.name as supplier_name, u.name as admin_name,
                   (SELECT COUNT(*) FROM purchase_order_details WHERE po_id = p.id) as total_items,
                   (SELECT material_name FROM materials_stocks WHERE id = (SELECT material_id FROM purchase_order_details WHERE po_id = p.id LIMIT 1)) as sample_item
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.id
            JOIN users u ON p.created_by = u.id
            $where
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 4. AMBIL DATA PO UNTUK MODAL TERIMA BARANG
    if ($action === 'get_po_receive') {
        $po_id = $_GET['po_id'] ?? '';
        
        $sqlDetail = "SELECT pod.*, ms.material_name, ms.sku_code, ms.unit FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?";
        $stmtDetail = $pdo->prepare($sqlDetail);
        $stmtDetail->execute([$po_id]);
        $items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
        
        $materials = $pdo->query("SELECT id, material_name, unit, sku_code FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'items' => $items, 'materials' => $materials]);
        exit;
    }

    // 5. SIMPAN HASIL TERIMA BARANG
    if ($action === 'save_receive_po') {
        $po_id = $_POST['po_id'] ?? '';
        $items = json_decode($_POST['items'], true); 
        $user_id = $_SESSION['user_id'] ?? 1;

        $pdo->beginTransaction();

        $stmtPoNo = $pdo->prepare("SELECT po_no, supplier_id FROM purchase_orders WHERE id = ?");
        $stmtPoNo->execute([$po_id]);
        $poData = $stmtPoNo->fetch(PDO::FETCH_ASSOC);
        $po_no = $poData['po_no'];
        $supplier_id = $poData['supplier_id'];

        $pdo->prepare("DELETE FROM purchase_order_details WHERE po_id = ?")->execute([$po_id]);

        $stmtInsertDetail = $pdo->prepare("INSERT INTO purchase_order_details (po_id, material_id, qty, price) VALUES (?, ?, ?, ?)");
        $stmtUpdateStock = $pdo->prepare("UPDATE materials_stocks SET stock = stock + ?, expiry_date = ? WHERE id = ?");
        $stmtUpdNoExp = $pdo->prepare("UPDATE materials_stocks SET stock = stock + ? WHERE id = ?");
        $stmtHistory = $pdo->prepare("INSERT INTO barang_masuk (transaction_no, material_id, supplier_id, qty, source, po_id, expiry_date, notes, user_id) VALUES (?, ?, ?, ?, 'PO', ?, ?, 'Penerimaan PO', ?)");

        $total_po_amount = 0;

        foreach ($items as $item) {
            $mat_id = $item['material_id'];
            $qty = (float)$item['qty_terima'];
            $price = (float)$item['price']; 
            $exp_date = !empty($item['exp_date']) ? $item['exp_date'] : null;

            $total_po_amount += ($qty * $price);

            $stmtInsertDetail->execute([$po_id, $mat_id, $qty, $price]);

            if ($exp_date) {
                $stmtUpdateStock->execute([$qty, $exp_date, $mat_id]);
            } else {
                $stmtUpdNoExp->execute([$qty, $mat_id]);
            }

            $stmtHistory->execute([$po_no, $mat_id, $supplier_id, $qty, $po_id, $exp_date, $user_id]);
        }

        $stmtPO = $pdo->prepare("UPDATE purchase_orders SET status = 'received', total_amount = ?, payment_status = 'unpaid', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtPO->execute([$total_po_amount, $po_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Barang berhasil diterima. Tagihan otomatis tercatat di sistem Keuangan!']);
        exit;
    }

    // ==========================================
    // LOGIC KUNCI CETAK & IZIN CETAK (TERPISAH PO & TERIMA)
    // ==========================================

    // A. KUNCI SAAT DICETAK 1X
    if ($action === 'mark_printed') {
        $id = $_POST['id'] ?? '';
        $tipe = $_POST['tipe'] ?? ''; // 'po' atau 'terima'
        
        $column = ($tipe === 'terima') ? 'print_terima_status' : 'print_po_status';

        $stmt = $pdo->prepare("SELECT $column FROM purchase_orders WHERE id = ?");
        $stmt->execute([$id]);
        $status_cetak = $stmt->fetchColumn();

        if ($status_cetak === 'unlocked') {
            $update = $pdo->prepare("UPDATE purchase_orders SET $column = 'locked' WHERE id = ?");
            $update->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Dokumen terkunci setelah dicetak.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dokumen ini sudah terkunci. Butuh persetujuan.']);
        }
        exit;
    }

    // B. AJUKAN IZIN CETAK ULANG
    if ($action === 'request_print') {
        $id = $_POST['id'] ?? '';
        $tipe = $_POST['tipe'] ?? ''; 
        
        // Cek SOP Toko
        $stmtSetting = $pdo->query("SELECT req_approval_print FROM store_profile WHERE id = 1");
        $req_approval = $stmtSetting->fetchColumn() ?? 1;

        $column = ($tipe === 'terima') ? 'print_terima_status' : 'print_po_status';
        $status_baru = ($req_approval == 1) ? 'pending_approval' : 'unlocked';

        $update = $pdo->prepare("UPDATE purchase_orders SET $column = ? WHERE id = ?");
        $update->execute([$status_baru, $id]);
        
        $msg = ($req_approval == 1) ? 'Izin cetak ulang telah diajukan ke Manager.' : 'Akses cetak langsung dibuka (Auto-Approve).';
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>