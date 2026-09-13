<?php
require_once '../../config/auth.php';

// Fitur ini hanya boleh dibuka oleh Owner utama
if (($_SESSION['role'] ?? '') !== 'owner') {
    http_response_code(403);
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>
            <h1 style='color:#e11d48;'>403 - Akses Ditolak</h1>
            <p>Hanya akun Owner utama yang dapat mengakses menu Reset Database Go-Live.</p>
            <a href='" . BASE_URL . "owner/dashboard/'>Kembali ke Dashboard</a>
         </div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <!-- Header Halaman -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-700">
                            Pemeliharaan Sistem & Go-Live
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fa-solid fa-broom-ball text-rose-600"></i> Inisialisasi Database (Reset Go-Live)
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Kosongkan seluruh data transaksi uji coba dan nol-kan nilai stok agar sistem siap digunakan resmi untuk produksi.</p>
                </div>
                <div>
                    <a href="logic.php?action=backup_sql" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-200 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-sm"></i> Download Backup (.SQL)
                    </a>
                </div>
            </div>

            <!-- Banner Peringatan Keamanan -->
            <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-xl shadow-rose-200/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center shrink-0 text-2xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-lg tracking-tight">Perhatian: Tindakan Ini Bersifat Permanen!</h3>
                        <p class="text-xs text-rose-100 mt-1 max-w-2xl leading-relaxed">
                            Fitur ini dirancang khusus untuk tahap <strong>Go-Live (Peluncuran Resmi)</strong>. Seluruh riwayat transaksi uji coba (produksi, pembelian PO/PR, mutasi, logistik, penjualan, dan stok) akan dihapus atau di-reset menjadi <strong>0</strong>. Seluruh akun login dan hak akses pengguna dijamin tetap aman.
                        </p>
                    </div>
                </div>
                <div class="shrink-0 w-full md:w-auto text-center md:text-right">
                    <span class="inline-block px-4 py-2 rounded-xl bg-white/20 backdrop-blur-sm text-xs font-black uppercase tracking-widest text-white border border-white/20">
                        <i class="fa-solid fa-lock mr-1.5"></i> Akun Login Terlindungi
                    </span>
                </div>
            </div>

            <!-- Grid Konten -->
            <div class="space-y-8">
                
                <!-- Section 1: Data Counter (Ringkasan Data Saat Ini) -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-blue-600"></i> Ringkasan Data Saat Ini di Sistem
                        </h3>
                        <button onclick="loadStats()" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-rotate"></i> Perbarui Angka
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4" id="statsGrid">
                        <!-- Loading State -->
                        <div class="col-span-full p-8 text-center bg-white rounded-3xl border border-slate-200">
                            <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i>
                            <p class="text-xs font-bold text-slate-400 mt-2">Menghitung data transaksi...</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Pemilihan Mode Reset -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-black">1</span>
                            Pilih Mode Pembersihan Go-Live
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Tentukan tingkat pembersihan database sesuai dengan kebutuhan peluncuran toko Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Mode A: Standar Go-Live (Rekomendasi) -->
                        <label class="relative block p-6 rounded-3xl border-2 border-blue-500 bg-blue-50/20 hover:bg-blue-50/40 transition-all cursor-pointer shadow-sm group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <input type="radio" name="resetMode" value="standar" checked class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-slate-800 text-sm">Mode Standar Go-Live</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-700">Direkomendasikan</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                            Cocok jika Anda sudah selesai menginput data Master (Katalog Produk, Resep BOM, Bahan Baku, dan Supplier).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-blue-100 space-y-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-700 font-medium">
                                    <i class="fa-solid fa-check text-emerald-600"></i>
                                    <span>Hapus semua riwayat <strong>Produksi, Rencana, & Mutasi</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-700 font-medium">
                                    <i class="fa-solid fa-check text-emerald-600"></i>
                                    <span>Hapus semua transaksi <strong>PO, PR, Masuk/Keluar, & Penjualan</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-700 font-medium">
                                    <i class="fa-solid fa-check text-emerald-600"></i>
                                    <span>Reset seluruh nilai <strong>Stok Bahan & Produk Menjadi 0</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-blue-700 font-bold">
                                    <i class="fa-solid fa-shield-halved text-blue-600"></i>
                                    <span><strong>Tetap Menyimpan:</strong> Master Produk, Resep, Bahan, & Akun User</span>
                                </div>
                            </div>
                        </label>

                        <!-- Mode B: Reset Total / Factory Reset -->
                        <label class="relative block p-6 rounded-3xl border-2 border-slate-200 hover:border-rose-400 bg-white hover:bg-rose-50/20 transition-all cursor-pointer shadow-sm group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <input type="radio" name="resetMode" value="total" class="mt-1 w-5 h-5 text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-black text-slate-800 text-sm">Factory Reset Total</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-100 text-rose-700">Bersih Mutlak</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                            Gunakan jika ingin memulai dari nol total seperti aplikasi yang baru pertama kali di-install.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-700 font-medium">
                                    <i class="fa-solid fa-check text-rose-600"></i>
                                    <span>Hapus seluruh transaksi & nol-kan semua stok</span>
                                </div>
                                <div class="flex items-center gap-2 text-rose-700 font-medium">
                                    <i class="fa-solid fa-trash-can text-rose-600"></i>
                                    <span>Hapus seluruh <strong>Master Produk, Resep (BOM), & Bahan Baku</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-rose-700 font-medium">
                                    <i class="fa-solid fa-trash-can text-rose-600"></i>
                                    <span>Hapus data <strong>Kategori, Supplier, & Pelanggan POS</strong></span>
                                </div>
                                <div class="flex items-center gap-2 text-blue-700 font-bold">
                                    <i class="fa-solid fa-shield-halved text-blue-600"></i>
                                    <span><strong>Hanya Menyimpan:</strong> Akun Login (`users`), Role, Dapur, & Toko</span>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>

                <!-- Section 3: Verifikasi Keamanan & Eksekusi -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-black">2</span>
                            Otorisasi Keamanan Owner
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Konfirmasi identitas Anda sebagai pemilik sistem untuk mencegah eksekusi tanpa sengaja.</p>
                    </div>

                    <form id="formReset" class="space-y-6 max-w-2xl">
                        
                        <!-- Input 1: Ketik Kata Konfirmasi -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">
                                Ketik frasa konfirmasi: <span class="text-rose-600 font-mono select-all bg-rose-50 px-2 py-0.5 rounded border border-rose-200">RESET DATA LIVE</span>
                            </label>
                            <input type="text" id="confirmText" required autocomplete="off"
                                placeholder="Ketik persis: RESET DATA LIVE"
                                class="w-full px-4 py-3 border border-slate-300 rounded-2xl outline-none focus:border-rose-600 focus:ring-4 focus:ring-rose-100 font-black text-slate-800 text-sm bg-slate-50 focus:bg-white tracking-widest uppercase transition-all">
                        </div>

                        <!-- Input 2: Password Akun Owner -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">
                                Masukkan Password Akun Anda (Owner) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="ownerPassword" required 
                                    placeholder="Password login akun Anda..."
                                    class="w-full px-4 py-3 border border-slate-300 rounded-2xl outline-none focus:border-rose-600 focus:ring-4 focus:ring-rose-100 font-bold text-slate-800 text-sm bg-slate-50 focus:bg-white transition-all pr-12">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                                    <i id="eyeIcon" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Eksekusi -->
                        <div class="pt-4 flex flex-col sm:flex-row items-center gap-4">
                            <button type="submit" id="btnExecuteReset" class="w-full sm:w-auto px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-rose-200 flex items-center justify-center gap-2 group">
                                <i class="fa-solid fa-triangle-exclamation text-sm group-hover:rotate-12 transition-transform"></i>
                                Eksekusi Reset Database Sekarang
                            </button>
                            <a href="<?= BASE_URL ?>owner/dashboard/" class="w-full sm:w-auto px-6 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl font-bold text-xs text-center transition-all">
                                Batal & Kembali ke Dashboard
                            </a>
                        </div>

                    </form>
                </div>

            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>
