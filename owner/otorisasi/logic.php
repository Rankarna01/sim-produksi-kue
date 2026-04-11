<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Gembok Keamanan API
checkPermission('otorisasi');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $sql = "SELECT auth_code, created_at, valid_until, is_used 
                FROM access_codes 
                ORDER BY created_at DESC LIMIT 20";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($action === 'generate') {
        // Generate 6 digit acak
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user_id = $_SESSION['user_id'];
        
        // Masa aktif 24 jam
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $display_expiry = date('d/m/Y, H:i.s', strtotime($expiry));

        $stmt = $pdo->prepare("INSERT INTO access_codes (auth_code, created_by, valid_until) VALUES (?, ?, ?)");
        $stmt->execute([$code, $user_id, $expiry]);

        echo json_encode([
            'status' => 'success', 
            'code' => $code, 
            'valid_until' => $display_expiry
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}