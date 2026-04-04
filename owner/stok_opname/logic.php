<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    // 1. Ambil List Bahan Baku untuk Dropdown
    if ($action === 'get_materials') {
        $stmt = $pdo->query("SELECT id, code, name, stock, unit FROM materials ORDER BY name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 2. Baca Riwayat Opname
    if ($action === 'read_history') {
        $stmt = $pdo->query("
            SELECT mo.id, mo.system_stock, mo.actual_stock, mo.difference, mo.reason, mo.created_at,
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

    // 3. Simpan Opname Baru (Update Master & Catat Log)
    if ($action === 'save') {
        $material_id = $_POST['material_id'] ?? '';
        $system_stock = (float)str_replace(',', '.', $_POST['system_stock'] ?? 0);
        $actual_stock = (float)str_replace(',', '.', $_POST['actual_stock'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (empty($material_id) || empty($reason) || !isset($_POST['actual_stock'])) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
            exit;
        }

        $difference = $actual_stock - $system_stock;

        // Validasi: Jika tidak ada selisih, buat apa dicatat?
        if ($difference == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Stok Fisik sama dengan Stok Sistem. Tidak ada penyesuaian yang perlu dicatat.']);
            exit;
        }

        $pdo->beginTransaction();

        // A. Catat ke tabel history (material_opnames)
        $stmtLog = $pdo->prepare("
            INSERT INTO material_opnames (material_id, user_id, system_stock, actual_stock, difference, reason) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtLog->execute([$material_id, $user_id, $system_stock, $actual_stock, $difference, $reason]);

        // B. Timpa (Update) stok di tabel materials menjadi stok aktual
        $stmtUpdate = $pdo->prepare("UPDATE materials SET stock = ? WHERE id = ?");
        $stmtUpdate->execute([$actual_stock, $material_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stok berhasil disesuaikan!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>