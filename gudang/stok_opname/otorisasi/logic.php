<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. AUTO-EXPIRE: Otomatis ubah status yang lewat waktu jadi expired
    $pdo->query("UPDATE stok_opname_keys SET status = 'expired' WHERE status = 'active' AND valid_until < NOW()");

    // 2. READ DATA
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->query("SELECT COUNT(id) FROM stok_opname_keys");
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "SELECT * FROM stok_opname_keys ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }

    // 3. GENERATE KEY BARU
    if ($action === 'generate') {
        $user_id = $_SESSION['user_id'] ?? 1;
        
        // Generate 6 digit angka random
        $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Waktu berlaku 24 jam dari sekarang
        $valid_until = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $pdo->prepare("INSERT INTO stok_opname_keys (access_code, valid_until, status, created_by) VALUES (?, ?, 'active', ?)");
        $stmt->execute([$pin, $valid_until, $user_id]);

        echo json_encode([
            'status' => 'success', 
            'pin' => $pin,
            'valid_until_formatted' => date('d/m/Y, H:i:s', strtotime($valid_until))
        ]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>