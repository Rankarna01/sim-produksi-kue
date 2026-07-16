<?php
require_once '../../config/auth.php';
checkPermission('stok_opname');

// Inisialisasi status kunci (Default: Terkunci)
$is_unlocked = isset($_SESSION['opname_unlocked']) && $_SESSION['opname_unlocked'] === true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* Style khusus Input PIN agar kotak-kotak rapi */
        .pin-input {
            width: 48px; height: 58px; text-align: center; font-size: 26px; font-weight: 900;
            border: 2px solid #cbd5e1; border-radius: 16px; background: #f8fafc; transition: all 0.3s;
        }
        .pin-input:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); outline: none; }
        
        /* Custom scrollbar untuk list */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Tombol Plus Minus Qty */
        .qty-btn {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            border-radius: 8px; font-weight: bold; font-size: 16px; transition: all 0.2s;
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <?php include '../../components/header.php'; ?>
        
        <!-- Lock Screen Admin (Tetap ada sesuai permintaan user) -->
        <div id="lock-screen" class="absolute inset-0 z-[60] bg-slate-900/50 backdrop-blur-md flex items-center justify-center p-4 <?= $is_unlocked ? 'hidden' : '' ?>">
            <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl p-10 text-center border border-slate-100">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-lock text-3xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Butuh Persetujuan Admin</h3>
                <p class="text-sm text-slate-500 mb-8 leading-relaxed">Masukkan Kode Otorisasi dari Administrator untuk melanjutkan penyesuaian stok.</p>
                
                <div class="flex justify-center gap-2 mb-8" id="pin-container">
                    <input type="text" maxlength="1" class="pin-input" autofocus>
                    <input type="text" maxlength="1" class="pin-input">
                    <input type="text" maxlength="1" class="pin-input">
                    <input type="text" maxlength="1" class="pin-input">
                    <input type="text" maxlength="1" class="pin-input">
                    <input type="text" maxlength="1" class="pin-input">
                </div>

                <button onclick="verifyPin()" id="btn-unlock" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl transition-all shadow-lg flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-solid fa-unlock-keyhole"></i> Buka Akses
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-8 w-full">
            
            <!-- Page Header -->
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Detail Stok Opname</h2>
                    <p class="text-sm text-slate-500 mt-1">Buat dokumen penyesuaian stok fisik dapur dengan sistem secara real-time.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('formOpname').requestSubmit();" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-100 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Posting Opname
                    </button>
                </div>
            </div>

            <!-- Form Opname Utama (Gambar 3 Layout) -->
            <form id="formOpname" class="space-y-6">
                <!-- Info Header Dokumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <!-- Kolom Tanggal -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Opname</span>
                            <input type="date" id="opname_date" name="opname_date" value="<?= date('Y-m-d') ?>" class="mt-1 bg-transparent border-0 font-bold text-slate-800 outline-none text-sm p-0 focus:ring-0 cursor-pointer">
                        </div>
                        <button type="button" onclick="document.getElementById('opname_date').focus();" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square"></i> Ubah
                        </button>
                    </div>

                    <!-- Kolom Catatan -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                        <div class="flex-1 mr-4">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Catatan / Keterangan</span>
                            <input type="text" id="reason" name="reason" value="Audit Stok Harian" class="mt-1 bg-transparent border-0 font-bold text-slate-800 outline-none text-sm p-0 w-full focus:ring-0" placeholder="Ketik catatan di sini...">
                        </div>
                        <button type="button" onclick="document.getElementById('reason').focus();" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold flex items-center gap-1">
                            <i class="fa-solid fa-pen-to-square"></i> Ubah
                        </button>
                    </div>
                </div>

                <!-- Panel Daftar Bahan Baku (Gambar 3 Panel) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="p-5 bg-slate-50/50 border-b border-slate-200 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
                            <h3 class="font-bold text-slate-800 text-sm">Bahan Baku Stok Opname (<span id="item_count">0</span>)</h3>
                        </div>
                        <button type="button" onclick="bukaModalTambah()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-100 flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i> Tambah Bahan
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] text-slate-400 uppercase tracking-widest font-black">
                                    <th class="p-4 text-center w-16">No</th>
                                    <th class="p-4">Nama Bahan Baku</th>
                                    <th class="p-4 text-center w-32">Waktu Input</th>
                                    <th class="p-4 text-right w-36">Qty Aktual</th>
                                    <th class="p-4 text-right w-36">Qty System</th>
                                    <th class="p-4 text-center w-36">Qty Selisih</th>
                                    <th class="p-4 text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="opname_list" class="text-sm divide-y divide-slate-100">
                                <tr id="empty_state">
                                    <td colspan="7" class="p-12 text-center text-slate-400 italic">
                                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                            <i class="fa-solid fa-box-open text-2xl"></i>
                                        </div>
                                        Belum ada bahan baku diopname.<br>Klik tombol <strong class="text-indigo-600 cursor-pointer" onclick="bukaModalTambah()">+ Tambah Bahan</strong> untuk memasukkan data.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            <!-- Riwayat Opname Terlipat di Bagian Bawah -->
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <button type="button" onclick="toggleRiwayat()" class="w-full p-5 bg-slate-50 border-b border-slate-200 flex justify-between items-center text-left hover:bg-slate-100/55 transition-colors">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-slate-500"></i>
                        <h3 class="font-bold text-slate-700 text-sm">Riwayat Penyesuaian Stok Terakhir</h3>
                    </div>
                    <span id="riwayat-chevron" class="text-slate-400 transition-transform duration-300"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div id="riwayat-panel" class="hidden transition-all duration-300">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50/30 border-b border-slate-200 text-[10px] text-slate-400 uppercase tracking-widest font-black">
                                    <th class="p-4 text-center w-12">No</th>
                                    <th class="p-4">Waktu Opname</th>
                                    <th class="p-4">No. Dokumen</th>
                                    <th class="p-4">Bahan Baku</th>
                                    <th class="p-4 text-right">Stok Sistem</th>
                                    <th class="p-4 text-right text-indigo-600">Stok Fisik</th>
                                    <th class="p-4 text-center">Selisih</th>
                                    <th class="p-4">Catatan</th>
                                    <th class="p-4 text-center">Petugas</th>
                                </tr>
                            </thead>
                            <tbody id="table-body" class="text-xs divide-y divide-slate-100">
                                <tr><td colspan="9" class="p-8 text-center text-slate-400 italic">Memuat riwayat opname...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH BAHAN BAKU (Gambar 1 & Gambar 2 Layout) -->
    <div id="modal-tambah-bahan" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="tutupModalTambah()"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-[32px] shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh] overflow-hidden border border-slate-100">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-base font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-basket-shopping text-indigo-600"></i> Tambah Bahan Baku
                </h3>
                <div class="flex items-center gap-2">
                    <button onclick="tutupModalTambah()" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all">Batal</button>
                    <button onclick="tambahkanBahanTerpilih()" id="btn-submit-tambah" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-100 flex items-center gap-1.5">
                        Tambah (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
            
            <!-- Modal Filters (Gambar 1 Filters) -->
            <div class="p-6 bg-slate-50/30 border-b border-slate-100 flex flex-col md:flex-row gap-4 items-center shrink-0">
                <!-- Input Jam -->
                <div class="w-full md:w-36">
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fa-solid fa-clock"></i></span>
                        <input type="text" id="filter-jam" class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 bg-white focus:outline-none focus:border-indigo-500" placeholder="20.15.00">
                    </div>
                </div>

                <!-- Dropdown Gudang/Kategori -->
                <div class="w-full md:w-48">
                    <select id="filter-gudang" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 bg-white focus:outline-none focus:border-indigo-500 cursor-pointer" onchange="filterBahan()">
                        <option value="">Semua Gudang</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="w-full md:flex-1 relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="search-modal" class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-xs font-medium bg-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500" placeholder="Cari Bahan Baku / SKU / Kode..." oninput="filterBahan()">
                </div>
            </div>

            <!-- Modal Info Banner -->
            <div class="px-6 py-3 bg-indigo-50/80 border-b border-indigo-100 flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-circle-info text-indigo-500 text-xs"></i>
                <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Maksimal 500 bahan baku terpilih tiap penambahan</span>
            </div>

            <!-- Modal Body Table -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] text-slate-400 uppercase tracking-widest font-black">
                                <th class="p-3 text-center w-12">
                                    <input type="checkbox" id="check-all-modal" onchange="toggleSelectAllModal(this)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </th>
                                <th class="p-3">Bahan Baku</th>
                                <th class="p-3 text-center w-36">Kode / SKU</th>
                                <th class="p-3 w-40">Gudang</th>
                                <th class="p-3 text-right w-28">Stok Sistem</th>
                                <th class="p-3 text-center w-48">Stok Aktual</th>
                            </tr>
                        </thead>
                        <tbody id="modal-materials-tbody" class="text-xs divide-y divide-slate-100">
                            <!-- JS Rendered -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer Paginasi -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex justify-between items-center shrink-0">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Total: <span id="modal-total-records">0</span> Bahan
                </div>
                <div id="modal-pagination" class="flex gap-1">
                    <!-- JS Rendered -->
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>