<?php
session_start();

// Deteksi Routing Dinamis
$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
$base_url = $is_localhost ? '/sim-produksi-kue/' : '/';

// ==========================================
// PERBAIKAN: Cek sesi aktif & Cegah Blank Page
// ==========================================
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    
    if ($role === 'owner') { header("Location: " . $base_url . "owner/dashboard/"); exit; }
    elseif ($role === 'produksi') { header("Location: " . $base_url . "produksi/input_produksi/"); exit; }
    elseif ($role === 'admin') { header("Location: " . $base_url . "admin/scan_barcode/"); exit; }
    elseif ($role === 'auditor') { header("Location: " . $base_url . "owner/dashboard/"); exit; }
    else {
        // Jika ada role aneh/nyangkut, hancurkan sesi agar tidak nyangkut di layar putih
        session_destroy(); 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Produksi Kue</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        surface: '#FFFFFF',
                        background: '#F1F5F9',
                        primary: '#2563EB',
                        secondary: '#94A3B8',
                        danger: '#EF4444'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-background h-screen flex items-center justify-center p-4 sm:p-6 overflow-hidden relative">

    <div class="absolute top-[-15%] left-[-5%] w-[500px] h-[500px] bg-primary/10 rounded-full blur-[80px] pointer-events-none"></div>
    <div class="absolute bottom-[-15%] right-[-5%] w-[400px] h-[400px] bg-blue-400/10 rounded-full blur-[80px] pointer-events-none"></div>

    <div class="w-full max-w-[1000px] bg-surface rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] flex flex-col lg:flex-row overflow-hidden min-h-[550px] relative z-10">
        
        <div class="hidden lg:flex lg:w-5/12 bg-gradient-to-b from-primary to-blue-800 text-white flex-col justify-center items-center p-12 relative z-10 lg:rounded-r-[5rem] shadow-[15px_0_30px_rgba(0,0,0,0.1)]">
            
            <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-white opacity-5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-64 h-64 bg-white opacity-10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="h-24 w-24 bg-white/10 backdrop-blur-md border border-white/20 rounded-full flex items-center justify-center mb-6 text-4xl shadow-xl">
                    <i class="fa-solid fa-cake-candles text-white"></i>
                </div>
                <h1 class="text-3xl font-extrabold mb-3 tracking-tight">Halo, Kawan!</h1>
                <p class="text-blue-100 text-sm leading-relaxed font-light mb-8 px-4">
                    Selamat datang di RotiKu ERP. Sistem Informasi Manajemen Terpadu untuk mengelola resep, stok bahan baku, dan produksi harian secara real-time.
                </p>
                
                <div class="flex flex-col gap-3 text-xs font-medium text-blue-200">
                    <span class="flex items-center justify-center gap-2"><i class="fa-solid fa-check-circle"></i> Terintegrasi & Cepat</span>
                    <span class="flex items-center justify-center gap-2"><i class="fa-solid fa-check-circle"></i> Data Akurat & Aman</span>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-7/12 flex flex-col justify-center p-8 sm:p-14 lg:pl-20 relative z-0 bg-surface">
            
            <div class="text-center mb-10">
                <div class="lg:hidden h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 text-primary text-3xl">
                    <i class="fa-solid fa-cake-candles"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Sign In</h2>
                <p class="text-secondary text-sm mt-2 font-medium">Gunakan kredensial akun untuk masuk.</p>
            </div>

            <div id="alert-error" class="hidden mb-6 p-4 bg-danger/10 border-l-4 border-danger text-danger text-sm rounded-r-lg font-bold">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> <span id="error-message">Error</span>
            </div>

            <form id="loginForm" class="space-y-5">
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-regular fa-user text-slate-400"></i>
                        </div>
                        <input type="text" id="username" name="username" class="w-full pl-12 pr-5 py-4 bg-slate-100/80 border-2 border-transparent rounded-2xl focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all text-slate-700 font-semibold placeholder:text-slate-400 placeholder:font-normal" placeholder="Username" required>
                    </div>
                </div>

                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="w-full pl-12 pr-5 py-4 bg-slate-100/80 border-2 border-transparent rounded-2xl focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all text-slate-700 font-semibold placeholder:text-slate-400 placeholder:font-normal" placeholder="Password" required>
                    </div>
                </div>

                <div class="flex items-center justify-end text-sm">
                    <span class="text-slate-400 font-medium hover:text-primary cursor-not-allowed transition-colors">Lupa Password?</span>
                </div>

                <button type="submit" id="btn-login" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-2xl transition-all shadow-[0_10px_20px_rgba(37,99,235,0.3)] hover:shadow-[0_10px_25px_rgba(37,99,235,0.4)] hover:-translate-y-0.5 flex items-center justify-center gap-3 mt-4">
                    <span>SIGN IN</span>
                </button>
            </form>
            
            <div class="mt-12 text-center text-xs text-slate-400 font-medium">
                &copy; <?= date('Y') ?> Sistem ERP RotiKu.<br>All rights reserved.
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
                    btnLogin.classList.replace('hover:bg-blue-700', 'hover:bg-emerald-600');
                    btnLogin.classList.replace('shadow-[0_10px_20px_rgba(37,99,235,0.3)]', 'shadow-[0_10px_20px_rgba(16,185,129,0.3)]');
                    btnLogin.innerHTML = '<i class="fa-solid fa-check text-xl"></i> Berhasil!';
                    setTimeout(() => { window.location.href = result.redirect; }, 800);
                } else {
                    errorMessage.textContent = result.message;
                    alertError.classList.remove('hidden');
                    btnLogin.innerHTML = '<span>SIGN IN</span>';
                    btnLogin.disabled = false;
                }
            } catch (error) {
                errorMessage.textContent = 'Terjadi kesalahan koneksi server.';
                alertError.classList.remove('hidden');
                btnLogin.innerHTML = '<span>SIGN IN</span>';
                btnLogin.disabled = false;
            }
        });
    </script>
</body>
</html>