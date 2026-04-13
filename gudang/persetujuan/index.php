<?php
require_once '../../config/auth.php';
// checkPermission('persetujuan_pilar'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ tab: 'permintaan' }">
            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Menu Persetujuan</h2>
                <p class="text-sm text-slate-500 mt-1">Kelola permintaan stok, PO supplier, dan izin operasional.</p>
            </div>

            <div class="flex flex-wrap gap-2 mb-6 bg-white p-2 rounded-2xl shadow-sm border border-slate-200 w-max">
                <button @click="tab = 'manual'" :class="tab === 'manual' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-box mr-2"></i> Transaksi Manual
                </button>
                <button @click="tab = 'po'" :class="tab === 'po' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-cart-shopping mr-2"></i> Purchase Orders
                </button>
                <button @click="tab = 'izin'" :class="tab === 'izin' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-print mr-2"></i> Izin Cetak
                </button>
                <button @click="tab = 'permintaan'" :class="tab === 'permintaan' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                    <i class="fa-solid fa-truck-ramp-box mr-2"></i> Permintaan Barang
                </button>
            </div>

            <div x-show="tab === 'permintaan'" x-transition class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-black text-slate-700 text-sm uppercase tracking-tighter">Daftar Antrean Permintaan Dapur</h3>
                    <button onclick="loadPermintaan()" class="text-blue-600 hover:rotate-180 transition-transform duration-500">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="p-5 text-center">No</th>
                                <th class="p-5">Tgl & Request No</th>
                                <th class="p-5">Asal Dapur</th>
                                <th class="p-5">Bahan Baku</th>
                                <th class="p-5 text-center">Qty Minta</th>
                                <th class="p-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-permintaan" class="text-sm divide-y divide-slate-50">
                            </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab !== 'permintaan'" class="p-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <i class="fa-solid fa-screwdriver-wrench text-4xl text-slate-200 mb-4"></i>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Fitur sedang dalam pengembangan</p>
            </div>

        </main>
    </div>

    <div id="modal-proses" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden animate-fade-in-up">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Proses Kirim Barang</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="form-proses" class="p-8 space-y-5">
                <input type="hidden" name="action" value="proses_kirim">
                <input type="hidden" id="modal-id" name="id">
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bahan yang diminta</label>
                    <input type="text" id="modal-bahan" readonly class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-500 outline-none cursor-not-allowed">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Permintaan Dapur</label>
                        <input type="text" id="modal-qty-minta" readonly class="w-full px-5 py-3 bg-blue-50 border border-blue-100 rounded-2xl font-black text-blue-600 outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2">Jumlah Dikirim</label>
                        <input type="number" step="any" name="qty_approved" id="modal-qty-kirim" required class="w-full px-5 py-3 border-2 border-blue-600 rounded-2xl font-black text-slate-800 outline-none focus:ring-4 focus:ring-blue-100 transition-all">
                    </div>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="rejectPermintaan()" class="flex-1 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-red-500 bg-red-50 hover:bg-red-100 transition-all">Tolak</button>
                    <button type="submit" class="flex-[2] py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-white bg-blue-600 shadow-lg shadow-blue-200 hover:opacity-90 transition-all active:scale-95">Setujui & Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>