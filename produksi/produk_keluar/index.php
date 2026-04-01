<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Produk Keluar (Expired/Rusak)</h2>
                    <p class="text-sm text-secondary mt-1">Lacak dan catat produk yang ditarik dari etalase. Scan barcode struk agar lebih cepat.</p>
                </div>
                <button onclick="openModal('modal-keluar'); resetForm();" class="bg-danger hover:bg-red-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-minus-circle"></i> Catat Produk Keluar
                </button>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold text-center w-16">No</th>
                                <th class="p-4 font-semibold">Tanggal Tarik</th>
                                <th class="p-4 font-semibold">No. Invoice Asal</th>
                                <th class="p-4 font-semibold">Produk</th>
                                <th class="p-4 font-semibold text-center">Jumlah Tarik</th>
                                <th class="p-4 font-semibold text-center">Alasan</th>
                                <th class="p-4 font-semibold text-center w-28">Petugas</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="7" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-keluar" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalKeluar()"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-xl z-10 transform transition-all flex flex-col max-h-[95vh]">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-danger/5 rounded-t-3xl">
                <h3 class="text-lg font-bold text-danger"><i class="fa-solid fa-box-open mr-2"></i> Tarik Produk Expired</h3>
                <button onclick="closeModalKeluar()" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <div class="mb-4">
                    <button id="btnKamera" onclick="toggleKamera()" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2 mb-3">
                        <i class="fa-solid fa-camera"></i> Scan Barcode Struk
                    </button>

                    <div id="kameraContainer" class="hidden mb-4 bg-slate-50 p-2 rounded-2xl border border-slate-200">
                        <div class="flex justify-between items-center mb-2 px-2">
                            <span class="text-xs font-bold text-slate-700"><i class="fa-solid fa-video text-danger animate-pulse mr-1"></i> Kamera Aktif</span>
                            <button onclick="toggleKamera()" class="text-danger hover:text-red-700 text-xs font-bold bg-danger/10 px-2 py-1 rounded-lg">Tutup</button>
                        </div>
                        <div id="reader" class="w-full rounded-xl overflow-hidden bg-slate-200 min-h-[200px]"></div>
                    </div>

                    <div class="flex gap-2">
                        <input type="text" id="search_invoice" class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-danger outline-none font-mono text-sm placeholder:text-slate-300 uppercase" placeholder="Cth: BRC-2026...">
                        <button type="button" onclick="cariInvoice()" class="bg-slate-800 hover:bg-slate-900 text-white px-5 rounded-xl font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                </div>

                <form id="formKeluar">
                    <div id="form-details" class="space-y-4 hidden border-t border-slate-100 pt-5">
                        
                        <input type="hidden" id="origin_invoice" name="origin_invoice">
                        <input type="hidden" id="product_id" name="product_id">

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Petugas Penarik <span class="text-danger">*</span></label>
                            <select id="employee_id" name="employee_id" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-danger outline-none font-bold text-slate-700 text-sm">
                                <option value="">-- Memuat Pegawai --</option>
                            </select>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Produk Terdeteksi</div>
                                    <div id="info_product" class="font-bold text-blue-900 text-sm">Nama Kue</div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Sisa Bisa Ditarik</div>
                                    <div id="info_sisa" class="font-bold text-danger text-sm">0 Pcs</div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Jml Ditarik <span class="text-danger">*</span></label>
                                <input type="number" id="quantity" name="quantity" required min="1" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-danger outline-none font-black text-danger text-lg text-center" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Status <span class="text-danger">*</span></label>
                                <select id="reason" name="reason" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-danger outline-none font-bold text-slate-700 text-sm">
                                    <option value="Expired">Expired (Basi)</option>
                                    <option value="Rusak">Rusak / Cacat</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Catatan Detail</label>
                            <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-danger outline-none text-sm placeholder:text-slate-300" placeholder="Ketik alasan penarikan detail..."></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeModalKeluar()" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-danger hover:bg-red-700 rounded-xl transition-all shadow-sm">
                                <i class="fa-solid fa-save mr-1"></i> Simpan Pencatatan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>