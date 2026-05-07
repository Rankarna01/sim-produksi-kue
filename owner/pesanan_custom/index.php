<?php
require_once '../../config/auth.php';
checkPermission('pesanan_custom');
$page_title = "Pesanan Dapur - Produksi";
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full bg-slate-50/50">
            
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-fire-burner text-orange-500"></i> Layar Pesanan Dapur
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 font-bold">Pantau dan kerjakan pesanan khusus (Custom Item) dari Kasir POS secara real-time.</p>
                </div>
                <button onclick="loadOrders()" class="bg-white border border-slate-200 text-slate-600 hover:text-primary hover:border-primary px-4 py-2 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-rotate-right"></i> Refresh Data
                </button>
            </div>

            <div id="order-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div id="loader" class="col-span-full text-center py-10">
                    <i class="fa-solid fa-circle-notch fa-spin text-4xl text-orange-500 mb-3"></i>
                    <p class="font-bold text-slate-500">Menarik data dari Kasir...</p>
                </div>
                </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>