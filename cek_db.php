<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Cek Koneksi Database</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 40px; color: #1e293b; }
        .card { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; padding: 28px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .badge-success { background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 99px; font-weight: 700; display: inline-block; }
        .badge-fail { background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 99px; font-weight: 700; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        td, th { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; text-align: left; }
        th { font-weight: 700; color: #64748b; background: #f8fafc; }
    </style>
</head>
<body>
    <div class='card'>";

if (isset($pdo) && $pdo instanceof PDO) {
    echo "<span class='badge-success'>✅ KONEKSI DATABASE BERHASIL</span>";
    echo "<h2 style='margin-top: 16px;'>Detail Database</h2>";
    echo "<table>
            <tr><th>Host Server</th><td>" . htmlspecialchars($host) . "</td></tr>
            <tr><th>Nama Database</th><td>" . htmlspecialchars($dbname) . "</td></tr>
            <tr><th>User Database</th><td>" . htmlspecialchars($user) . "</td></tr>
          </table>";

    // Cek isi tabel users
    try {
        $stmt = $pdo->query("SELECT id, name, username, role, status FROM users LIMIT 10");
        $users = $stmt->fetchAll();
        echo "<h3 style='margin-top: 24px;'>Daftar User di Database (" . count($users) . " User):</h3>";
        echo "<table>
                <thead>
                    <tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th></tr>
                </thead>
                <tbody>";
        foreach ($users as $i => $u) {
            echo "<tr>
                    <td>" . ($i + 1) . "</td>
                    <td><strong>" . htmlspecialchars($u['name']) . "</strong></td>
                    <td><code>" . htmlspecialchars($u['username']) . "</code></td>
                    <td>" . htmlspecialchars($u['role']) . "</td>
                  </tr>";
        }
        echo "  </tbody>
              </table>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>Tabel users belum ditemukan atau query gagal: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<span class='badge-fail'>❌ KONEKSI GAGAL</span>";
    echo "<h2 style='margin-top: 16px;'>Database Tidak Terhubung</h2>";
}

echo "  <div style='margin-top: 28px; text-align: center;'>
            <a href='index.php' style='color: #2563eb; font-weight: bold; text-decoration: none;'>← Kembali ke Halaman Login</a>
        </div>
    </div>
</body>
</html>";
?>
