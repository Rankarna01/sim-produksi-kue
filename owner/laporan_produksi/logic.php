<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

$action = $_GET['action'] ?? '';

try {
    // Kumpulkan Parameter Filter
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';

    // Siapkan Query Dinamis
    $sql = "
        SELECT p.created_at, p.invoice_no, u.name as karyawan, 
               pr.name as produk, d.quantity, p.status, w.name as gudang 
        FROM productions p
        JOIN production_details d ON p.id = d.production_id
        JOIN products pr ON d.product_id = pr.id
        JOIN users u ON p.user_id = u.id
        JOIN warehouses w ON p.warehouse_id = w.id
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($start_date)) {
        $sql .= " AND DATE(p.created_at) >= ?";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $sql .= " AND DATE(p.created_at) <= ?";
        $params[] = $end_date;
    }
    if (!empty($status)) {
        $sql .= " AND p.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // ============================================
    // ROUTE 1: EXCEL EXPORT (DOWNLOAD FILE)
    // ============================================
    if ($action === 'export_excel') {
        $filename = "Laporan_Produksi_" . date('Ymd_His') . ".csv";
        
        // Setup Header untuk memaksa browser mendownload file CSV/Excel
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Buka output stream
        $output = fopen('php://output', 'w');
        
        // Tambahkan BOM agar Excel mengenali UTF-8 dengan benar
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // Tulis Baris Judul Kolom (Header)
        fputcsv($output, ['No', 'Tanggal Produksi', 'Invoice', 'Karyawan', 'Nama Produk', 'Jumlah (Pcs)', 'Status', 'Gudang Tujuan']);
        
        // Tulis Data Baris per Baris
        $no = 1;
        foreach ($data as $row) {
            $status_indo = ($row['status'] === 'pending') ? 'Pending' : 'Masuk Gudang';
            fputcsv($output, [
                $no++,
                $row['created_at'],
                $row['invoice_no'],
                $row['karyawan'],
                $row['produk'],
                $row['quantity'],
                $status_indo,
                $row['gudang']
            ]);
        }
        
        fclose($output);
        exit; // Hentikan script agar tidak mengirim HTML apapun setelah CSV
    }

    // ============================================
    // ROUTE 2: BACA DATA UNTUK TABEL AJAX (JSON)
    // ============================================
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

} catch (PDOException $e) {
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>