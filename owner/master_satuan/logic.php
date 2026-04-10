<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_satuan');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $pdo->query("SELECT * FROM units ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            // SUNTIKAN: Gembok Hak Edit
            checkPermission('edit_master_satuan');

            $id = $_POST['id'] ?? '';
            // Kapitalisasi huruf pertama agar rapi (cth: gram -> Gram)
            $name = ucfirst(strtolower(trim($_POST['name'])));

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Satuan wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                // Cek duplikasi
                $cek = $pdo->prepare("SELECT id FROM units WHERE LOWER(name) = ?");
                $cek->execute([strtolower($name)]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Satuan ini sudah ada!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO units (name) VALUES (?)");
                $stmt->execute([$name]);
                echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil ditambahkan!']);
            } else {
                // Cek duplikasi selain ID sendiri
                $cek = $pdo->prepare("SELECT id FROM units WHERE LOWER(name) = ? AND id != ?");
                $cek->execute([strtolower($name), $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Satuan ini sudah ada!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE units SET name=? WHERE id=?");
                $stmt->execute([$name, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil diperbarui!']);
            }
            break;

        case 'delete':
            // SUNTIKAN: Gembok Hak Hapus
            checkPermission('hapus_master_satuan');

            $id = $_POST['id'] ?? '';
            
            // Opsional: Bisa ditambah logika cek apakah satuan ini sedang dipakai di tabel materials/bom
            // Tapi untuk sekarang kita eksekusi hapus langsung
            $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Satuan berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>