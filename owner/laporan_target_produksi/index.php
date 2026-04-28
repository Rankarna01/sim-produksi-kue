<?php
require_once '../../config/auth.php';
checkPermission('lap_target_produksi');
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
            
            <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Target vs Realisasi</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau kinerja produksi harian dari Dapur 1 dan Dapur 2.</p>
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
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Filter Lokasi Dapur</label>
                    <select id="kitchen_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                        <option value="semua">Semua Dapur</option>
                    </select>
                </div>
                <div>
                    <button onclick="loadData()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm min-w-[1000px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 text-center w-12">No</th>
                                <th class="p-5">Tanggal</th>
                                <th class="p-5">Dapur & Karyawan</th>
                                <th class="p-5">Produk</th>
                                <th class="p-5 text-center">Est. Adonan</th>
                                <th class="p-5 text-center">Target (Rencana)</th>
                                <th class="p-5 text-center">Aktual (Realisasi)</th>
                                <th class="p-5 text-center">Pencapaian</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>