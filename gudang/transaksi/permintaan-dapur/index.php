<?php
require_once '../../../config/auth.php';
checkPermission('trx_permintaan_dapur');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ statusFilter: 'semua' }">
            <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Permintaan Dapur (Internal)</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola dan proses permintaan bahan baku dari cabang/dapur.</p>
                </div>
                <div class="flex gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm text-[10px] font-black uppercase tracking-widest self-start sm:self-auto">
                    <button class="px-4 py-2 hover:bg-slate-50 rounded-xl">Harian</button>
                    <button class="px-4 py-2 hover:bg-slate-50 rounded-xl">Periode</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-md">Semua</button>
                </div>
            </div>

            <div class="flex border-b border-slate-200 mb-6 gap-6 overflow-x-auto custom-scrollbar">
                <button @click="statusFilter = 'semua'; loadData(1, 'semua');" :class="statusFilter === 'semua' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Semua</button>
                <button @click="statusFilter = 'menunggu'; loadData(1, 'menunggu');" :class="statusFilter === 'menunggu' ? 'border-amber-500 text-amber-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Menunggu</button>
                <button @click="statusFilter = 'diproses'; loadData(1, 'diproses');" :class="statusFilter === 'diproses' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Diproses</button>
                <button @click="statusFilter = 'ditolak'; loadData(1, 'ditolak');" :class="statusFilter === 'ditolak' ? 'border-rose-500 text-rose-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Ditolak</button>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 bg-slate-50/50">
                                <th class="p-5">Waktu Request</th>
                                <th class="p-5">Barang Diminta</th>
                                <th class="p-5">Jumlah</th>
                                <th class="p-5">Peminta (Dapur)</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50">
                            <tr><td colspan="6" class="p-10 text-center text-slate-400 font-bold animate-pulse">Memuat Data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="pagination" class="mt-6 flex justify-center gap-2 pb-10"></div>
        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>