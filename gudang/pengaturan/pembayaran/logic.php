<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('pengaturan_pembayaran');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN SEMUA DATA
    if ($action === 'read') {
        $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY created_at ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. TAMBAH DATA BARU
    if ($action === 'save') {
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama metode tidak boleh kosong!']); exit;
        }

        // Cek duplikat
        $cek = $pdo->prepare("SELECT id FROM payment_methods WHERE LOWER(name) = LOWER(?)");
        $cek->execute([$name]);
        if ($cek->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran ini sudah ada!']); exit;
        }

        $stmt = $pdo->prepare("INSERT INTO payment_methods (name) VALUES (?)");
        $stmt->execute([$name]);

        echo json_encode(['status' => 'success', 'message' => 'Metode pembayaran berhasil ditambahkan!']);
        exit;
    }

    // 3. HAPUS DATA
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        // Pastikan bukan metode krusial jika kamu punya aturan khusus
        // (Bisa dilewati jika bebas dihapus)

        $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Metode pembayaran berhasil dihapus!']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>