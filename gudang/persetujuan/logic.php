<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('persetujuan');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // ==========================================================
    // 1-5. LOGIC PERMINTAAN PEMBELIAN (PR)
    // ==========================================================
    if ($action === 'read_permintaan') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->query("SELECT COUNT(id) FROM purchase_requests WHERE status = 'pending'");
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT pr.*, ms.material_name, ms.unit, ms.sku_code, u.name as requested_by_name 
            FROM purchase_requests pr
            JOIN materials_stocks ms ON pr.material_id = ms.id
            JOIN users u ON pr.user_id = u.id
            WHERE pr.status = 'pending'
            ORDER BY pr.created_at ASC
            LIMIT $limit OFFSET $offset
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data, 'current_page' => $page, 'total_pages' => $total_pages]);
        exit;
    }

    if ($action === 'get_materials') {
        $stmt = $pdo->query("SELECT id, material_name, unit, stock FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'submit_request') {
        $material_id = $_POST['material_id'] ?? '';
        $qty = (float)($_POST['qty'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($material_id) || $qty <= 0) { echo json_encode(['status' => 'error', 'message' => 'Data tidak valid!']); exit; }

        // Cek SOP Toko
        $stmtSetting = $pdo->query("SELECT req_approval_pr FROM store_profile WHERE id = 1");
        $req_approval = $stmtSetting->fetchColumn() ?? 1;
        
        $status_pr = ($req_approval == 1) ? 'pending' : 'processed';

        $req_no = "PR-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -3));
        
        // Pastikan tabel purchase_requests bisa menerima insert status
        $stmt = $pdo->prepare("INSERT INTO purchase_requests (request_no, material_id, qty, notes, user_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$req_no, $material_id, $qty, $notes, $user_id, $status_pr]);
        
        $msg = ($req_approval == 1) ? 'Permintaan diajukan!' : 'Permintaan langsung diproses (Auto-Approve)!';
        echo json_encode(['status' => 'success', 'message' => $msg]); exit;
    }

    if ($action === 'proses_ke_po') {
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("UPDATE purchase_requests SET status = 'processed' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Permintaan siap dibuatkan PO!']); exit;
    }

    if ($action === 'tolak_permintaan') {
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("UPDATE purchase_requests SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Permintaan ditolak.']); exit;
    }

    // ==========================================================
    // 6-8. LOGIC PERSETUJUAN PO 
    // ==========================================================
    if ($action === 'read_po_approval') {
        $sql = "
            SELECT p.*, s.name as supplier_name, u.name as admin_name,
                   (SELECT COUNT(*) FROM purchase_order_details WHERE po_id = p.id) as total_items,
                   (SELECT material_name FROM materials_stocks WHERE id = (SELECT material_id FROM purchase_order_details WHERE po_id = p.id LIMIT 1)) as sample_item
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.id
            JOIN users u ON p.created_by = u.id
            WHERE p.status = 'waiting_approval'
            ORDER BY p.created_at ASC
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]); exit;
    }

    if ($action === 'get_po_detail') {
        $po_id = $_GET['po_id'] ?? '';
        $sql = "SELECT pod.*, ms.material_name, ms.sku_code, ms.unit FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$po_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
    }

    if ($action === 'update_po_status') {
        $po_id = $_POST['po_id'] ?? '';
        $new_status = $_POST['status'] ?? ''; 
        $user_id = $_SESSION['user_id'] ?? 1;

        $pdo->beginTransaction();
        try {
            if ($new_status === 'approved' && isset($_POST['detail_id']) && isset($_POST['qty']) && isset($_POST['price'])) {
                $detail_ids = $_POST['detail_id'];
                $qtys = $_POST['qty'];
                $prices = $_POST['price'];
                $new_total_amount = 0;

                $stmtUpdateDetail = $pdo->prepare("UPDATE purchase_order_details SET qty = ? WHERE id = ? AND po_id = ?");
                for ($i = 0; $i < count($detail_ids); $i++) {
                    $d_id = $detail_ids[$i];
                    $d_qty = max(0, (float)$qtys[$i]);
                    $d_price = (float)$prices[$i];
                    $new_total_amount += ($d_qty * $d_price);
                    $stmtUpdateDetail->execute([$d_qty, $d_id, $po_id]);
                }
                $stmtUpdateTotal = $pdo->prepare("UPDATE purchase_orders SET total_amount = ? WHERE id = ?");
                $stmtUpdateTotal->execute([$new_total_amount, $po_id]);
            }
            $stmtStatus = $pdo->prepare("UPDATE purchase_orders SET status = ?, approved_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmtStatus->execute([$new_status, $user_id, $po_id]);

            $pdo->commit();
            $msg = $new_status === 'approved' ? 'PO Disetujui!' : 'PO Ditolak!';
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ==========================================================
    // 9-11. LOGIC PERSETUJUAN TRANSAKSI MANUAL (BARANG MASUK)
    // ==========================================================
    if ($action === 'read_manual') {
        $sql = "
            SELECT bm.*, ms.material_name, ms.unit, ms.sku_code, u.name as admin_name
            FROM barang_masuk bm
            JOIN materials_stocks ms ON bm.material_id = ms.id
            LEFT JOIN users u ON bm.user_id = u.id
            WHERE bm.status = 'pending' AND bm.source = 'Manual'
            ORDER BY bm.created_at ASC
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]); exit;
    }

    if ($action === 'approve_manual') {
        $id = $_POST['id'] ?? '';
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT material_id, qty, expiry_date FROM barang_masuk WHERE id = ? AND status = 'pending'");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new Exception("Data tidak ditemukan atau sudah diproses!");
            
            $pdo->prepare("UPDATE barang_masuk SET status = 'approved' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE materials_stocks SET stock = stock + ?, expiry_date = ? WHERE id = ?")->execute([$row['qty'], $row['expiry_date'], $row['material_id']]);
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Stok Masuk Manual Disetujui! Stok Gudang bertambah.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'reject_manual') {
        $id = $_POST['id'] ?? '';
        $pdo->prepare("UPDATE barang_masuk SET status = 'rejected' WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Transaksi dibatalkan.']); exit;
    }

    // ==========================================================
    // 12. LOGIC PERSETUJUAN BARANG KELUAR MANUAL (FITUR BARU)
    // ==========================================================
    if ($action === 'read_keluar') {
        $sql = "
            SELECT bk.*, ms.material_name, ms.unit, ms.sku_code, u.name as admin_name
            FROM barang_keluar bk
            JOIN materials_stocks ms ON bk.material_id = ms.id
            LEFT JOIN users u ON bk.user_id = u.id
            WHERE bk.approval_status = 'pending'
            ORDER BY bk.created_at ASC
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]); exit;
    }

    if ($action === 'approve_keluar') {
        $id = $_POST['id'] ?? '';
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT material_id, qty FROM barang_keluar WHERE id = ? AND approval_status = 'pending'");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new Exception("Data tidak ditemukan atau sudah diproses!");
            
            // Re-check Stok saat ini
            $stmtCek = $pdo->prepare("SELECT stock FROM materials_stocks WHERE id = ? FOR UPDATE");
            $stmtCek->execute([$row['material_id']]);
            $curr_stock = $stmtCek->fetchColumn();

            if ($curr_stock < $row['qty']) throw new Exception("Persetujuan Gagal! Stok fisik (".(float)$curr_stock.") tidak mencukupi.");

            $pdo->prepare("UPDATE materials_stocks SET stock = stock - ? WHERE id = ?")->execute([$row['qty'], $row['material_id']]);
            $pdo->prepare("UPDATE barang_keluar SET approval_status = 'approved' WHERE id = ?")->execute([$id]);
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Barang Keluar Disetujui! Stok Gudang telah dikurangi.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'reject_keluar') {
        $id = $_POST['id'] ?? '';
        $pdo->prepare("UPDATE barang_keluar SET approval_status = 'rejected' WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Transaksi Keluar ditolak. Stok tidak berubah.']); exit;
    }

    // ==========================================================
    // 13-14. LOGIC IZIN CETAK
    // ==========================================================
    if ($action === 'read_izin_cetak') {
        $sql = "SELECT p.id, p.po_no, p.print_po_status, p.print_terima_status, p.updated_at, s.name as supplier_name
                FROM purchase_orders p
                JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.print_po_status = 'pending_approval' OR p.print_terima_status = 'pending_approval'
                ORDER BY p.updated_at DESC";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]); exit;
    }

    if ($action === 'proses_izin_cetak') {
        $id = $_POST['id'] ?? '';
        $tipe = $_POST['tipe'] ?? ''; // 'po' atau 'terima'
        $keputusan = $_POST['keputusan'] ?? ''; // 'approve' atau 'reject'

        $kolom = ($tipe === 'po') ? 'print_po_status' : 'print_terima_status';
        $status_baru = ($keputusan === 'approve') ? 'unlocked' : 'locked';

        $stmt = $pdo->prepare("UPDATE purchase_orders SET $kolom = ? WHERE id = ?");
        $stmt->execute([$status_baru, $id]);
        
        $msg = $keputusan === 'approve' ? 'Akses Cetak Terbuka!' : 'Akses Cetak Ditolak!';
        echo json_encode(['status' => 'success', 'message' => $msg]); exit;
    }

    // ==========================================================
    // 15. LOGIC MASTER HISTORI PERSETUJUAN
    // ==========================================================
    if ($action === 'read_histori') {
        $modul = $_GET['modul'] ?? 'semua';
        $status = $_GET['status'] ?? 'semua';
        $search = $_GET['search'] ?? '';

        $query = "
            SELECT 'PR' as modul, request_no as ref_no, status, created_at as tgl_proses, notes as detail
            FROM purchase_requests WHERE status IN ('processed', 'rejected')
            UNION ALL
            SELECT 'PO' as modul, po_no as ref_no, status, updated_at as tgl_proses, 'PO ke Supplier' as detail
            FROM purchase_orders WHERE status IN ('approved', 'rejected')
            UNION ALL
            SELECT 'Masuk' as modul, transaction_no as ref_no, status, created_at as tgl_proses, notes as detail
            FROM barang_masuk WHERE source = 'Manual' AND status IN ('approved', 'rejected')
            UNION ALL
            SELECT 'Keluar' as modul, transaction_no as ref_no, approval_status as status, created_at as tgl_proses, notes as detail
            FROM barang_keluar WHERE approval_status IN ('approved', 'rejected')
        ";

        $finalSql = "SELECT * FROM ($query) as histori WHERE 1=1";
        $params = [];

        if ($modul !== 'semua') {
            $finalSql .= " AND modul = ?";
            $params[] = $modul;
        }
        if ($status !== 'semua') {
            if ($status === 'approved') {
                $finalSql .= " AND status IN ('processed', 'approved')";
            } else {
                $finalSql .= " AND status = 'rejected'";
            }
        }
        if (!empty($search)) {
            $finalSql .= " AND ref_no LIKE ?";
            $params[] = "%$search%";
        }
        
        $finalSql .= " ORDER BY tgl_proses DESC LIMIT 50";
        
        $stmt = $pdo->prepare($finalSql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]); exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>