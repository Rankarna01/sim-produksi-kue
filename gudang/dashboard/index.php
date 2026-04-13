<?php
require_once '../../config/auth.php';
// checkPermission('view_dashboard_gudang'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Logistik Dashboard</h2>
                    <p class="text-sm text-slate-500 mt-1">Ringkasan stok pilar utama dan status pengadaan barang.</p>
                </div>
                <div class="text-sm font-bold text-slate-500 bg-white px-5 py-2.5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-blue-600"></i> <?= date('d F Y') ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4 shadow-inner">
                        <i class="fa-solid fa-cart-flatbed"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">PO Berjalan</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-po">12 <span class="text-xs font-bold text-slate-400 italic">Order</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-hourglass-half animate-pulse"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Butuh Approval</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-req">5 <span class="text-xs font-bold text-slate-400 italic">Request</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Stok Kritis</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-kritis">8 <span class="text-xs font-bold text-slate-400 italic">Item</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Hutang PO</h3>
                    <div class="text-2xl font-black text-slate-800">Rp 4.5M</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 lg:col-span-2">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tighter">Permintaan Dapur Terbaru</h3>
                        <a href="../transaksi/permintaan/" class="text-xs font-bold text-blue-600 hover:underline">Kelola Semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="pb-4">Asal Dapur</th>
                                    <th class="pb-4">Nama Bahan</th>
                                    <th class="pb-4 text-center">Jumlah</th>
                                    <th class="pb-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-50">
                                <tr>
                                    <td class="py-4 font-bold text-slate-700">Dapur 01</td>
                                    <td class="py-4 text-slate-500 italic text-xs">Tepung Terigu Segitiga Biru</td>
                                    <td class="py-4 text-center font-black text-blue-600">10 Sak</td>
                                    <td class="py-4 text-center">
                                        <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Menunggu</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-4 font-bold text-slate-700">Dapur 02</td>
                                    <td class="py-4 text-slate-500 italic text-xs">Gula Pasir Kristal</td>
                                    <td class="py-4 text-center font-black text-blue-600">5 Karung</td>
                                    <td class="py-4 text-center">
                                        <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-[10px] font-black uppercase">Proses</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
                        <h3 class="text-lg font-black text-slate-800 mb-4 uppercase tracking-tighter">Suplier Aktif</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-500">PT</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">PT. Sembako Jaya</p>
                                    <p class="text-[10px] text-slate-400 font-bold italic">Bahan Baku Utama</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-500">CV</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">CV. Plastik Makmur</p>
                                    <p class="text-[10px] text-slate-400 font-bold italic">Packaging & Plastik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script>
        console.log("Logistik Dashboard Ready");
    </script>
</body>
</html>