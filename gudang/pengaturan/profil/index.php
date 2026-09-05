<?php
require_once '../../../config/auth.php';
checkPermission('pengaturan_profil'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <!-- Page Header -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-700">
                            Konfigurasi Sistem
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fa-solid fa-sliders text-blue-600"></i> Profil Toko & SOP Persetujuan
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola identitas usaha untuk cetak dokumen dan atur kebijakan saklar persetujuan operasional.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="loadProfil()" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-rotate"></i> Muat Ulang
                    </button>
                    <button type="button" onclick="document.getElementById('submitBtn').click()" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
                        <i class="fa-regular fa-floppy-disk"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>

            <!-- Main Form (Full Width Container) -->
            <form id="formProfil" class="w-full space-y-6" enctype="multipart/form-data">
                
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
                    
                    <!-- LEFT COLUMN (Identitas Usaha & Kop Surat) -->
                    <div class="xl:col-span-7 space-y-6">
                        
                        <!-- Card 1: Informasi Toko -->
                        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                            
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-lg">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-800 text-base">Identitas & Informasi Toko</h3>
                                        <p class="text-xs text-slate-400">Data ini digunakan untuk kop surat, invoice, dan berkas transaksi resmi.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Nama Toko -->
                            <div>
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                    Nama Perusahaan / Toko <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <input type="text" id="store_name" name="store_name" required 
                                        placeholder="Contoh: ROTIKU ERP"
                                        class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 font-black text-slate-800 text-sm bg-slate-50/50 focus:bg-white transition-all uppercase tracking-wider">
                                </div>
                            </div>

                            <!-- Logo Upload Box -->
                            <div>
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                    Logo Toko (Kop Dokumen)
                                </label>
                                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <!-- Preview Area -->
                                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl border-2 border-dashed border-blue-200 bg-white flex items-center justify-center overflow-hidden relative shadow-sm shrink-0">
                                        <div id="logoPlaceholder" class="flex flex-col items-center justify-center text-slate-400 p-2 text-center">
                                            <i class="fa-regular fa-image text-2xl mb-1 text-slate-300"></i>
                                            <span class="text-[10px] font-bold">Belum Ada Logo</span>
                                        </div>
                                        <img id="logoPreview" src="" alt="Logo Toko" class="absolute inset-0 w-full h-full object-contain p-2 hidden">
                                    </div>
                                    
                                    <!-- Upload Controls -->
                                    <div class="flex-1 w-full space-y-2 text-center sm:text-left">
                                        <div class="flex flex-col sm:flex-row items-center gap-3">
                                            <label for="logo" class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black tracking-wider transition-all shadow-md shadow-blue-200">
                                                <i class="fa-solid fa-cloud-arrow-up"></i> Pilih File Logo
                                            </label>
                                            <input type="file" id="logo" name="logo" accept="image/png, image/jpeg" onchange="previewLogo(event)" class="hidden">
                                            <span id="fileNameDisplay" class="text-xs text-slate-400 italic">Belum ada file baru dipilih</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">
                                            Mendukung format <strong>PNG</strong> atau <strong>JPG</strong> (Maks. 2MB). Disarankan menggunakan logo dengan latar transparan berasio kotak/persegi.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div>
                                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                    Alamat Lengkap Operasional
                                </label>
                                <div class="relative">
                                    <div class="absolute top-3 left-4 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <textarea id="address" name="address" rows="3" 
                                        placeholder="Contoh: Jl. Gudang Utama No. 123, Medan, Sumatera Utara"
                                        class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 font-bold text-slate-700 text-sm bg-slate-50/50 focus:bg-white transition-all custom-scrollbar"></textarea>
                                </div>
                            </div>

                            <!-- Kontak (Telepon & Email) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-phone"></i>
                                        </div>
                                        <input type="text" id="phone" name="phone" 
                                            placeholder="(061) 1234567 / 0812..."
                                            class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 font-bold text-slate-700 text-sm bg-slate-50/50 focus:bg-white transition-all font-mono">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">
                                        Email Resmi Korespondensi
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-envelope"></i>
                                        </div>
                                        <input type="email" id="email" name="email" 
                                            placeholder="logistik@rotiku.com"
                                            class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-100 font-bold text-slate-700 text-sm bg-slate-50/50 focus:bg-white transition-all">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Card 2: Live Preview Kop Dokumen -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-eye text-blue-600 text-sm"></i>
                                    <h4 class="font-black text-slate-800 text-sm uppercase tracking-wider">Simulasi Tampilan Kop Surat</h4>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded">Preview Cetak</span>
                            </div>

                            <div class="p-6 rounded-2xl bg-slate-50/70 border border-slate-200/80 font-sans">
                                <div class="flex items-center gap-5">
                                    <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                                        <img id="kopPreviewImg" src="" alt="Logo" class="w-full h-full object-contain hidden">
                                        <i id="kopPreviewIcon" class="fa-solid fa-store text-2xl text-slate-300"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 id="kopPreviewName" class="font-black text-base text-slate-800 uppercase tracking-tight truncate">ROTIKU ERP</h3>
                                        <p id="kopPreviewAddress" class="text-xs text-slate-600 font-medium line-clamp-1 mt-0.5">Alamat toko akan tampil di sini</p>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-[11px] text-slate-500 font-semibold">
                                            <span id="kopPreviewPhone"><i class="fa-solid fa-phone text-[10px] mr-1 text-slate-400"></i> -</span>
                                            <span id="kopPreviewEmail"><i class="fa-solid fa-envelope text-[10px] mr-1 text-slate-400"></i> -</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 border-b-2 border-slate-800/80"></div>
                                <div class="mt-1 border-b border-slate-300"></div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (SOP Persetujuan / Switches) -->
                    <div class="xl:col-span-5 space-y-6">
                        
                        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                            
                            <div class="border-b border-slate-100 pb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-lg">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-black text-slate-800 text-base">Kebijakan Persetujuan (SOP)</h3>
                                            <p class="text-xs text-slate-400">Saklar alur kerja otorisasi manager.</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-3 leading-relaxed bg-amber-50/80 text-amber-800 border border-amber-200/60 p-3 rounded-xl">
                                    <i class="fa-solid fa-circle-info mr-1 text-amber-600"></i>
                                    <strong>Aktifkan saklar</strong> untuk mewajibkan persetujuan Manager/Owner sebelum data masuk/keluar. Matikan jika ingin transaksi langsung diproses (Auto-Approve).
                                </p>
                            </div>

                            <!-- List Switch Cards -->
                            <div class="space-y-3">
                                
                                <!-- 1. Barang Masuk Manual -->
                                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/30 transition-all flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-arrow-down-to-bracket"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                <span>Barang Masuk (Manual)</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-700">Masuk</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Wajibkan persetujuan sebelum stok barang bertambah.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="req_approval_in" name="req_approval_in" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                </div>

                                <!-- 2. Barang Keluar Manual -->
                                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-rose-300 hover:bg-rose-50/30 transition-all flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                <span>Barang Keluar (Manual)</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-100 text-rose-700">Keluar</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Wajibkan persetujuan sebelum bahan baku dikeluarkan.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="req_approval_out" name="req_approval_out" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                    </label>
                                </div>

                                <!-- 3. Permintaan Barang (PR) -->
                                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 transition-all flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-clipboard-list"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                <span>Permintaan Barang (PR)</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-blue-100 text-blue-700">PR Toko</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">PR wajib disetujui owner sebelum masuk draft PO.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="req_approval_pr" name="req_approval_pr" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <!-- 4. Purchase Order (PO) -->
                                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/30 transition-all flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                <span>Purchase Order (PO)</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-indigo-100 text-indigo-700">PO Vendor</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">PO wajib di-acc sebelum sah dipesan ke supplier.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="req_approval_po" name="req_approval_po" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <!-- 5. Izin Cetak Ulang Dokumen -->
                                <div class="p-4 rounded-2xl border border-slate-200/80 hover:border-amber-300 hover:bg-amber-50/30 transition-all flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-print"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                <span>Izin Cetak Ulang Dokumen</span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-100 text-amber-700">Keamanan</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Kunci cetak otomatis (1x) & butuh izin untuk print ulang.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" id="req_approval_print" name="req_approval_print" class="sr-only peer">
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                    </label>
                                </div>

                            </div>

                        </div>

                        <!-- Info Card -->
                        <div class="p-5 rounded-3xl bg-blue-50/50 border border-blue-100 flex items-start gap-3 text-xs text-blue-900 leading-relaxed">
                            <i class="fa-solid fa-circle-check text-blue-600 text-base mt-0.5"></i>
                            <div>
                                <strong class="font-bold">Keamanan & Audit Jejak:</strong>
                                <p class="text-blue-800/80 mt-0.5">Seluruh perubahan profil dan status SOP persetujuan akan langsung tercatat pada sistem log audit gudang.</p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Bottom Sticky Save Bar -->
                <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
                        <span>Pastikan data sudah benar sebelum menekan tombol simpan.</span>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button type="button" onclick="loadProfil()" class="flex-1 sm:flex-none px-6 py-3 border border-slate-200 hover:bg-slate-50 rounded-2xl text-xs font-bold text-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit" id="submitBtn" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                            <i class="fa-regular fa-floppy-disk text-sm"></i> Simpan Profil & Pengaturan
                        </button>
                    </div>
                </div>

            </form>

        </main>
    </div>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>