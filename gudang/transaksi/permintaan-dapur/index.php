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
                    <p class="text-sm text-slate-500 mt-1">Kelola dan proses invoice permintaan bahan baku dari cabang/dapur.</p>
                </div>
            </div>

            <div class="flex border-b border-slate-200 mb-6 gap-6 overflow-x-auto custom-scrollbar">
                <button @click="statusFilter = 'semua'; loadData(1, 'semua');" :class="statusFilter === 'semua' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Semua</button>
                <button @click="statusFilter = 'menunggu'; loadData(1, 'menunggu');" :class="statusFilter === 'menunggu' ? 'border-amber-500 text-amber-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Menunggu</button>
                <button @click="statusFilter = 'diproses'; loadData(1, 'diproses');" :class="statusFilter === 'diproses' ? 'border-emerald-500 text-emerald-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Selesai/Diproses</button>
                <button @click="statusFilter = 'ditolak'; loadData(1, 'ditolak');" :class="statusFilter === 'ditolak' ? 'border-rose-500 text-rose-500' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 px-2 text-xs font-black uppercase tracking-widest border-b-4 transition-all whitespace-nowrap">Ditolak</button>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100 bg-slate-50/50">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Waktu Request</th>
                                <th class="p-5">No. Invoice</th>
                                <th class="p-5">Peminta (Dapur)</th>
                                <th class="p-5 text-center">Jml Barang</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50">
                            <tr><td colspan="7" class="p-10 text-center text-slate-400 font-bold animate-pulse">Memuat Data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="pagination" class="mt-6 flex justify-center gap-2 pb-10"></div>
        </main>
    </div>

    <div id="modal-proses" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-proses')"></div>
        <div class="bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-[2.5rem]">
                <div>
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-boxes-packing text-blue-600"></i> Proses Permintaan</h3>
                    <p class="text-xs font-bold text-slate-500 mt-1">Invoice: <span id="modal_req_no" class="text-blue-600">REQ-XXX</span> | Dapur: <span id="modal_dapur_name" class="text-slate-800">Nama</span></p>
                </div>
                <button onclick="closeModal('modal-proses')" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="overflow-y-auto flex-1 p-6 custom-scrollbar">
                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left text-sm border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="p-4 text-center">No</th>
                                <th class="p-4">Nama Bahan Baku</th>
                                <th class="p-4 text-center">Sisa Gudang Pilar</th>
                                <th class="p-4 text-center">Qty Diminta</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center w-32">Aksi Proses</th>
                            </tr>
                        </thead>
                        <tbody id="table-detail" class="divide-y divide-slate-50 font-medium">
                            </tbody>
                    </table>
                </div>
            </div>
            
            <div class="p-6 border-t border-slate-100 flex justify-end gap-3 rounded-b-[2.5rem]">
                <button type="button" onclick="closeModal('modal-proses')" class="px-8 py-3 text-sm font-black text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>