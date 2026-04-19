<?php
require_once '../../../config/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Otorisasi Akses</h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">Generate kode PIN untuk akses fitur audit Stok Opname (Berlaku 24 jam).</p>
                </div>
                <button onclick="generateKey()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl text-sm font-black tracking-widest uppercase transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> Generate Kode Baru
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                    <h3 class="font-black text-slate-700 text-sm uppercase tracking-widest">Riwayat Kode Akses</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Kode PIN</th>
                                <th class="p-5">Dibuat Pada</th>
                                <th class="p-5">Berlaku Sampai</th>
                                <th class="p-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-50 font-medium text-slate-700">
                            <tr><td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50/50"></div>
            </div>

        </main>
    </div>

    <div id="modal-success" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl z-10 transform transition-all flex flex-col p-8 text-center animate-fade-in-up">
            
            <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-2xl text-emerald-500"></i>
                </div>
            </div>
            
            <h3 class="text-2xl font-black text-slate-800 tracking-tighter mb-2">Kode Akses Dibuat</h3>
            
            <div class="mt-4 mb-2 flex items-center justify-center gap-3">
                <span class="text-sm font-bold text-slate-400">Kode:</span>
                <span id="new-pin-code" class="text-3xl font-black text-indigo-600 tracking-[0.3em]">186641</span>
            </div>
            
            <p id="new-pin-expiry" class="text-xs font-bold text-slate-400 mb-8">(Berlaku sampai 19/04/2026, 19:28:15)</p>
            
            <button onclick="closeModal()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-base transition-all shadow-lg shadow-emerald-200">
                Ya
            </button>
        </div>
    </div>

    <style>
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>