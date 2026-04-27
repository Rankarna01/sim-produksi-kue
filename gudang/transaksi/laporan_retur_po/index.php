<?php
require_once '../../../config/auth.php';
// Akses disamakan dengan PO atau Laporan PO
checkPermission('trx_po');
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
            
            <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Retur PO</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau riwayat pengembalian barang ke supplier dan pemotongan tagihan.</p>
                </div>
                <div class="flex gap-2 w-full lg:w-auto">
                    <button onclick="exportData('excel')" class="flex-1 lg:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-regular fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="exportData('pdf')" class="flex-1 lg:flex-none bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-print"></i> Cetak PDF
                    </button>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                    <input type="date" id="start_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                    <input type="date" id="end_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status Retur</label>
                    <select id="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                        <option value="semua">Semua Status</option>
                        <option value="pending">Menunggu Persetujuan</option>
                        <option value="approved">Disetujui (Selesai)</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
                <div>
                    <button onclick="loadData(1)" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[1000px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 text-center w-12">No</th>
                                <th class="p-5">Tanggal Retur</th>
                                <th class="p-5">No. PO & Supplier</th>
                                <th class="p-5">Barang</th>
                                <th class="p-5 text-center">Qty Retur</th>
                                <th class="p-5 text-right">Potongan Harga</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="pagination" class="mt-6 flex items-center justify-center gap-2 pb-10"></div>

        </main>
    </div>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>