<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('pengaturan_profil');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN DATA PROFIL
    if ($action === 'read') {
        $stmt = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            $pdo->query("INSERT INTO store_profile (id, store_name, req_approval_in, req_approval_out, req_approval_po, req_approval_pr, req_approval_print) VALUES (1, 'GUDANG PILAR', 1, 1, 1, 1, 1)");
            $stmt = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. SIMPAN DATA PROFIL & LOGO
    if ($action === 'save') {
        $store_name = trim($_POST['store_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        // Cek Saklar EKSPILSIT dari AJAX JS
        $req_in = (isset($_POST['req_approval_in']) && $_POST['req_approval_in'] == '1') ? 1 : 0;
        $req_out = (isset($_POST['req_approval_out']) && $_POST['req_approval_out'] == '1') ? 1 : 0;
        $req_po = (isset($_POST['req_approval_po']) && $_POST['req_approval_po'] == '1') ? 1 : 0;
        $req_pr = (isset($_POST['req_approval_pr']) && $_POST['req_approval_pr'] == '1') ? 1 : 0;
        $req_print = (isset($_POST['req_approval_print']) && $_POST['req_approval_print'] == '1') ? 1 : 0;

        if (empty($store_name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Toko / Perusahaan wajib diisi!']); exit;
        }

        // Proses Upload Logo (Jika ada)
        $logo_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'Format file harus JPG atau PNG!']); exit;
            }
            if ($file['size'] > $maxSize) {
                echo json_encode(['status' => 'error', 'message' => 'Ukuran logo maksimal 2MB!']); exit;
            }

            $uploadDir = '../../../uploads/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'logo_toko_' . time() . '.' . $ext;
            $destination = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $logo_path = 'uploads/' . $fileName;
                
                $oldLogoStmt = $pdo->query("SELECT logo_path FROM store_profile WHERE id = 1");
                $oldLogo = $oldLogoStmt->fetchColumn();
                if ($oldLogo && file_exists('../../../' . $oldLogo)) {
                    unlink('../../../' . $oldLogo);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengupload logo. Cek permission folder uploads/']); exit;
            }
        }

        $pdo->beginTransaction();

        if ($logo_path) {
            $stmt = $pdo->prepare("UPDATE store_profile SET store_name=?, phone=?, email=?, address=?, req_approval_in=?, req_approval_out=?, req_approval_po=?, req_approval_pr=?, req_approval_print=?, logo_path=? WHERE id=1");
            $stmt->execute([$store_name, $phone, $email, $address, $req_in, $req_out, $req_po, $req_pr, $req_print, $logo_path]);
        } else {
            $stmt = $pdo->prepare("UPDATE store_profile SET store_name=?, phone=?, email=?, address=?, req_approval_in=?, req_approval_out=?, req_approval_po=?, req_approval_pr=?, req_approval_print=? WHERE id=1");
            $stmt->execute([$store_name, $phone, $email, $address, $req_in, $req_out, $req_po, $req_pr, $req_print]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Profil dan Pengaturan berhasil disimpan!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>