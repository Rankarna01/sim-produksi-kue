<?php
// Izinkan akses dari domain luar (CORS) agar POS PWA bisa nembak API ini
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require_once '../../config/database.php';

// BASE_URL Untuk Gambar. 
// GANTI "http://localhost/sim_produksi_kue" dengan alamat domain/IP server aslimu nanti.
$BASE_URL_API = "http://localhost/sim_produksi_kue"; 

// Tangkap action dari GET atau POST
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    
    // =========================================================================
    // 1. ENDPOINT LOGIN POS
    // =========================================================================
    if ($action === 'login_pos') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if(empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Username dan Password wajib diisi.']);
            exit;
        }

        // Asumsi kamu menggunakan tabel `users` dan ada kolom `warehouse_id`
        $stmt = $pdo->prepare("SELECT id, name, username, password, role, warehouse_id FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Jangan pernah mengirim password kembali ke Frontend!
            unset($user['password']);

            // Validasi apakah user ini punya ID Toko (Warehouse ID)
            if(empty($user['warehouse_id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Akun ini tidak memiliki akses ke Toko/Cabang (Warehouse ID kosong).']);
                exit;
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Login berhasil!',
                'data' => $user
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah!']);
        }
        exit;
    }


    // =========================================================================
    // 2. ENDPOINT GET KATALOG (STOK AKTUAL PER TOKO)
    // =========================================================================
    if ($action === 'get_katalog') {
        $warehouse_id = $_GET['warehouse_id'] ?? '';

        if(empty($warehouse_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Warehouse ID (ID Toko) wajib dikirim!']);
            exit;
        }

        // QUERY PINTAR: Menghitung Stok Aktual per Toko
        // Rumus: Total Masuk (Produksi berstatus 'masuk_gudang') DIKURANGI Total Keluar (Penjualan Kasir)
        $sql = "
            SELECT 
                p.id, 
                p.code, 
                p.name, 
                p.category, 
                p.modal_price, 
                p.price as selling_price, 
                p.image,
                COALESCE(inflow.total_in, 0) AS total_masuk,
                COALESCE(outflow.total_out, 0) AS total_keluar,
                (COALESCE(inflow.total_in, 0) - COALESCE(outflow.total_out, 0)) AS current_stock
            FROM products p
            LEFT JOIN (
                -- MENGHITUNG BARANG MASUK DARI PRODUKSI (KUE MATANG) KE TOKO INI
                SELECT 
                    pd.product_id, 
                    SUM(pd.quantity) as total_in
                FROM production_details pd
                JOIN productions pr ON pd.production_id = pr.id
                WHERE pr.warehouse_id = ? AND pr.status = 'masuk_gudang'
                GROUP BY pd.product_id
            ) inflow ON p.id = inflow.product_id
            LEFT JOIN (
                -- MENGHITUNG BARANG KELUAR DARI PENJUALAN KASIR (POS) DI TOKO INI
                SELECT 
                    po.product_id, 
                    SUM(po.quantity) as total_out
                FROM product_outs po
                WHERE po.warehouse_id = ?
                GROUP BY po.product_id
            ) outflow ON p.id = outflow.product_id
            ORDER BY p.name ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        // Bind parameter warehouse_id dua kali (untuk inflow dan outflow)
        $stmt->execute([$warehouse_id, $warehouse_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format ulang link gambar agar berbentuk URL Penuh
        foreach ($products as $key => $prod) {
            if (!empty($prod['image'])) {
                $products[$key]['image_url'] = $BASE_URL_API . '/uploads/products/' . $prod['image'];
            } else {
                $products[$key]['image_url'] = $BASE_URL_API . '/assets/img/no-image.png'; 
            }
        }

        echo json_encode([
            'status' => 'success',
            'warehouse_id' => $warehouse_id,
            'data' => $products
        ]);
        exit;
    }

    // Jika parameter action tidak ditemukan
    echo json_encode(['status' => 'error', 'message' => 'Endpoint API tidak valid atau Action tidak ditemukan.']);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>