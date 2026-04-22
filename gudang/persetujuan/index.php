<?php
require_once '../../config/auth.php';
checkPermission('persetujuan');

// ==========================================
// AUTO-DETECT DEFAULT TAB BERDASARKAN IZIN
// ==========================================
$defaultTab = 'po'; // Default fallback
if (hasPermission('persetujuan_po')) { $defaultTab = 'po'; }
elseif (hasPermission('persetujuan_pr')) { $defaultTab = 'permintaan'; }
elseif (hasPermission('persetujuan_masuk_manual')) { $defaultTab = 'manual'; }
elseif (hasPermission('persetujuan_keluar_manual')) { $defaultTab = 'keluar'; }
elseif (hasPermission('persetujuan_izin_cetak')) { $defaultTab = 'izin'; }
elseif (hasPermission('persetujuan_histori')) { $defaultTab = 'histori'; }
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ tab: '<?= $defaultTab ?>' }">
            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Menu Persetujuan</h2>
                <p class="text-sm text-slate-500 mt-1">Kelola permintaan pembelian (PR), PO supplier, barang masuk/keluar, dan izin cetak.</p>
            </div>

            <div class="flex flex-wrap gap-2 mb-6 bg-white p-2 rounded-2xl shadow-sm border border-slate-200 w-max overflow-x-auto custom-scrollbar">
                
                <?php if (hasPermission('persetujuan_po')): ?>
                <button @click="tab = 'po'; loadPOApproval();" :class="tab === 'po' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-cart-shopping mr-2"></i> Purchase Orders
                </button>
                <?php endif; ?>

                <?php if (hasPermission('persetujuan_pr')): ?>
                <button @click="tab = 'permintaan'; loadPermintaan(1);" :class="tab === 'permintaan' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-clipboard-list mr-2"></i> Permintaan Barang (PR)
                </button>
                <?php endif; ?>

                <?php if (hasPermission('persetujuan_masuk_manual')): ?>
                <button @click="tab = 'manual'; loadManualApproval();" :class="tab === 'manual' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-box mr-2"></i> Barang Masuk
                </button>
                <?php endif; ?>

                <?php if (hasPermission('persetujuan_keluar_manual')): ?>
                <button @click="tab = 'keluar'; loadKeluarApproval();" :class="tab === 'keluar' ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-box-open mr-2"></i> Barang Keluar
                </button>
                <?php endif; ?>

                <?php if (hasPermission('persetujuan_izin_cetak')): ?>
                <button @click="tab = 'izin'; loadIzinCetak();" :class="tab === 'izin' ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-print mr-2"></i> Izin Cetak
                </button>
                <?php endif; ?>

                <?php if (hasPermission('persetujuan_histori')): ?>
                <button @click="tab = 'histori'; loadHistori();" :class="tab === 'histori' ? 'bg-slate-800 text-white shadow-lg shadow-slate-300' : 'text-slate-500 hover:bg-slate-50'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> Histori
                </button>
                <?php endif; ?>

            </div>

            <?php if (hasPermission('persetujuan_pr')): ?>
            <div x-show="tab === 'permintaan'" x-cloak x-transition class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-slate-50/50 gap-4">
                    <div>
                        <h3 class="font-black text-slate-700 text-sm uppercase tracking-tighter">Daftar Permintaan Pembelian Barang</h3>
                        <p class="text-xs text-slate-500 mt-1">Pilih barang yang disetujui untuk dimasukkan ke draft PO Supplier.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="bukaModalBuatRequest()" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-plus mr-1"></i> Buat Request Baru
                        </button>
                        <button onclick="loadPermintaan(1)" class="text-blue-600 hover:rotate-180 transition-transform duration-500 bg-blue-50 w-8 h-8 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Waktu Request</th>
                                <th class="p-5">Barang Diminta</th>
                                <th class="p-5 text-center">Qty Minta</th>
                                <th class="p-5">Diajukan Oleh</th>
                                <th class="p-5 text-center w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-permintaan" class="text-sm divide-y divide-slate-50">
                            <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination-permintaan" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50/50"></div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('persetujuan_po')): ?>
            <div x-show="tab === 'po'" x-transition class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-slate-50/50 gap-4">
                    <div>
                        <h3 class="font-black text-slate-700 text-sm uppercase tracking-tighter">Otorisasi Purchase Orders</h3>
                        <p class="text-xs text-slate-500 mt-1">Review dan berikan persetujuan untuk dokumen PO sebelum dikirim ke supplier.</p>
                    </div>
                    <button onclick="loadPOApproval()" class="text-blue-600 hover:rotate-180 transition-transform duration-500 bg-blue-50 w-8 h-8 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div id="list-po-approval" class="space-y-4">
                        <div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('persetujuan_masuk_manual')): ?>
            <div x-show="tab === 'manual'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-box text-emerald-500"></i> Persetujuan Barang Masuk</h3>
                            <p class="text-xs font-medium text-slate-500 mt-1">Data input masuk dari staf yang menunggu validasi agar stok bertambah ke Gudang.</p>
                        </div>
                        <button onclick="loadManualApproval()" class="w-10 h-10 bg-white border border-slate-200 text-slate-500 hover:text-blue-600 rounded-xl shadow-sm flex items-center justify-center transition-colors" title="Refresh Data">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white border-b border-slate-100">
                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                    <th class="p-4 w-12 text-center">No</th>
                                    <th class="p-4">Tgl Input</th>
                                    <th class="p-4">Barang</th>
                                    <th class="p-4 text-center">Qty Masuk</th>
                                    <th class="p-4">Keterangan / Alasan</th>
                                    <th class="p-4">Penginput</th>
                                    <th class="p-4 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-manual-approval" class="divide-y divide-slate-100 font-medium text-slate-600">
                                <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-emerald-600 text-2xl"></i></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('persetujuan_keluar_manual')): ?>
            <div x-show="tab === 'keluar'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-box-open text-rose-500"></i> Persetujuan Barang Keluar</h3>
                            <p class="text-xs font-medium text-slate-500 mt-1">Otorisasi pemotongan stok untuk barang yang rusak, expired, atau alasan lainnya.</p>
                        </div>
                        <button onclick="loadKeluarApproval()" class="w-10 h-10 bg-white border border-slate-200 text-slate-500 hover:text-rose-600 rounded-xl shadow-sm flex items-center justify-center transition-colors" title="Refresh Data">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white border-b border-slate-100">
                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                    <th class="p-4 w-12 text-center">No</th>
                                    <th class="p-4">Tgl Input</th>
                                    <th class="p-4">Barang</th>
                                    <th class="p-4 text-center">Qty Keluar</th>
                                    <th class="p-4">Status & Alasan</th>
                                    <th class="p-4">Penginput</th>
                                    <th class="p-4 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-keluar-approval" class="divide-y divide-slate-100 font-medium text-slate-600">
                                <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('persetujuan_izin_cetak')): ?>
            <div x-show="tab === 'izin'" x-cloak x-transition class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-print text-amber-500"></i> Permohonan Izin Cetak Ulang</h3>
                        <p class="text-xs font-medium text-slate-500 mt-1">Buka kembali kunci akses print untuk dokumen PO atau Tanda Terima.</p>
                    </div>
                    <button onclick="loadIzinCetak()" class="w-10 h-10 bg-white border border-slate-200 text-slate-500 hover:text-amber-600 rounded-xl shadow-sm flex items-center justify-center transition-colors" title="Refresh Data">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Supplier</th>
                                <th class="p-4">Nomor Dokumen PO</th>
                                <th class="p-4">Jenis Permohonan</th>
                                <th class="p-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-izin-cetak" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-amber-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('persetujuan_histori')): ?>
            <div x-show="tab === 'histori'" x-cloak x-transition class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-slate-50/50 gap-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-book text-slate-600"></i> Master Log Histori Persetujuan</h3>
                        <p class="text-xs font-medium text-slate-500 mt-1">Lacak semua riwayat persetujuan dari berbagai modul sistem.</p>
                    </div>
                </div>
                <div class="p-4 border-b border-slate-100 bg-white flex flex-col md:flex-row gap-4 items-center">
                    <select id="histori_modul" onchange="loadHistori()" class="px-4 py-2 border border-slate-300 rounded-xl outline-none text-xs font-bold text-slate-600 bg-slate-50 w-full md:w-auto">
                        <option value="semua">-- Semua Modul --</option>
                        <option value="PR">Permintaan Barang (PR)</option>
                        <option value="PO">Purchase Order (PO)</option>
                        <option value="Masuk">Barang Masuk (Manual)</option>
                        <option value="Keluar">Barang Keluar (Manual)</option>
                    </select>
                    <select id="histori_status" onchange="loadHistori()" class="px-4 py-2 border border-slate-300 rounded-xl outline-none text-xs font-bold text-slate-600 bg-slate-50 w-full md:w-auto">
                        <option value="semua">-- Semua Status --</option>
                        <option value="approved">Disetujui / Selesai</option>
                        <option value="rejected">Ditolak / Batal</option>
                    </select>
                    <div class="relative w-full flex-1">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="histori_search" placeholder="Cari No Dokumen..." class="w-full pl-8 pr-3 py-2 border border-slate-300 rounded-xl outline-none text-xs font-bold text-slate-600 bg-slate-50" onkeyup="cariHistori()">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Tanggal Proses</th>
                                <th class="p-4">Modul</th>
                                <th class="p-4">No Referensi</th>
                                <th class="p-4">Keterangan / Detail</th>
                                <th class="p-4 text-center">Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="table-histori" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-slate-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <div id="modal-detail-po" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-detail-po')"></div>
        <div class="relative bg-surface w-full max-w-3xl rounded-3xl shadow-xl z-10 transform transition-all flex flex-col overflow-hidden max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                <div>
                    <h3 class="text-lg font-black text-slate-800" id="detail-po-no">PO #---</h3>
                    <p class="text-xs text-slate-500 mt-1 font-bold" id="detail-supplier">CV. ---</p>
                </div>
                <button onclick="closeModal('modal-detail-po')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form id="form-approve-po" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" id="approve_po_id" name="po_id">
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-slate-50/30">
                    <div class="mb-4 text-xs font-bold text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-200">
                        <i class="fa-solid fa-circle-info mr-1"></i> Anda dapat mengurangi jumlah barang yang akan di-PO sebelum klik Approve.
                    </div>
                    <table class="w-full text-left text-sm border border-slate-200 rounded-xl overflow-hidden bg-white">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-4 w-12 text-center">No</th>
                                <th class="p-4">Barang & SKU</th>
                                <th class="p-4 text-center">Harga Satuan</th>
                                <th class="p-4 text-center w-40">ACC Qty (Revisi)</th>
                            </tr>
                        </thead>
                        <tbody id="detail-po-items" class="divide-y divide-slate-100">
                            <tr><td colspan="4" class="p-4 text-center">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeModal('modal-detail-po')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all">Tutup</button>
                    <button type="button" id="btn-reject-po" class="bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-sm">Reject PO</button>
                    <button type="submit" id="btn-approve-po" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-blue-200">Approve PO</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-buat-req" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-buat-req')"></div>
        <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl z-10 transform transition-all flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-black text-slate-800 tracking-tighter">Request Pembelian</h3>
                <button onclick="closeModal('modal-buat-req')" class="text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto">
                <form id="formBuatReq" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pilih Barang</label>
                        <select id="pilar_material_id" name="material_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-bold text-slate-700 bg-slate-50">
                            <option value="">-- Memuat Bahan --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Qty (Jumlah Beli)</label>
                        <input type="number" step="any" name="qty" required class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-black text-blue-600 bg-slate-50 text-center" placeholder="Contoh: 10">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Keterangan / Alasan</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-medium text-slate-700 bg-slate-50" placeholder="Cth: Stok menipis..."></textarea>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="closeModal('modal-buat-req')" class="flex-1 py-3 text-xs font-black uppercase tracking-widest text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="flex-1 py-3 text-xs font-black uppercase tracking-widest text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-md shadow-blue-200">Kirim Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 6px;}
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        [x-cloak] { display: none !important; }
    </style>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>