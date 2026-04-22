<?php
require_once '../../config/auth.php';
checkPermission('data_opname'); // Pastikan permission_slug sesuai dengan di Manajemen Role
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Data Riwayat Stok Opname</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau hasil audit fisik gudang, selisih barang, dan status persetujuannya.</p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="search" placeholder="Cari No. Dokumen / Nama PIC..." onkeyup="cariData()" class="pl-8 pr-3 py-2 w-full border border-slate-300 rounded-xl outline-none text-sm focus:border-blue-600 shadow-sm transition-all bg-slate-50 focus:bg-white">
                    </div>
                    <select id="filter_status" onchange="loadData()" class="w-full sm:w-auto px-4 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-600 bg-slate-50 focus:bg-white">
                        <option value="semua">Semua Status</option>
                        <option value="approved">Approved (Selesai)</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1 shadow-sm w-full sm:w-auto">
                    <input type="date" id="start_date" onchange="loadData()" class="border-none outline-none text-xs font-bold text-slate-600 bg-transparent w-full">
                    <span class="text-slate-400 font-bold">-</span>
                    <input type="date" id="end_date" onchange="loadData()" class="border-none outline-none text-xs font-bold text-slate-600 bg-transparent w-full">
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Tgl. Opname</th>
                                <th class="p-5">No. Dokumen</th>
                                <th class="p-5 text-center">Total Item</th>
                                <th class="p-5">PIC (Auditor)</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination" class="mt-6 flex items-center justify-center gap-2"></div>

        </main>
    </div>

    <div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-detail')"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-[2rem] shadow-2xl z-10 flex flex-col overflow-hidden max-h-full">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div>
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-purple-600"></i> Detail Stok Opname
                    </h3>
                    <p class="text-xs font-bold text-slate-500 mt-1" id="detail-subtitle">Dokumen: --- | Tanggal: ---</p>
                </div>
                <button onclick="closeModal('modal-detail')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-6 sm:p-8 overflow-y-auto custom-scrollbar flex-1 bg-slate-50/50">
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Barang / SKU</th>
                                <th class="p-4 text-center border-l border-slate-200 bg-slate-50">Stok Sistem</th>
                                <th class="p-4 text-center bg-slate-50">Fisik (Aktual)</th>
                                <th class="p-4 text-center border-r border-slate-200 bg-slate-50">Selisih</th>
                                <th class="p-4">Catatan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody id="table-detail-items" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="6" class="p-8 text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0">
                <button onclick="closeModal('modal-detail')" class="px-6 py-3 rounded-xl font-black text-slate-500 hover:bg-slate-100 transition-all text-xs uppercase tracking-widest">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>