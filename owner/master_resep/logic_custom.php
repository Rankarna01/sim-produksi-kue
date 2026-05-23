<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_resep');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ============================================================
        // READ: Ambil semua item custom dari POS + hitung jumlah bahan
        // ============================================================
        case 'read_custom_items':
            $stmt = $pdo->query("
                SELECT 
                    sci.id,
                    sci.name,
                    sci.price,
                    COUNT(bc.id) as total_bahan
                FROM saved_custom_items_pos sci
                LEFT JOIN bom_custom bc ON bc.custom_item_id = sci.id
                GROUP BY sci.id, sci.name, sci.price
                ORDER BY sci.name ASC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // READ: Baca BOM (resep) untuk 1 item custom
        // ============================================================
        case 'read_bom_custom':
            $custom_item_id = (int)($_GET['custom_item_id'] ?? 0);
            if (!$custom_item_id) { echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']); break; }

            $stmt = $pdo->prepare("
                SELECT 
                    bc.id, 
                    ms.id as material_id, 
                    ms.material_name as name, 
                    bc.quantity_needed, 
                    bc.unit_used
                FROM bom_custom bc
                JOIN materials_stocks ms ON bc.material_id = ms.id
                WHERE bc.custom_item_id = ?
                ORDER BY ms.material_name ASC
            ");
            $stmt->execute([$custom_item_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // READ: Dropdown bahan baku
        // ============================================================
        case 'get_materials':
            $stmt = $pdo->query("SELECT id, material_name as name, unit FROM materials_stocks ORDER BY material_name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // READ: Dropdown satuan
        // ============================================================
        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // WRITE: Simpan/Replace resep item custom (langsung, tanpa approval)
        // ============================================================
        case 'save_bom_custom':
            $custom_item_id = (int)($_POST['custom_item_id'] ?? 0);
            $drafts = json_decode($_POST['drafts'] ?? '[]', true);

            if (!$custom_item_id) {
                echo json_encode(['status' => 'error', 'message' => 'ID item custom tidak valid!']); exit;
            }
            if (empty($drafts)) {
                echo json_encode(['status' => 'error', 'message' => 'Resep tidak boleh kosong!']); exit;
            }

            // Validasi: cek apakah custom_item_id ada di tabel POS
            $cek = $pdo->prepare("SELECT id FROM saved_custom_items_pos WHERE id = ?");
            $cek->execute([$custom_item_id]);
            if (!$cek->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Item custom tidak ditemukan di database POS!']); exit;
            }

            $pdo->beginTransaction();

            // 1. Hapus resep lama untuk item ini
            $pdo->prepare("DELETE FROM bom_custom WHERE custom_item_id = ?")->execute([$custom_item_id]);

            // 2. Insert resep baru
            $stmtInsert = $pdo->prepare("
                INSERT INTO bom_custom (custom_item_id, material_id, quantity_needed, unit_used) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($drafts as $d) {
                $stmtInsert->execute([
                    $custom_item_id,
                    (int)$d['material_id'],
                    (float)$d['quantity_needed'],
                    $d['unit_used']
                ]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Resep item custom berhasil disimpan!']);
            break;

        // ============================================================
        // WRITE: Hapus seluruh resep item custom
        // ============================================================
        case 'delete_bom_custom':
            $custom_item_id = (int)($_POST['custom_item_id'] ?? 0);
            if (!$custom_item_id) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']); exit;
            }
            $pdo->prepare("DELETE FROM bom_custom WHERE custom_item_id = ?")->execute([$custom_item_id]);
            echo json_encode(['status' => 'success', 'message' => 'Resep item custom berhasil dihapus.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenali!']);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>
