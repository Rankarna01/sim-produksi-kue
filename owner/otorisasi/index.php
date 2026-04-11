<?php
require_once '../../config/auth.php';
// Gembok Keamanan: Hanya yang punya izin 'otorisasi' yang bisa masuk
checkPermission('otorisasi');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Otorisasi Akses</h2>
                    <p class="text-sm text-secondary mt-1">Generate kode PIN untuk akses fitur sensitif (Berlaku 24 Jam).</p>
                </div>
                <button onclick="generateAccessCode()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl text-sm font-bold transition-all shadow-lg flex items-center gap-2 shrink-0 active:scale-95">
                    <i class="fa-solid fa-key"></i> Generate Kode Baru
                </button>
            </div>

            <div class="bg-surface rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                    <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wider">Riwayat Kode Akses</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-100 text-[11px] text-secondary uppercase tracking-widest">
                                <th class="p-4 font-bold text-center w-16">No</th>
                                <th class="p-4 font-bold">Kode PIN</th>
                                <th class="p-4 font-bold">Dibuat Pada</th>
                                <th class="p-4 font-bold">Berlaku Sampai</th>
                                <th class="p-4 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-otorisasi" class="text-sm divide-y divide-slate-50">
                            <tr><td colspan="5" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-auth-success" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAuthModal()"></div>
        <div class="relative bg-white w-full max-w-sm rounded-[40px] shadow-2xl z-10 p-10 text-center transform transition-all scale-95 opacity-0" id="auth-card">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <div class="w-16 h-16 bg-emerald-100/50 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-4xl"></i>
                </div>
            </div>

            <h3 class="text-2xl font-black text-slate-800 mb-3">Kode Akses Dibuat</h3>
            <p class="text-sm text-slate-500 mb-8 leading-relaxed">
                Kode: <span id="display-code" class="font-black text-indigo-600 text-xl tracking-[0.3em] ml-2">000000</span><br>
                <span class="text-[11px] text-slate-400 mt-2 block">(Berlaku sampai <span id="display-expiry">...</span>)</span>
            </p>

            <button onclick="closeAuthModal()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg active:scale-95">
                Ya
            </button>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>