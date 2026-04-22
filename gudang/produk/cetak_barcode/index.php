<?php
require_once '../../../config/auth.php';
checkPermission('cetak_barcode');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Cetak Barcode Produk</h2>
                <p class="text-sm text-slate-500 mt-1">Buat dan cetak label barcode untuk inventaris fisik barang & rak.</p>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8 mb-6">
                <h3 class="font-black text-slate-800 text-base mb-6">Pilih Produk</h3>
                <div class="space-y-6">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full relative">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cari Produk</label>
                            <div class="absolute inset-y-0 left-0 pl-4 top-6 flex items-center pointer-events-none">
                                <i class="fa-solid fa-search text-slate-400"></i>
                            </div>
                            <select id="select_product" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-slate-50">
                                <option value="">-- Loading Data... --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-32">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Qty</label>
                            <input type="number" id="qty_product" value="1" min="1" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-black text-blue-600 text-center bg-slate-50">
                        </div>
                        <button onclick="addProduk()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black shadow-md shadow-blue-200 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i> Tambah
                        </button>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full relative">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cari Rak (Cetak Barcode Rak)</label>
                            <div class="absolute inset-y-0 left-0 pl-4 top-6 flex items-center pointer-events-none">
                                <i class="fa-solid fa-server text-slate-400"></i>
                            </div>
                            <select id="select_rack" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:border-emerald-600 outline-none font-bold text-slate-700 bg-slate-50">
                                <option value="">-- Loading Data... --</option>
                            </select>
                        </div>
                        <div class="w-full md:w-32">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Qty Cetak</label>
                            <input type="number" id="qty_rack" value="1" min="1" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-black text-emerald-600 text-center bg-slate-50">
                        </div>
                        <button onclick="addRak()" class="w-full md:w-auto bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-black shadow-md shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak Rak
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8 mb-6">
                <h3 class="font-black text-slate-800 text-base mb-6">Atur Tampilan Barcode</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tinggi Barcode</label>
                        <input type="number" id="set_height" value="30" oninput="updatePreview()" class="w-full px-4 py-2 border border-slate-300 rounded-lg font-bold text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lebar Barcode</label>
                        <input type="number" step="0.5" id="set_width" value="1" oninput="updatePreview()" class="w-full px-4 py-2 border border-slate-300 rounded-lg font-bold text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipe Barcode</label>
                        <select id="set_format" onchange="updatePreview()" class="w-full px-4 py-2 border border-slate-300 rounded-lg font-bold text-sm bg-slate-50">
                            <option value="CODE128">C128</option>
                            <option value="EAN13">EAN13</option>
                            <option value="UPC">UPC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipe Kertas</label>
                        <select id="set_paper" onchange="toggleCustomLayout()" class="w-full px-4 py-2 border border-slate-300 rounded-lg font-bold text-sm bg-slate-50">
                            <option value="custom">Custom</option>
                            <option value="auto">Auto Fit</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs font-bold text-slate-600">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_hide_text" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> Sembunyikan SKU/Barcode Text</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_hide_name" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> Sembunyikan Nama Produk</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_hide_price" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> Sembunyikan Harga</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_sku_top" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> SKU di Atas</label>
                    
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_name_bottom" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600" checked> Nama Produk di Bawah</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_show_rack_text" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> Tampilkan Lokasi Rak (Teks)</label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600"><input type="checkbox" id="chk_show_rack_barcode" onchange="updatePreview()" class="w-4 h-4 rounded text-blue-600"> Tampilkan Barcode Rak</label>
                </div>
            </div>

            <div id="custom_layout_panel" class="bg-white rounded-3xl border border-blue-200 shadow-sm p-6 md:p-8 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-black text-blue-600 text-base"><i class="fa-solid fa-ruler-combined"></i> Gunakan Custom Layout (Untuk Kertas Thermal)</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Tinggi Kertas/Label (px)</label>
                        <input type="number" id="ly_h" value="150" class="w-full px-3 py-2 border rounded-lg text-sm font-bold bg-blue-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Lebar Kertas/Label (px)</label>
                        <input type="number" id="ly_w" value="200" class="w-full px-3 py-2 border rounded-lg text-sm font-bold bg-blue-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Margin (px)</label>
                        <input type="number" id="ly_m" value="0" class="w-full px-3 py-2 border rounded-lg text-sm font-bold bg-blue-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Padding Dalam (px)</label>
                        <input type="number" id="ly_p" value="5" class="w-full px-3 py-2 border rounded-lg text-sm font-bold bg-blue-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Lebar Kertas Total (px)</label>
                        <input type="number" id="ly_paper_w" value="200" class="w-full px-3 py-2 border rounded-lg text-sm font-bold bg-blue-50" title="Contoh: 200 untuk printer thermal 1 baris">
                    </div>
                </div>
                <button onclick="updatePreview()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-xs font-black transition-all shadow-md">Simpan Layout</button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-slate-800 text-lg flex items-center gap-2"><i class="fa-solid fa-eye text-slate-400"></i> Print Preview</h3>
                    <div class="flex gap-2">
                        <button onclick="clearQueue()" class="bg-white border border-rose-200 text-rose-500 hover:bg-rose-500 hover:text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all">Bersihkan</button>
                        <button onclick="prosesCetak()" class="bg-slate-500 hover:bg-slate-700 text-white px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak Sekarang
                        </button>
                    </div>
                </div>
                
                <div class="flex-1 bg-slate-200 p-8 flex justify-center overflow-auto custom-scrollbar">
                    <div id="preview_paper" class="bg-white shadow-xl flex flex-wrap content-start transition-all" style="width: 200px;">
                        <div class="w-full text-center py-20 text-slate-400 font-bold italic opacity-60 flex flex-col items-center gap-3">
                            <i class="fa-solid fa-barcode text-5xl"></i>
                            <p>Preview area</p>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>