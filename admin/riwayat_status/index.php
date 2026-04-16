<?php
require_once '../../config/auth.php';
checkRole(['admin', 'owner', 'auditor']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php 
        if($_SESSION['role'] == 'admin') include '../../components/sidebar_admin.php'; 
        else include '../../components/sidebar.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Riwayat Validasi Masuk Store</h2>
                <p class="text-sm text-secondary mt-1">Daftar produk yang telah berhasil Anda validasi dan masuk ke sistem stok Store.</p>
            </div>

            <div class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col sm:flex-row gap-4 items-end flex-wrap">
                    <div class="flex-1 w-full min-w-[150px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Mulai Tanggal</label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                    <div class="flex-1 w-full min-w-[150px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    </div>
                    
                    <div class="flex-1 w-full min-w-[150px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Asal Dapur</label>
                        <select id="kitchen_id" name="kitchen_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white">
                            <option value="">Semua Dapur</option>
                        </select>
                    </div>

                    <?php if($_SESSION['role'] !== 'admin'): ?>
                    <div class="flex-1 w-full min-w-[150px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Lokasi Store</label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-white">
                            <option value="">Semua Store</option>
                        </select>
                    </div>
                    <?php else: ?>
                        <input type="hidden" id="warehouse_id" value="">
                    <?php endif; ?>

                    <div class="w-full sm:w-auto flex gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl font-bold transition-all">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold w-16 text-center">No</th>
                                <th class="p-4 font-semibold">Tgl Validasi (Selesai)</th>
                                <th class="p-4 font-semibold">Produk</th>
                                <th class="p-4 font-semibold">Asal Dapur</th>
                                <th class="p-4 font-semibold text-center">Jumlah Masuk</th>
                                <th class="p-4 font-semibold">Store Tujuan</th>
                            </tr>
                        </thead>
                        <tbody id="table-history" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="6" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>const currentUserRole = "<?= $_SESSION['role'] ?>";</script>
    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>