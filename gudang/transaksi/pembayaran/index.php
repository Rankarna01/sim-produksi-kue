<?php
require_once '../../../config/auth.php';
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

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">

            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-credit-card text-3xl text-blue-600"></i>
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Pembayaran PO & Hutang</h2>
                        <p class="text-sm text-slate-500 mt-1">Kelola tagihan dan riwayat pembayaran ke Supplier.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select id="filter_status" onchange="loadBills()" class="px-4 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50 focus:border-blue-600">
                        <option value="belum_lunas">Belum Lunas (Hutang)</option>
                        <option value="unpaid">Belum Bayar</option>
                        <option value="partial">Bayar Sebagian</option>
                        <option value="paid">Sudah Lunas</option>
                        <option value="semua">Semua Status</option>
                    </select>

                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                        <i class="fa-regular fa-clock text-slate-400 mr-2"></i>
                        <span class="text-xs font-bold text-slate-500 mr-2">Periode:</span>
                        <input type="date" id="start_date" onchange="loadBills()" class="bg-transparent border-none outline-none text-xs font-bold text-slate-700 w-28">
                        <span class="mx-1 text-slate-300">-</span>
                        <input type="date" id="end_date" onchange="loadBills()" class="bg-transparent border-none outline-none text-xs font-bold text-slate-700 w-28">
                    </div>
                </div>

                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="search-bill" placeholder="Cari PO / Supplier..." class="w-full pl-11 pr-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all text-xs font-bold text-slate-600 bg-slate-50" onkeyup="cariBills()">
                </div>
            </div>

            <div class="space-y-4" id="container-bills">
                <div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i> Memuat data tagihan...</div>
            </div>

        </main>
    </div>

    <div id="modal-bayar" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-bayar')"></div>
        <div class="relative bg-white w-full max-w-3xl rounded-[2rem] shadow-xl z-10 flex flex-col overflow-hidden max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Pembayaran PO</h3>
                    <p class="text-sm font-bold text-slate-500" id="modal-po-no">LC-...</p>
                </div>
                <button onclick="closeModal('modal-bayar')" class="w-8 h-8 flex items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-white">
                
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="border border-slate-200 rounded-2xl p-4 text-center bg-slate-50">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Tagihan</p>
                        <p class="text-xl font-black text-slate-800" id="modal-total">Rp 0</p>
                    </div>
                    <div class="border border-emerald-200 rounded-2xl p-4 text-center bg-emerald-50">
                        <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">Sudah Dibayar</p>
                        <p class="text-xl font-black text-emerald-600" id="modal-dibayar">Rp 0</p>
                    </div>
                    <div class="border border-rose-200 rounded-2xl p-4 text-center bg-rose-50">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Sisa Tagihan</p>
                        <p class="text-xl font-black text-rose-600" id="modal-sisa">Rp 0</p>
                    </div>
                </div>

                <form id="form-pembayaran" class="mb-8 border border-blue-100 bg-blue-50/30 p-5 rounded-2xl">
                    <h4 class="font-black text-blue-700 text-sm mb-4 flex items-center gap-2"><i class="fa-solid fa-plus"></i> Input Pembayaran Baru</h4>
                    
                    <input type="hidden" id="pay_po_id">
                    <input type="hidden" id="pay_max_amount">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Bayar <span class="text-rose-500">*</span></label>
                            <input type="datetime-local" id="pay_date" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-700 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Jumlah Bayar (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" id="pay_amount" required placeholder="0" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-black text-blue-600 bg-white text-lg">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="pay_method" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-700 bg-white">
                            <option value="">-- Pilih Metode --</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Catatan (Opsional)</label>
                        <input type="text" id="pay_notes" placeholder="Contoh: Transfer BCA ke rek Supplier..." class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-medium text-slate-700 bg-white">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-black transition-all shadow-md shadow-blue-200">
                        Simpan Pembayaran
                    </button>
                </form>

                <div>
                    <h4 class="font-black text-slate-700 text-sm mb-3">Riwayat Pembayaran</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                    <th class="p-3 w-12 text-center">No</th>
                                    <th class="p-3">Tanggal Bayar</th>
                                    <th class="p-3">Metode</th>
                                    <th class="p-3">Catatan</th>
                                    <th class="p-3 text-right">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="table-riwayat" class="divide-y divide-slate-100 font-medium text-slate-600">
                                <tr><td colspan="5" class="p-6 text-center italic text-slate-400">Belum ada data pembayaran.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

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