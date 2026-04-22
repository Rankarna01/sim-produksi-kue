<?php
require_once '../../../config/auth.php';
checkPermission('pengaturan_pembayaran');
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
            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                    <i class="fa-regular fa-credit-card text-blue-600"></i> Master Metode Pembayaran
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-black text-slate-800 text-sm mb-4">Tambah Metode Baru</h3>
                    
                    <form id="form-metode" class="flex flex-col sm:flex-row gap-3">
                        <input type="text" id="method_name" required placeholder="Contoh: E-Wallet, Kartu Kredit" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-blue-600 font-medium text-sm text-slate-700 transition-all bg-slate-50 focus:bg-white">
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                            <i class="fa-solid fa-plus"></i> Tambah
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-black text-slate-800 text-sm mb-4">Daftar Metode Pembayaran</h3>
                    
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <ul id="list-metode" class="divide-y divide-slate-100">
                            <li class="p-4 text-center text-slate-400 text-sm"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data...</li>
                        </ul>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>