<?php
// config/database.php

$host = 'localhost';
$dbname = 'sim_produksi_kue';
$user = 'root'; // Sesuaikan dengan user MySQL kamu (biasanya 'root' di XAMPP/Laragon)
$pass = '';     // Sesuaikan dengan password MySQL kamu (biasanya kosong)

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Set error mode ke Exception agar mudah di-debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Hentikan eksekusi dan tampilkan error jika koneksi gagal
    die("Koneksi database gagal: " . $e->getMessage());
}
?>