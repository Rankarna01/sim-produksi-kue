<?php
require_once '../../../config/auth.php';
// checkPermission('transaksi_pilar'); 
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
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Permintaan Barang</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola dan monitor semua riwayat permintaan bahan dari dapur.</p>
                </div>
                <div class="flex gap-2 bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm text-[10px] font-black uppercase tracking-widest">
                    <button class="px-4 py-2 hover:bg-slate-50 rounded-xl">Harian</button>
                    <button class="px-4 py-2 hover:bg-slate-50 rounded-xl">Periode</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-xl shadow-md">Semua</button>
                </div>
            </div>

            <div class="flex border-b border-slate-200 mb-6 gap-8">
                <button @click="statusFilter = 'semua'; loadData(1, 'semua');" :class="statusFilter === 'semua' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all">Semua</button>
                <button @click="statusFilter = 'menunggu'; loadData(1, 'menunggu');" :class="statusFilter === 'menunggu' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all">Menunggu</button>
                <button @click="statusFilter = 'diproses'; loadData(1, 'diproses');" :class="statusFilter === 'diproses' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all">Diproses</button>
                <button @click="statusFilter = 'ditolak'; loadData(1, 'ditolak');" :class="statusFilter === 'ditolak' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all">Ditolak</button>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 bg-slate-50/50">
                                <th class="p-5">Tanggal</th>
                                <th class="p-5">Barang</th>
                                <th class="p-5">Jumlah</th>
                                <th class="p-5">Pengirim (Dapur)</th>
                                <th class="p-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50">
                            </tbody>
                    </table>
                </div>
            </div>
            
            <div id="pagination" class="mt-6 flex justify-center gap-2 pb-10"></div>
        </main>
    </div>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>