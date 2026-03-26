<?php
session_start();
// Jika sudah login, tendang langsung ke dashboard sesuai role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'owner') header("Location: /sim-produksi-kue/owner/dashboard/");
    if ($role === 'produksi') header("Location: /sim-produksi-kue/produksi/input_produksi/");
    if ($role === 'admin') header("Location: /sim-produksi-kue/admin/scan_barcode/");
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
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-background h-screen flex items-center justify-center p-4">

    <div class="bg-surface w-full max-w-md rounded-2xl shadow-lg border border-slate-100 p-8">
        <div class="text-center mb-8">
            <div class="h-16 w-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 text-primary text-3xl">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang</h1>
            <p class="text-slate-500 text-sm mt-1">Silakan masukkan username Anda</p>
        </div>

        <div id="alert-error" class="hidden mb-4 p-4 bg-danger/10 border-l-4 border-danger text-danger text-sm rounded-r-lg font-medium">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> <span id="error-message">Error message here</span>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-regular fa-user text-slate-400"></i>
                    </div>
                    <input type="text" id="username" name="username" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-white" placeholder="contoh: owner" required>
                </div>
            </div>

            <button type="submit" id="btn-login" class="w-full bg-primary hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const usernameInput = document.getElementById('username').value;
            const btnLogin = document.getElementById('btn-login');
            const alertError = document.getElementById('alert-error');
            const errorMessage = document.getElementById('error-message');

            // Reset UI
            alertError.classList.add('hidden');
            btnLogin.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
            btnLogin.disabled = true;
            btnLogin.classList.add('opacity-70', 'cursor-not-allowed');

            // Siapkan data untuk dikirim
            const formData = new FormData();
            formData.append('username', usernameInput);

            try {
                // Fetch AJAX ke PHP Logic
                const response = await fetch('login_logic.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();

                if (result.status === 'success') {
                    // Jika sukses, ubah tombol jadi hijau dan redirect
                    btnLogin.classList.remove('bg-primary', 'hover:bg-blue-700');
                    btnLogin.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
                    btnLogin.innerHTML = '<i class="fa-solid fa-check"></i> Berhasil! Mengalihkan...';
                    
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 800);
                } else {
                    // Tampilkan pesan error
                    errorMessage.textContent = result.message;
                    alertError.classList.remove('hidden');
                    
                    // Kembalikan tombol ke keadaan semula
                    btnLogin.innerHTML = '<span>Masuk Sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
                    btnLogin.disabled = false;
                    btnLogin.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = 'Terjadi kesalahan koneksi server.';
                alertError.classList.remove('hidden');
                
                btnLogin.innerHTML = '<span>Masuk Sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
                btnLogin.disabled = false;
                btnLogin.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>