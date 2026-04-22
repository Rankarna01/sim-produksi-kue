<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

// Set headers to force download as Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Barang_Masuk_" . date('Ymd_His') . ".xls");

// Logika query sama persis dengan PDF
$source = $_GET['source'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($source !== 'semua') { $whereClause .= " AND bm.source = ?"; $params[] = $source; }
if ($filter_date === 'harian') { $whereClause .= " AND DATE(bm.created_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(bm.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR bm.transaction_no LIKE ? OR s.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT bm.*, ms.material_name, ms.unit, s.name as supplier_name, u.name as admin_name 
        FROM barang_masuk bm JOIN materials_stocks ms ON bm.material_id = ms.id JOIN users u ON bm.user_id = u.id LEFT JOIN suppliers s ON bm.supplier_id = s.id 
        $whereClause ORDER BY bm.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="7"><h3>LAPORAN BARANG MASUK GUDANG</h3></th>
        </tr>
        <tr>
            <th colspan="7">Waktu Tarik: <?= date('d M Y H:i:s') ?></th>
        </tr>
        <tr>
            <th style="background-color: #f4f4f4;">Waktu</th>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Jumlah Masuk</th>
            <th style="background-color: #f4f4f4;">Satuan</th>
            <th style="background-color: #f4f4f4;">Sumber Dokumen</th>
            <th style="background-color: #f4f4f4;">Supplier</th>
            <th style="background-color: #f4f4f4;">Catatan</th>
            <th style="background-color: #f4f4f4;">Admin Input</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= $row['material_name'] ?></td>
            <td><?= floatval($row['qty']) ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['source'] === 'PO' ? $row['transaction_no'] : 'Manual' ?></td>
            <td><?= $row['supplier_name'] ?: '-' ?></td>
            <td><?= $row['notes'] ?: '-' ?></td>
            <td><?= $row['admin_name'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>