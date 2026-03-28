<?php
// Mulai session untuk bisa mengakses data yang sedang aktif
session_start();

// Hapus semua variabel session yang ada (user_id, name, role)
session_unset();

// Hancurkan session secara keseluruhan dari server
session_destroy();

// Redirect (Arahkan) kembali ke halaman login utama
header("Location: /sim-produksi-kue/index.php");
exit();
?>