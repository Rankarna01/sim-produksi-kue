<?php
require_once '../../config/auth.php';
// Kunci akses halaman menggunakan permission persetujuan_owner
checkPermission('persetujuan_owner');
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Persetujuan Owner</h2>
                    <p class="text-sm text-secondary mt-1">Tinjau dan setujui pengajuan perubahan resep (BOM) dari tim produksi.</p>
                </div>
                <button onclick="loadData()" class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate-right"></i> Refresh
                </button>
            </div>

            <div class="bg-surface rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-xs text-slate-400 uppercase tracking-widest font-black">
                                <th class="p-5 text-center w-16">No</th>
                                <th class="p-5">Tanggal & No. Pengajuan</th>
                                <th class="p-5">Produk & Diajukan Oleh</th>
                                <th class="p-5">Catatan Perubahan</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="6" class="p-10 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-detail')"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-3xl shadow-2xl z-10 flex flex-col overflow-hidden max-h-[95vh] sm:max-h-[90vh]">
            
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl shrink-0">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-2" id="modal-title">
                    <i class="fa-solid fa-clipboard-list text-blue-600"></i> Detail Pengajuan Resep
                </h3>
                <button onclick="closeModal('modal-detail')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 bg-blue-50/50 border border-blue-100 p-5 rounded-2xl">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Produk Jadi</p>
                        <p class="font-black text-blue-700 text-lg uppercase" id="det_product">-</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Alasan Perubahan</p>
                        <p class="font-bold text-slate-700 italic" id="det_notes">-</p>
                    </div>
                </div>

                <h4 class="text-sm font-black text-slate-800 border-b border-slate-200 pb-2 mb-4">Komposisi Resep Baru (Racikan)</h4>
                
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-4 text-center w-12">No</th>
                                <th class="p-4">Nama Bahan Baku</th>
                                <th class="p-4 text-right">Takaran (Pcs)</th>
                            </tr>
                        </thead>
                        <tbody id="table-detail-bahan" class="divide-y divide-slate-50">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="modal-footer-aksi" class="p-5 border-t border-slate-100 bg-white shrink-0 flex justify-end gap-3 rounded-b-3xl">
                </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>