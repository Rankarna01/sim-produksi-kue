<?php
require_once '../../../config/auth.php';
checkPermission('trx_po');
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

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ tab: 'semua' }">

            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Purchase Orders (PO)</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola dokumen pesanan pembelian bahan baku ke Supplier.</p>
                </div>
                <button @click="tab = 'buat_po'; loadDraftPO();" x-show="tab !== 'buat_po'" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Buat PO Baru
                </button>
                <button @click="tab = 'semua'; switchTabPO('semua');" x-show="tab === 'buat_po'" x-cloak class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke List PO
                </button>
            </div>

            <div x-show="tab !== 'buat_po'" class="space-y-6">
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex border-b border-slate-100 w-full md:w-auto overflow-x-auto custom-scrollbar">
                        <button @click="tab = 'semua'; switchTabPO('semua');" :class="tab === 'semua' ? 'border-slate-800 text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-3 px-4 text-xs font-black uppercase tracking-widest border-b-2 transition-all whitespace-nowrap">Semua PO</button>
                        <button @click="tab = 'belum_terima'; switchTabPO('belum_terima');" :class="tab === 'belum_terima' ? 'border-slate-800 text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-3 px-4 text-xs font-black uppercase tracking-widest border-b-2 transition-all whitespace-nowrap">Belum Diterima</button>
                        <button @click="tab = 'sudah_terima'; switchTabPO('sudah_terima');" :class="tab === 'sudah_terima' ? 'border-slate-800 text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-3 px-4 text-xs font-black uppercase tracking-widest border-b-2 transition-all whitespace-nowrap">Sudah Diterima</button>
                        <button @click="tab = 'dibatalkan'; switchTabPO('dibatalkan');" :class="tab === 'dibatalkan' ? 'border-slate-800 text-slate-800' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-3 px-4 text-xs font-black uppercase tracking-widest border-b-2 transition-all whitespace-nowrap">Dibatalkan</button>
                    </div>

                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search-po" placeholder="Cari PO (ID, Supplier, Barang)" class="w-full pl-11 pr-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all text-xs font-bold text-slate-600 bg-slate-50" onkeyup="cariDataPO()">
                    </div>
                </div>

                <div class="space-y-4" id="container-list-po">
                    <div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></div>
                </div>
            </div>

            <div x-show="tab === 'buat_po'" x-cloak class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-black text-slate-800 text-lg">Draft PO Baru</h3>
                </div>

                <div class="p-6 md:p-8">
                    <div class="bg-[#FFFAEB] border border-[#FDE68A] rounded-2xl p-6 mb-8">
                        <h4 class="font-black text-amber-700 text-sm flex items-center gap-2 mb-4">
                            <i class="fa-regular fa-comment-dots text-lg"></i> Permintaan Barang Pending
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-amber-800/60 border-b border-amber-200/60 text-xs tracking-widest uppercase">
                                        <th class="pb-3 font-black">Tgl</th>
                                        <th class="pb-3 font-black">Barang</th>
                                        <th class="pb-3 font-black">Qty</th>
                                        <th class="pb-3 font-black">Dari</th>
                                        <th class="pb-3 font-black">Catatan</th>
                                        <th class="pb-3 font-black text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="list-pr-pending" class="divide-y divide-amber-200/40 text-amber-900 font-bold text-xs">
                                    <tr><td colspan="6" class="py-6 text-center italic opacity-70">Memuat permintaan...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <form id="form-po" class="space-y-8 border-b border-slate-100 pb-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Pilih Supplier <span class="text-rose-500">*</span></label>
                                <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none bg-slate-50 font-bold text-slate-700">
                                    <option value="">-- Pilih Supplier --</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Tanggal Pengiriman (Wajib)</label>
                                <input type="date" id="shipping_date" name="shipping_date" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none bg-slate-50 font-bold text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Dibuat Oleh</label>
                                <input type="text" value="<?= $_SESSION['name'] ?? 'Administrator' ?>" readonly class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none bg-slate-100 text-slate-400 font-bold cursor-not-allowed">
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <h4 class="font-black text-slate-700 text-sm mb-4">Item Barang (Manual)</h4>
                            <div class="flex flex-col md:flex-row gap-4 items-end">
                                <div class="flex-1 w-full relative">
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Cari Nama Barang <span class="text-rose-500">*</span></label>
                                    <input type="text" id="search_material" placeholder="Ketik nama atau SKU bahan..." autocomplete="off" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-white shadow-sm" onkeyup="filterMaterialList()">
                                    <input type="hidden" id="item_material_id">
                                    <div id="material_list" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden custom-scrollbar"></div>
                                </div>
                                <div class="w-full md:w-32">
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Jumlah</label>
                                    <input type="number" step="any" id="item_qty" value="1" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none bg-white font-black text-blue-600 text-center shadow-sm">
                                </div>
                                <button type="button" onclick="tambahItemManual()" class="w-full md:w-auto bg-slate-800 hover:bg-black text-white px-8 py-3 rounded-xl font-black transition-all shadow-sm">
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-black text-slate-800 text-base flex items-center gap-2"><i class="fa-solid fa-cart-flatbed text-slate-400"></i> Daftar Item PO</h4>
                            <button type="button" onclick="simpanPO()" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
                                Simpan PO
                            </button>
                        </div>
                        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        <th class="p-5">Nama Barang</th>
                                        <th class="p-5 text-center w-40">Qty / Satuan</th>
                                        <th class="p-5 text-center w-20">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-po" class="divide-y divide-slate-50 font-bold text-slate-700">
                                    <tr><td colspan="3" class="p-10 text-center text-slate-400 italic text-xs">Belum ada item yang ditambahkan.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <div id="modal-terima-barang" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-terima-barang')"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-3xl shadow-xl z-10 flex flex-col overflow-hidden max-h-full">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="text-lg font-black text-slate-800" id="terima-po-title">Penerimaan Barang PO: ---</h3>
                <button onclick="closeModal('modal-terima-barang')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-slate-50/30">
                <table class="w-full text-left text-sm mb-6 bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <th class="p-4">Barang</th>
                            <th class="p-4 text-center w-24">Qty PO</th>
                            <th class="p-4 text-center w-28">Qty Terima</th>
                            <th class="p-4 w-24 text-center">Satuan</th>
                            <th class="p-4 w-32">Harga Satuan (Total)</th>
                            <th class="p-4 w-40">Expired Date</th>
                            <th class="p-4 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="terima-po-items" class="divide-y divide-slate-100">
                        <tr><td colspan="7" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data...</td></tr>
                    </tbody>
                </table>

                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex flex-col sm:flex-row items-end gap-4">
                    <div class="flex-1 w-full">
                        <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Tambah Barang Lain (Opsional)</label>
                        <select id="terima_extra_item" class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none font-bold text-slate-700 bg-white">
                            <option value="">-- Pilih Barang --</option>
                        </select>
                    </div>
                    <button type="button" onclick="addExtraTerimaItem()" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2.5 rounded-lg font-bold text-xs hover:bg-blue-700 transition-all">Tambah</button>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0 rounded-b-3xl">
                <button onclick="closeModal('modal-terima-barang')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all text-xs uppercase tracking-widest">Batal</button>
                <button onclick="submitTerimaBarang()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-emerald-200 flex items-center gap-2"><i class="fa-solid fa-check"></i> Simpan & Terima Barang</button>
            </div>
        </div>
    </div>

    <div id="modal-retur-po" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-retur-po')"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-3xl shadow-xl z-10 flex flex-col overflow-hidden max-h-full border-t-8 border-rose-500">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-rose-50 shrink-0">
                <div>
                    <h3 class="text-lg font-black text-rose-700" id="retur-po-title">Pengajuan Retur PO: ---</h3>
                    <p class="text-[10px] text-rose-500 font-bold uppercase tracking-widest mt-1">Mengurangi Tagihan dan Stok Gudang</p>
                </div>
                <button onclick="closeModal('modal-retur-po')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-white"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <div class="bg-rose-50/50 p-4 rounded-xl border border-rose-100 mb-6 text-xs text-rose-700 font-bold">
                    <i class="fa-solid fa-circle-info mr-1"></i> Isi angka pada kolom "Qty Retur" untuk barang yang ingin dikembalikan karena rusak/sobek. Kosongkan (isi 0) jika barang aman.
                </div>
                <table class="w-full text-left text-sm mb-6 bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <th class="p-4">Barang & Satuan</th>
                            <th class="p-4 text-center w-28">Harga Satuan</th>
                            <th class="p-4 text-center w-24 bg-blue-50/50">Diterima</th>
                            <th class="p-4 text-center w-28 bg-rose-50/50">Qty Retur</th>
                        </tr>
                    </thead>
                    <tbody id="retur-po-items" class="divide-y divide-slate-100 font-bold text-slate-700">
                        <tr><td colspan="4" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data...</td></tr>
                    </tbody>
                </table>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Alasan Retur Keseluruhan <span class="text-rose-500">*</span></label>
                    <textarea id="retur_reason" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-500 outline-none font-medium text-slate-700" placeholder="Contoh: Ada 2 karung tepung yang sobek digigit tikus..." required></textarea>
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 shrink-0 rounded-b-3xl">
                <button onclick="closeModal('modal-retur-po')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">Batal</button>
                <button onclick="submitReturPO()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-rose-200 flex items-center gap-2"><i class="fa-solid fa-paper-plane"></i> Ajukan Retur</button>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax_list.js?v=<?= time() ?>"></script>
    <script src="ajax_form.js?v=<?= time() ?>"></script>
</body>
</html>