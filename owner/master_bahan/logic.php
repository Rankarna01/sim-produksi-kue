<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_bahan');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// AMBIL SESSION KITCHEN ID
$user_kitchen_id = $_SESSION['kitchen_id'] ?? null;

try {
    switch ($action) {
        
        case 'read':
            // PROTEKSI GANDA
            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }

            if ($warehouse_id == 1) {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? OR warehouse_id IS NULL ORDER BY id DESC");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? ORDER BY id DESC");
            }
            $stmt->execute([$warehouse_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'save':
            checkPermission('edit_master_bahan');
            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            
            $raw_stock = $_POST['stock'] ?? 0;
            $raw_min_stock = $_POST['min_stock'] ?? 0;
            $stock = (float) str_replace(',', '.', $raw_stock);
            $min_stock = (float) str_replace(',', '.', $raw_min_stock);

            if (empty($code) || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode dan Nama wajib diisi!']); exit;
            }

            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'Pembuatan bahan baru harus melalui Fitur Pengajuan ke Gudang!']); exit;
            } else {
                // Saat edit, satuan (unit) tidak diupdate agar terkunci (lock) ke settingan awal (gudang)
                $stmt = $pdo->prepare("UPDATE materials SET code=?, name=?, stock=?, min_stock=? WHERE id=?");
                $stmt->execute([$code, $name, $stock, $min_stock, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil diperbarui! (Satuan dikunci)']);
            }
            break;

        case 'delete':
            checkPermission('hapus_master_bahan');
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil dihapus!']);
            break;

        case 'read_pilar':
            // Ambil data bahan aktif di Gudang Utama
            $stmt = $pdo->query("SELECT id, material_name, stock, unit FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'read_requests':
            // DITAMBAHKAN FITUR PAGINATION
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }

            $status = $_GET['status'] ?? 'semua';
            $where = "mr.warehouse_id = :wid";
            $params = [':wid' => $warehouse_id];
            
            if ($status !== 'semua') {
                // Konversi string "berhasil" menjadi query ke beberapa status positif agar aman
                if ($status === 'berhasil') {
                    $where .= " AND mr.status IN ('berhasil', 'approved', 'diproses', 'processed')";
                } else {
                    $where .= " AND mr.status = :status";
                    $params[':status'] = $status;
                }
            }

            // Hitung Total Data
            $countSql = "SELECT COUNT(mr.id) FROM material_requests mr WHERE $where";
            $stmtCount = $pdo->prepare($countSql);
            $stmtCount->execute($params);
            $total_data = $stmtCount->fetchColumn();
            $total_pages = ceil($total_data / $limit);

            $sql = "SELECT mr.*, ms.material_name, ms.unit FROM material_requests mr JOIN materials_stocks ms ON mr.material_id = ms.id WHERE $where ORDER BY mr.created_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'status' => 'success', 
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'current_page' => $page,
                'total_pages' => $total_pages
            ]);
            break;

        case 'submit_request':
            $warehouse_id = $_POST['warehouse_id']; 
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }

            $pilar_id = $_POST['pilar_id'];
            $req_qty = (float)$_POST['qty'];
            $req_unit = strtolower(trim($_POST['req_unit'] ?? 'default'));
            $user_id = $_SESSION['user_id'] ?? 1;
            
            $req_no = "REQ-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));

            if ($req_qty <= 0) throw new Exception("Jumlah permintaan harus lebih dari 0!");

            // Cek Gudang Pilar untuk mendapatkan Unit Dasar
            $stmtCek = $pdo->prepare("SELECT stock, unit FROM materials_stocks WHERE id = ? AND status = 'active'");
            $stmtCek->execute([$pilar_id]);
            $pilar = $stmtCek->fetch();

            if (!$pilar) throw new Exception("Bahan di Gudang Pilar tidak ditemukan atau diarsipkan!");
            
            // ==========================================
            // LOGIC KONVERSI SATUAN
            // ==========================================
            $base_unit = strtolower(trim($pilar['unit']));
            $final_qty = $req_qty; // Default jika "Sesuai Gudang"

            if ($req_unit !== 'default' && $req_unit !== $base_unit) {
                if ($base_unit === 'kg' && $req_unit === 'gram') {
                    $final_qty = $req_qty / 1000;
                } elseif ($base_unit === 'liter' && $req_unit === 'ml') {
                    $final_qty = $req_qty / 1000;
                } elseif ($base_unit === 'lusin' && $req_unit === 'pcs') {
                    $final_qty = $req_qty / 12;
                } else {
                    throw new Exception("Sistem belum mendukung konversi dari ($req_unit) ke Satuan Dasar Gudang ($base_unit). Silakan pilih 'Sesuai Gudang'.");
                }
            }

            // Validasi apakah jumlah (setelah dikonversi) melebihi stok di gudang utama
            if ($pilar['stock'] < $final_qty) {
                echo json_encode(['status' => 'error', 'message' => "Stok Gudang Pilar tidak mencukupi! Tersisa: " . $pilar['stock'] . " " . $pilar['unit']]); 
                exit;
            }

            // Simpan request menggunakan $final_qty (Sesuai satuan Gudang)
            $stmt = $pdo->prepare("INSERT INTO material_requests (request_no, warehouse_id, user_id, material_id, qty_requested, status) VALUES (?, ?, ?, ?, ?, 'menunggu')");
            $stmt->execute([$req_no, $warehouse_id, $user_id, $pilar_id, $final_qty]);

            echo json_encode(['status' => 'success', 'message' => "Pengajuan berhasil dikirim! (Sistem otomatis mengkonversi ke {$final_qty} {$pilar['unit']})"]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>