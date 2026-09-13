<?php
// owner/reset_data/logic.php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// HANYA ROLE OWNER YANG DIIZINKAN MENGAKSES FITUR INI
if (($_SESSION['role'] ?? '') !== 'owner') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak! Fitur reset database hanya dapat dijalankan oleh akun Owner utama.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// =========================================================================
// 1. STATISTIK DATA SAAT INI (LIVE COUNTER)
// =========================================================================
if ($action === 'get_stats') {
    header('Content-Type: application/json');
    try {
        // Hitung data transaksi produksi
        $prodCount = (int)$pdo->query("SELECT COUNT(*) FROM productions")->fetchColumn();
        $planCount = (int)$pdo->query("SELECT COUNT(*) FROM production_plans")->fetchColumn();
        $titipanCount = (int)$pdo->query("SELECT COUNT(*) FROM titipan_productions")->fetchColumn();
        $totalProduksi = $prodCount + $planCount + $titipanCount;

        // Hitung data pengadaan (PO & PR)
        $poCount = (int)$pdo->query("SELECT COUNT(*) FROM purchase_orders")->fetchColumn();
        $prCount = (int)$pdo->query("SELECT COUNT(*) FROM purchase_requests")->fetchColumn();
        $totalPO = $poCount + $prCount;

        // Hitung data logistik masuk & keluar
        $bmCount = (int)$pdo->query("SELECT COUNT(*) FROM barang_masuk")->fetchColumn();
        $bkCount = (int)$pdo->query("SELECT COUNT(*) FROM barang_keluar")->fetchColumn();
        $poOutCount = (int)$pdo->query("SELECT COUNT(*) FROM product_outs")->fetchColumn();
        $totalLogistik = $bmCount + $bkCount + $poOutCount;

        // Hitung data penjualan POS
        $salesCount = 0;
        try {
            $salesCount = (int)$pdo->query("SELECT COUNT(*) FROM sales_pos")->fetchColumn();
        } catch (Exception $e) {}

        // Hitung data stok opname
        $opnameCount = 0;
        try {
            $opnameCount += (int)$pdo->query("SELECT COUNT(*) FROM stok_opname")->fetchColumn();
            $opnameCount += (int)$pdo->query("SELECT COUNT(*) FROM gudang_stok_opnames")->fetchColumn();
        } catch (Exception $e) {}

        // Hitung data Master
        $productsCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $materialsCount = (int)$pdo->query("SELECT COUNT(*) FROM materials_stocks")->fetchColumn();
        $recipesCount = (int)$pdo->query("SELECT COUNT(*) FROM bom")->fetchColumn();

        // Hitung Akun Login yang aman
        $usersCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $rolesCount = (int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'transaksi_produksi' => $totalProduksi,
                'transaksi_po_pr' => $totalPO,
                'transaksi_logistik' => $totalLogistik,
                'transaksi_pos' => $salesCount,
                'riwayat_opname' => $opnameCount,
                'master_produk' => $productsCount,
                'master_bahan' => $materialsCount,
                'master_resep' => $recipesCount,
                'user_login_aman' => $usersCount,
                'role_aman' => $rolesCount
            ]
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// =========================================================================
// 2. BACKUP DATABASE KE FILE .SQL (DOWNLOAD LANGSUNG DARI BROWSER)
// =========================================================================
if ($action === 'backup_sql') {
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $filename = "backup_sim_kue_" . date('Y-m-d_His') . ".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");

        echo "-- =========================================================\n";
        echo "-- BACKUP DATABASE OTOMATIS SEBELUM RESET SISTEM GO-LIVE\n";
        echo "-- Waktu Eksekusi : " . date('Y-m-d H:i:s') . "\n";
        echo "-- Di-generate oleh User : " . ($_SESSION['name'] ?? 'Owner') . "\n";
        echo "-- =========================================================\n\n";
        echo "SET FOREIGN_KEY_CHECKS = 0;\n";
        echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        echo "SET NAMES utf8mb4;\n\n";

        foreach ($tables as $table) {
            // Dapatkan skema tabel
            $createTableStmt = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $createTableSql = $createTableStmt['Create Table'] ?? '';

            echo "-- --------------------------------------------------------\n";
            echo "-- Struktur Tabel `$table`\n";
            echo "-- --------------------------------------------------------\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $createTableSql . ";\n\n";

            // Dapatkan seluruh baris data
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) > 0) {
                echo "-- Data untuk Tabel `$table`\n";
                $columns = array_keys($rows[0]);
                $colList = '`' . implode('`, `', $columns) . '`';

                foreach (array_chunk($rows, 100) as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ($row as $val) {
                            if ($val === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($val);
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $values) . ')';
                    }
                    echo "INSERT INTO `$table` ($colList) VALUES\n" . implode(",\n", $insertValues) . ";\n";
                }
                echo "\n";
            }
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        exit;
    } catch (Exception $e) {
        die("Gagal membuat backup database: " . $e->getMessage());
    }
}

// =========================================================================
// 3. EKSEKUSI RESET DATA GO-LIVE
// =========================================================================
if ($action === 'execute_reset') {
    header('Content-Type: application/json');

    $password = $_POST['password'] ?? '';
    $confirmText = trim($_POST['confirm_text'] ?? '');
    $mode = $_POST['mode'] ?? 'standar'; // 'standar' (hanya transaksi & stok nol) atau 'total' (bersih master juga)

    // 1. Validasi konfirmasi teks
    if ($confirmText !== 'RESET DATA LIVE') {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Frasa konfirmasi tidak sesuai! Ketik teks "RESET DATA LIVE" dengan benar untuk melanjutkan.'
        ]);
        exit;
    }

    // 2. Validasi password akun Owner saat ini
    $userId = $_SESSION['user_id'] ?? 0;
    $userStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $currentHash = $userStmt->fetchColumn();

    if (!$currentHash || (!password_verify($password, $currentHash) && $password !== $currentHash)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Password Owner salah! Verifikasi keamanan gagal.'
        ]);
        exit;
    }

    try {
        // Matikan pengecekan Foreign Key untuk pembersihan yang aman & lancar
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // DAFTAR TABEL TRANSAKSI YANG SELALU DIKOSONGKAN (TRUNCATE)
        $transactionTables = [
            // 1. Produksi & Rencana
            'productions',
            'production_details',
            'production_plans',
            'production_plan_details',
            'titipan_productions',
            'titipan_production_details',
            'bom_requests',
            'bom_request_details',
            'bom_custom',

            // 2. Mutasi & Logistik
            'product_outs',
            'product_mutations',
            'barang_titipan_keluar',
            'barang_masuk',
            'barang_keluar',

            // 3. Purchasing (PO & PR)
            'purchase_requests',
            'purchase_orders',
            'purchase_order_details',
            'purchase_order_payments',
            'purchase_payments',
            'po_returns',
            'material_requests',
            'material_requests_header',

            // 4. Stok Opname & Audit History
            'stok_opname',
            'stok_opname_details',
            'stok_opname_keys',
            'material_opnames',
            'gudang_stok_opnames',
            'gudang_stok_opname_details',
            'opname_history_pos',
            'inventory_history_pos',

            // 5. Penjualan & Kasir POS
            'sales_pos',
            'sale_details_pos',
            'sale_payments_pos',
            'sale_cancellations_pos',
            'sale_cancellation_items_pos',
            'shifts_history_pos',
            'petty_cash_pos',
            'saved_custom_items_pos',
            'saved_custom_reguler_pos',

            // 6. Log Aktivitas
            'system_logs'
        ];

        // Eksekusi truncate pada tabel transaksi
        $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($transactionTables as $t) {
            if (in_array($t, $existingTables)) {
                $pdo->exec("TRUNCATE TABLE `$t`");
            }
        }

        // ==========================================================
        // PENANGANAN STOK: NOL-KAN SEMUA STOK (JADI 0)
        // ==========================================================
        $stockTables = [
            'materials_stocks',
            'materials',
            'products',
            'product_warehouse_stocks',
            'barang_titipan',
            'store_titipan_stocks'
        ];

        foreach ($stockTables as $tbl) {
            if (in_array($tbl, $existingTables)) {
                $cols = $pdo->query("SHOW COLUMNS FROM `$tbl`")->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('stock', $cols)) {
                    $pdo->exec("UPDATE `$tbl` SET `stock` = 0");
                }
                if (in_array('stok', $cols)) {
                    $pdo->exec("UPDATE `$tbl` SET `stok` = 0");
                }
            }
        }

        // ==========================================================
        // JIKA MODE 'TOTAL' (HAPUS MASTER JUGA KECUALI LOGIN & ROLE)
        // ==========================================================
        if ($mode === 'total') {
            $masterTablesToDelete = [
                'bom',
                'products',
                'materials_stocks',
                'materials',
                'product_warehouse_stocks',
                'barang_titipan',
                'store_titipan_stocks',
                'categories',
                'material_categories',
                'suppliers',
                'customers_pos',
                'promo_auto_discounts',
                'promo_buy_x_get_y',
                'vouchers_pos'
            ];

            foreach ($masterTablesToDelete as $mt) {
                if (in_array($mt, $existingTables)) {
                    $pdo->exec("TRUNCATE TABLE `$mt`");
                }
            }
        }

        // Hidupkan kembali foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // Catat Log Baru Khusus Reset Go-Live
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $modeDesc = ($mode === 'total') ? 'RESET TOTAL (Termasuk Master)' : 'RESET STANDAR GO-LIVE (Transaksi Bersih, Stok 0, Master Dipertahankan)';
            $logStmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, menu, description, ip_address) VALUES (?, 'reset_database', 'Reset Data', ?, ?)");
            $logStmt->execute([$userId, "Inisialisasi Go-Live: " . $modeDesc, $ip]);
        } catch (Exception $e) {}

        echo json_encode([
            'status' => 'success',
            'message' => 'Database berhasil dibersihkan! Seluruh transaksi telah di-reset menjadi 0 dan siap digunakan untuk Live Production.',
            'mode' => $mode
        ]);
        exit;

    } catch (Exception $e) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo json_encode([
            'status' => 'error', 
            'message' => 'Gagal mereset database: ' . $e->getMessage()
        ]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid!']);
exit;
