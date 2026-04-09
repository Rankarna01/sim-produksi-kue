<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('stok_opname');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    if ($action === 'get_materials') {
        $stmt = $pdo->query("SELECT id, code, name, stock, unit FROM materials ORDER BY name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'read_history') {
        // Kolom opname_no ditambahkan ke select query
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
        $material_ids = $_POST['material_id'] ?? [];
        $system_stocks = $_POST['system_stock'] ?? [];
        $actual_stocks = $_POST['actual_stock'] ?? [];
        $reason = trim($_POST['reason'] ?? ''); // Boleh kosong

        if (empty($material_ids) || count($material_ids) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada bahan baku yang dipilih!']);
            exit;
        }

        $pdo->beginTransaction();

        // =================================================================
        // LOGIKA GENERATOR OPNAME NO (Format: SO-BulanTglThn-Urutan)
        // =================================================================
        $arr_bulan = [1=>'A', 2=>'B', 3=>'C', 4=>'D', 5=>'E', 6=>'F', 7=>'G', 8=>'H', 9=>'I', 10=>'J', 11=>'K', 12=>'L'];
        $kode_bulan = $arr_bulan[(int)date('n')]; 
        $tgl_hari_ini = date('d'); 
        $tahun = date('y'); 

        $prefix = "SO-{$kode_bulan}{$tgl_hari_ini}{$tahun}-"; // Contoh: SO-D0526-
        
        $stmtCek = $pdo->prepare("SELECT opname_no FROM material_opnames WHERE opname_no LIKE ? ORDER BY opname_no DESC LIMIT 1");
        $stmtCek->execute([$prefix . "%"]);
        $lastInvoice = $stmtCek->fetchColumn();

        if ($lastInvoice) {
            $lastUrut = (int) substr($lastInvoice, -3);
            $nextUrut = $lastUrut + 1;
        } else {
            $nextUrut = 1;
        }
        
        $urutan_str = str_pad($nextUrut, 3, '0', STR_PAD_LEFT); 
        $opname_no = $prefix . $urutan_str; // Hasil: SO-D0526-001
        // =================================================================

        $processed_count = 0;

        $stmtLog = $pdo->prepare("INSERT INTO material_opnames (opname_no, material_id, user_id, system_stock, actual_stock, difference, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtUpdate = $pdo->prepare("UPDATE materials SET stock = ? WHERE id = ?");

        // Looping semua item yang dikirim
        for ($i = 0; $i < count($material_ids); $i++) {
            $mat_id = $material_ids[$i];
            $sys_stk = (float)str_replace(',', '.', $system_stocks[$i]);
            $act_stk = (float)str_replace(',', '.', $actual_stocks[$i]);
            
            $difference = $act_stk - $sys_stk;

            // Jika ada selisih, baru dicatat dan diupdate. Jika pas 0, lewati.
            if ($difference != 0) {
                $stmtLog->execute([$opname_no, $mat_id, $user_id, $sys_stk, $act_stk, $difference, $reason]);
                $stmtUpdate->execute([$act_stk, $mat_id]);
                $processed_count++;
            }
        }

        $pdo->commit();

        if ($processed_count > 0) {
            echo json_encode(['status' => 'success', 'message' => "Dokumen $opname_no diposting. $processed_count item berhasil disesuaikan!"]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Opname diproses, tapi tidak ada selisih stok (semua fisik cocok dengan sistem).']);
        }
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>