<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
// checkPermission('master_titipan'); // Aktifkan jika role selain owner bisa akses

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN DATA (READ)
    if ($action === 'read') {
        $search = $_GET['search'] ?? '';
        $params = [];
        $where = "";

        if (!empty($search)) {
            $where = "WHERE nama_barang LIKE ? OR nama_umkm LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        $stmt = $pdo->prepare("SELECT * FROM barang_titipan $where ORDER BY id DESC");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. SIMPAN DATA (CREATE & UPDATE)
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $nama_barang = trim($_POST['nama_barang'] ?? '');
        $nama_umkm = trim($_POST['nama_umkm'] ?? '');
        $harga_modal = $_POST['harga_modal'] ?? 0;
        $harga_jual = $_POST['harga_jual'] ?? 0;
        $stok = $_POST['stok'] ?? 0;

        if (empty($nama_barang) || empty($nama_umkm)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Barang dan UMKM wajib diisi!']); exit;
        }

        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE barang_titipan SET nama_barang=?, nama_umkm=?, harga_modal=?, harga_jual=?, stok=? WHERE id=?");
            $stmt->execute([$nama_barang, $nama_umkm, $harga_modal, $harga_jual, $stok, $id]);
            $msg = "Data berhasil diperbarui!";
        } else {
            // Insert Baru
            $stmt = $pdo->prepare("INSERT INTO barang_titipan (nama_barang, nama_umkm, harga_modal, harga_jual, stok) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama_barang, $nama_umkm, $harga_modal, $harga_jual, $stok]);
            $msg = "Barang titipan baru berhasil ditambahkan!";
        }

        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

    // 3. AMBIL DETAIL (UNTUK EDIT)
    if ($action === 'get_detail') {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM barang_titipan WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 4. HAPUS DATA (DELETE)
    if ($action === 'delete') {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM barang_titipan WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus!']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>