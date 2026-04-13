<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. READ ANTREAN PERMINTAAN
    if ($action === 'read_permintaan') {
        // PERBAIKAN: Mengubah JOIN dari warehouses w menjadi kitchens k
        $stmt = $pdo->query("
            SELECT mr.*, ms.material_name, ms.unit, ms.sku_code, k.name as nama_dapur 
            FROM material_requests mr
            JOIN materials_stocks ms ON mr.material_id = ms.id
            JOIN kitchens k ON mr.warehouse_id = k.id
            WHERE mr.status = 'menunggu'
            ORDER BY mr.created_at ASC
        ");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 2. PROSES ACC (SETUJUI) & KIRIM BARANG
    if ($action === 'proses_kirim') {
        $id = $_POST['id'];
        $qty_approved = (float)$_POST['qty_approved'];

        if ($qty_approved <= 0) {
            throw new Exception("Jumlah yang disetujui tidak boleh 0 atau minus!");
        }

        $pdo->beginTransaction();

        // Ambil data pengajuan
        $stmtReq = $pdo->prepare("SELECT * FROM material_requests WHERE id = ? FOR UPDATE");
        $stmtReq->execute([$id]);
        $req = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if (!$req || $req['status'] !== 'menunggu') {
            throw new Exception("Pengajuan tidak valid atau sudah diproses.");
        }

        // Ambil data Gudang Pilar
        $stmtPilar = $pdo->prepare("SELECT sku_code, material_name, unit, total_stock FROM materials_stocks WHERE id = ? FOR UPDATE");
        $stmtPilar->execute([$req['material_id']]);
        $pilar = $stmtPilar->fetch(PDO::FETCH_ASSOC);

        if ($pilar['total_stock'] < $qty_approved) {
            throw new Exception("Gagal! Stok Pilar tidak mencukupi. Sisa stok: " . $pilar['total_stock'] . " " . $pilar['unit']);
        }

        // Kurangi Stok Pilar
        $upPilar = $pdo->prepare("UPDATE materials_stocks SET total_stock = total_stock - ? WHERE id = ?");
        $upPilar->execute([$qty_approved, $req['material_id']]);

        // LOGIKA UPSERT STOK DAPUR
        $cekDapur = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND warehouse_id = ?");
        $cekDapur->execute([$pilar['sku_code'], $req['warehouse_id']]);
        $bahanDapur = $cekDapur->fetch(PDO::FETCH_ASSOC);

        if ($bahanDapur) {
            // Update jika bahan sudah ada di dapur
            $upDapur = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
            $upDapur->execute([$qty_approved, $bahanDapur['id']]);
        } else {
            // Insert jika bahan belum ada di dapur
            $insDapur = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock, warehouse_id) VALUES (?, ?, ?, ?, ?, ?)");
            $insDapur->execute([
                $pilar['sku_code'], 
                $pilar['material_name'], 
                $pilar['unit'], 
                $qty_approved, 
                10, 
                $req['warehouse_id']
            ]);
        }

        // Update status request
        $upReq = $pdo->prepare("UPDATE material_requests SET qty_approved = ?, status = 'diproses', processed_at = NOW() WHERE id = ?");
        $upReq->execute([$qty_approved, $id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dikirim dan stok dapur bertambah!']);
        exit;
    }

    // 3. FITUR BARU: TOLAK PERMINTAAN
    if ($action === 'tolak_kirim') {
        $id = $_POST['id'];

        $pdo->beginTransaction();
        
        $stmtReq = $pdo->prepare("SELECT status FROM material_requests WHERE id = ? FOR UPDATE");
        $stmtReq->execute([$id]);
        $req = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if (!$req || $req['status'] !== 'menunggu') {
            throw new Exception("Pengajuan tidak valid atau sudah diproses.");
        }

        $upReq = $pdo->prepare("UPDATE material_requests SET status = 'ditolak', processed_at = NOW() WHERE id = ?");
        $upReq->execute([$id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil ditolak!']);
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Kesalahan Database: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>