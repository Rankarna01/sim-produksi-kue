<?php
require_once '../../config/auth.php';
checkPermission('audit_logs');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">
    <?php include '../../components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full relative">
            <div class="mb-6 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Monitoring Aktivitas</h2>
                    <p class="text-sm text-secondary mt-1">Jejak digital transaksi dan pengelolaan data master.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select id="filter-period" onchange="toggleCustomDate(); loadLogs(1);" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold shadow-sm outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="all">Semua Waktu</option>
                        <option value="today">Hari Ini</option>
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                        <option value="custom">Pilih Tanggal</option>
                    </select>

                    <div id="custom-date-container" class="hidden flex items-center gap-2">
                        <input type="date" id="start-date" onchange="loadLogs(1)" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none">
                        <span class="text-slate-400">-</span>
                        <input type="date" id="end-date" onchange="loadLogs(1)" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm outline-none">
                    </div>

                    <button onclick="loadLogs(1)" class="bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm flex items-center gap-2 hover:opacity-90">
                        <i class="fa-solid fa-sync"></i>
                    </button>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] text-secondary uppercase tracking-widest font-black">
                                <th class="p-4 text-center w-16">No</th>
                                <th class="p-4">Waktu Kejadian</th>
                                <th class="p-4">Pegawai</th>
                                <th class="p-4">Modul / Menu</th>
                                <th class="p-4">Aktivitas & Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="table-logs" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Memuat data log...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination" class="mt-8 flex justify-center gap-2 pb-10"></div>
        </main>
    </div>
    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>