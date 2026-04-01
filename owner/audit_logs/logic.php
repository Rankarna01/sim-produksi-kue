<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'track_invoice') {
        $inv = trim($_GET['inv'] ?? '');
        
        if (empty($inv)) {
            echo json_encode(['status' => 'error', 'message' => 'Nomor Invoice kosong!']);
            exit;
        }

        // 1. CARI DATA PRODUKSI UTAMA (DENGAN NAMA EMPLOYEES BARU)
        $stmt = $pdo->prepare("
            SELECT p.id, p.created_at, p.status, p.notes, 
                   COALESCE(e.name, u.name) as karyawan, w.name as gudang
            FROM productions p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            JOIN warehouses w ON p.warehouse_id = w.id
            WHERE p.invoice_no = ?
            LIMIT 1
        ");
        $stmt->execute([$inv]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan! Pastikan Nomor Invoice benar.']);
            exit;
        }

        // 2. AMBIL DETAIL PRODUK YANG DIPRODUKSI
        $detStmt = $pdo->prepare("
            SELECT pr.name as produk, d.quantity
            FROM production_details d
            JOIN products pr ON d.product_id = pr.id
            WHERE d.production_id = ?
        ");
        $detStmt->execute([$prod['id']]);
        $details = $detStmt->fetchAll(PDO::FETCH_ASSOC);

        // Rangkai string detail kue
        $kue_list = [];
        foreach ($details as $d) {
            $kue_list[] = "<strong>{$d['quantity']} Pcs</strong> {$d['produk']}";
        }
        $kue_str = implode(", ", $kue_list);

        // 3. SIAPKAN ARRAY TIMELINE EVENTS
        $events = [];

        // EVENT PERTAMA: Produksi Awal
        $events[] = [
            'time' => $prod['created_at'],
            'type' => 'start',
            'title' => 'Produksi Dicatat (Dapur)',
            'description' => "Karyawan <strong>{$prod['karyawan']}</strong> mencatat produksi: $kue_str.<br>Tujuan simpan: <strong>Gudang {$prod['gudang']}</strong>.<br><em>Catatan: " . ($prod['notes'] ?: '-') . "</em>"
        ];

        // 4. CARI RIWAYAT BARANG KELUAR (EXPIRED/RUSAK) DARI INVOICE INI
        $outStmt = $pdo->prepare("
            SELECT o.created_at, o.quantity, o.reason, o.notes, u.name as karyawan, pr.name as produk
            FROM product_outs o
            JOIN users u ON o.user_id = u.id
            JOIN products pr ON o.product_id = pr.id
            WHERE o.origin_invoice = ?
            ORDER BY o.created_at ASC
        ");
        $outStmt->execute([$inv]);
        $outs = $outStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($outs as $out) {
            $events[] = [
                'time' => $out['created_at'],
                'type' => strtolower($out['reason']), // 'expired', 'rusak', dll
                'title' => "Produk Keluar ({$out['reason']})",
                'description' => "Ditarik <strong>{$out['quantity']} Pcs</strong> {$out['produk']} oleh <strong>{$out['karyawan']}</strong>.<br><em>Catatan: " . ($out['notes'] ?: '-') . "</em>"
            ];
        }

        // Jika statusnya saat ini 'ditolak' (Belum pernah masuk gudang sama sekali)
        if ($prod['status'] === 'ditolak') {
            $events[] = [
                'time' => $prod['created_at'], // Waktu aslinya
                'type' => 'rejected',
                'title' => 'Produksi Ditolak Admin',
                'description' => "Produksi ini ditolak oleh Admin Gudang dan harus direvisi jumlahnya oleh Karyawan Dapur."
            ];
        }

        // Urutkan timeline berdasarkan waktu kejadian (Paling awal ke paling akhir)
        usort($events, function($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        // Lempar ke Frontend
        echo json_encode([
            'status' => 'success',
            'invoice_no' => $inv,
            'current_status' => $prod['status'],
            'events' => $events
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>