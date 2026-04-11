<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('stok_opname');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    // FUNGSI VERIFIKASI PIN DARI OWNER
    if ($action === 'verify_pin') {
        $pin = $_POST['pin'] ?? '';
        
        // Cari PIN yang belum dipakai dan belum expired (dalam kurun waktu 24 jam)
        $stmt = $pdo->prepare("SELECT id FROM access_codes 
                               WHERE auth_code = ? AND is_used = 0 AND valid_until > NOW() 
                               LIMIT 1");
        $stmt->execute([$pin]);
        $data = $stmt->fetch();

        if ($data) {
            // Tandai kode sudah dipakai agar tidak bisa digunakan lagi oleh orang lain
            $update = $pdo->prepare("UPDATE access_codes SET is_used = 1 WHERE id = ?");
            $update->execute([$data['id']]);

            $_SESSION['opname_unlocked'] = true;
            echo json_encode(['status' => 'success', 'message' => 'Akses Terverifikasi!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kode PIN salah, sudah digunakan, atau sudah Expired (24 Jam)!']);
        }
        exit;
    }

    if ($action === 'get_materials') {
        $stmt = $pdo->query("SELECT id, code, name, stock, unit FROM materials ORDER BY name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'read_history') {
        $stmt = $pdo->query("
            SELECT mo.id, mo.opname_no, mo.system_stock, mo.actual_stock, mo.difference, mo.reason, mo.created_at,
                   m.name as material_name, m.code, m.unit,
                   u.name as petugas
            FROM material_opnames mo
            JOIN materials m ON mo.material_id = m.id
            JOIN users u ON mo.user_id = u.id
            ORDER BY mo.created_at DESC
            LIMIT 100
        ");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'save') {
        // Double Check Protection
        if (!isset($_SESSION['opname_unlocked']) || $_SESSION['opname_unlocked'] !== true) {
            echo json_encode(['status' => 'error', 'message' => 'Sesi otorisasi habis. Silakan refresh halaman.']); exit;
        }

        $material_ids = $_POST['material_id'] ?? [];
        $system_stocks = $_POST['system_stock'] ?? [];
        $actual_stocks = $_POST['actual_stock'] ?? [];
        $reason = trim($_POST['reason'] ?? '');

        if (empty($material_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada bahan baku yang dipilih!']); exit;
        }

        $pdo->beginTransaction();

        $arr_bulan = [1=>'A', 2=>'B', 3=>'C', 4=>'D', 5=>'E', 6=>'F', 7=>'G', 8=>'H', 9=>'I', 10=>'J', 11=>'K', 12=>'L'];
        $prefix = "SO-" . $arr_bulan[(int)date('n')] . date('dy') . "-"; 
        
        $stmtCek = $pdo->prepare("SELECT opname_no FROM material_opnames WHERE opname_no LIKE ? ORDER BY opname_no DESC LIMIT 1");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();
        $nextUrut = $lastInvoice ? ((int) substr($lastInvoice, -3)) + 1 : 1;
        $opname_no = $prefix . str_pad($nextUrut, 3, '0', STR_PAD_LEFT);

        $stmtLog = $pdo->prepare("INSERT INTO material_opnames (opname_no, material_id, user_id, system_stock, actual_stock, difference, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUpdate = $pdo->prepare("UPDATE materials SET stock = ? WHERE id = ?");

        $processed_count = 0;
        for ($i = 0; $i < count($material_ids); $i++) {
            $mat_id = $material_ids[$i];
            $sys_stk = (float)str_replace(',', '.', $system_stocks[$i]);
            $act_stk = (float)str_replace(',', '.', $actual_stocks[$i]);
            $difference = $act_stk - $sys_stk;

            if ($difference != 0) {
                $stmtLog->execute([$opname_no, $mat_id, $user_id, $sys_stk, $act_stk, $difference, $reason]);
                $stmtUpdate->execute([$act_stk, $mat_id]);
                $processed_count++;
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Dokumen $opname_no diposting!"]);
        exit;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}