<?php
session_start();

// Deteksi Routing Dinamis
$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
$base_url = $is_localhost ? '/sim-produksi-kue/' : '/';

// Cek sesi aktif
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'owner') header("Location: " . $base_url . "owner/dashboard/");
    if ($role === 'produksi') header("Location: " . $base_url . "produksi/input_produksi/");
    if ($role === 'admin') header("Location: " . $base_url . "admin/scan_barcode/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Produksi Kue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        surface: '#FFFFFF',
                        background: '#F8FAFC',
                        primary: '#2563EB',
                        secondary: '#94A3B8',
                        danger: '#EF4444'
                    }
                }
            }
        }
    </script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-surface h-screen flex overflow-hidden">

    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary to-blue-800 text-white flex-col justify-center items-center p-12 relative overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white opacity-5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col items-center text-center">
            <div class="h-28 w-28 bg-white/10 backdrop-blur-md border border-white/20 rounded-full flex items-center justify-center mb-8 text-5xl shadow-2xl">
                <i class="fa-solid fa-cake-candles text-white"></i>
            </div>
            <h1 class="text-4xl font-extrabold mb-4 tracking-tight">RotiKu ERP System</h1>
            <p class="text-blue-100 text-lg max-w-md leading-relaxed font-light mb-8">
                Sistem Informasi Manajemen Terpadu. Kelola resep, pantau pergerakan stok bahan baku, dan validasi produksi harian secara real-time dan akurat.
            </p>
            
            <div class="flex gap-4 text-sm font-medium text-blue-200 bg-black/10 px-6 py-3 rounded-full border border-white/10">
                <span class="flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Cepat</span>
                <span class="flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Akurat</span>
                <span class="flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Aman</span>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-background lg:bg-surface">
        <div class="w-full max-w-md bg-surface lg:bg-transparent p-8 lg:p-0 rounded-3xl shadow-xl lg:shadow-none border border-slate-100 lg:border-none">
            
            <div class="text-center lg:text-left mb-10">
                <div class="lg:hidden h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 text-primary text-3xl">
                    <i class="fa-solid fa-cake-candles"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Selamat Datang</h2>
                <p class="text-slate-500 text-sm mt-2">Masuk menggunakan kredensial akun Anda.</p>
            </div>

            <div id="alert-error" class="hidden mb-6 p-4 bg-danger/10 border-l-4 border-danger text-danger text-sm rounded-r-lg font-medium">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> <span id="error-message">Error</span>
            </div>

            <form id="loginForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-slate-400"></i>
                        </div>
                        <input type="text" id="username" name="username" class="w-full pl-11 pr-4 py-3.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-white text-slate-700 font-medium" placeholder="contoh: owner" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="w-full pl-11 pr-4 py-3.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-white text-slate-700 font-medium" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" id="btn-login" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-3 mt-8">
                    <span>Masuk Sekarang</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="mt-8 text-center lg:text-left text-xs text-slate-400 font-medium">
                &copy; <?= date('Y') ?> Sistem ERP RotiKu. All rights reserved.
            </div>
            
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btnLogin = document.getElementById('btn-login');
            const alertError = document.getElementById('alert-error');
            const errorMessage = document.getElementById('error-message');

            alertError.classList.add('hidden');
            btnLogin.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
            btnLogin.disabled = true;

            try {
                const response = await fetch('login_logic.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.status === 'success') {
                    btnLogin.classList.replace('bg-primary', 'bg-emerald-500');
                    btnLogin.innerHTML = '<i class="fa-solid fa-check text-xl"></i> Berhasil!';
                    setTimeout(() => { window.location.href = result.redirect; }, 800);
                } else {
                    errorMessage.textContent = result.message;
                    alertError.classList.remove('hidden');
                    btnLogin.innerHTML = '<span>Masuk Sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
                    btnLogin.disabled = false;
                }
            } catch (error) {
                errorMessage.textContent = 'Terjadi kesalahan koneksi server.';
                alertError.classList.remove('hidden');
                btnLogin.innerHTML = '<span>Masuk Sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
                btnLogin.disabled = false;
            }
        });
    </script>
</body>
</html>