<?php
$current_uri = $_SERVER['REQUEST_URI'];
function getNavClass($path, $current_uri) {
    if (strpos($current_uri, $path) !== false) return 'bg-blue-600/10 text-blue-600 font-bold';
    return 'text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium';
}

// DEFINISIKAN VISIBILITAS KATEGORI DROPDOWN
$showProduk = hasPermission('master_inventory') || hasPermission('master_kategori') || hasPermission('master_satuan') || hasPermission('master_lokasi') || hasPermission('monitoring_rak') || hasPermission('trx_barang_masuk') || hasPermission('trx_barang_keluar') || hasPermission('cetak_barcode');
$showOpname = hasPermission('data_opname') || hasPermission('otorisasi_opname') || hasPermission('scanner_opname');
$showTransaksi = hasPermission('trx_permintaan_dapur') || hasPermission('trx_permintaan_barang') || hasPermission('trx_po') || hasPermission('trx_pembayaran') || hasPermission('trx_supplier');
$showLaporan = hasPermission('lap_barang_masuk') || hasPermission('lap_barang_keluar') || hasPermission('lap_po') || hasPermission('lap_pembayaran_po') || hasPermission('lap_stok_opname') || hasPermission('lap_kartu_stok') || hasPermission('lap_stok_menipis') || hasPermission('lap_stok_terbanyak') || hasPermission('lap_perbandingan_harga') || hasPermission('lap_supplier');
$showPengaturan = hasPermission('pengaturan_karyawan') || hasPermission('pengaturan_pembayaran') || hasPermission('manage_users') || hasPermission('manage_roles') || hasPermission('pengaturan_profil');
?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<aside id="main-sidebar" class="w-64 bg-white border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">
    
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 bg-white shrink-0">
        <h1 class="font-black text-blue-600 text-xl uppercase tracking-tighter flex items-center">
            <i class="fa-solid fa-boxes-stacked mr-2"></i> Gudang Pilar
        </h1>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-red-500 p-2 rounded-lg bg-slate-50 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto custom-scrollbar pb-20">
        
        <?php if(hasPermission('dashboard')): ?>
        <a href="<?= BASE_URL ?>gudang/dashboard/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= getNavClass('/gudang/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center"></i> <span class="text-sm">Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if(hasPermission('persetujuan')): ?>
        <a href="<?= BASE_URL ?>gudang/persetujuan/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= getNavClass('/gudang/persetujuan/', $current_uri) ?>">
            <i class="fa-solid fa-file-circle-check w-5 text-center text-amber-500"></i> <span class="text-sm">Persetujuan</span>
        </a>
        <?php endif; ?>

        <div class="my-4 border-t border-slate-50"></div>

        <?php if($showProduk): ?>
        <div x-data="{ open: <?= strpos($current_uri, '/produk/') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-cube w-5 text-center text-indigo-500"></i>
                    <span class="text-sm">Produk & Master</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <?php if(hasPermission('master_inventory')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/inventory/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/inventory/', $current_uri) ?>">
                    <i class="fa-solid fa-boxes-stacked text-slate-400 w-4 text-center"></i> Inventory & Stok
                </a>
                <?php endif; ?>
                <?php if(hasPermission('master_kategori')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/kategori/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/kategori/', $current_uri) ?>">
                    <i class="fa-solid fa-tags text-slate-400 w-4 text-center"></i> Master Kategori
                </a>
                <?php endif; ?>
                <?php if(hasPermission('master_satuan')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/satuan/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/satuan/', $current_uri) ?>">
                    <i class="fa-solid fa-weight-scale text-slate-400 w-4 text-center"></i> Master Satuan
                </a>
                <?php endif; ?>
                <?php if(hasPermission('master_lokasi')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/lokasi/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/lokasi/', $current_uri) ?>">
                    <i class="fa-solid fa-location-dot text-slate-400 w-4 text-center"></i> Master Lokasi Rak
                </a>
                <?php endif; ?>
                <?php if(hasPermission('monitoring_rak')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/monitoring_rak/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/monitoring_rak/', $current_uri) ?>">
                    <i class="fa-solid fa-house text-slate-400 w-4 text-center"></i> Monitoring Rak & Stok
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_barang_masuk')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/barang_masuk/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/barang_masuk/', $current_uri) ?>">
                    <i class="fa-solid fa-arrow-right-to-bracket text-slate-400 w-4 text-center"></i> Barang Masuk
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_barang_keluar')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/barang_keluar/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/barang_keluar/', $current_uri) ?>">
                    <i class="fa-solid fa-arrow-right-from-bracket text-slate-400 w-4 text-center"></i> Barang Keluar
                </a>
                <?php endif; ?>
                <?php if(hasPermission('cetak_barcode')): ?>
                <a href="<?= BASE_URL ?>gudang/produk/cetak_barcode/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/produk/cetak_barcode/', $current_uri) ?>">
                    <i class="fa-solid fa-barcode text-slate-400 w-4 text-center"></i> Cetak Barcode
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($showOpname): ?>
        <div x-data="{ open: <?= strpos($current_uri, '/stok_opname/') !== false || strpos($current_uri, '/opname/') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-clipboard-check w-5 text-center text-purple-500"></i>
                    <span class="text-sm">Stok Opname</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <?php if(hasPermission('data_opname')): ?>
                <a href="<?= BASE_URL ?>gudang/opname/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/gudang/opname/', $current_uri) ?>">
                    <i class="fa-solid fa-list-check text-slate-400 w-4 text-center"></i> Data Opname
                </a>
                <?php endif; ?>
                <?php if(hasPermission('otorisasi_opname')): ?>
                <a href="<?= BASE_URL ?>gudang/stok_opname/otorisasi/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/stok_opname/otorisasi/', $current_uri) ?>">
                    <i class="fa-solid fa-key text-slate-400 w-4 text-center"></i> Otorisasi Akses
                </a>
                <?php endif; ?>
                <?php if(hasPermission('scanner_opname')): ?>
                <a href="<?= BASE_URL ?>gudang/stok_opname/scanner/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/stok_opname/scanner/', $current_uri) ?>">
                    <i class="fa-solid fa-barcode text-slate-400 w-4 text-center"></i> Scanner Audit
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($showTransaksi): ?>
        <div x-data="{ open: <?= strpos($current_uri, '/transaksi/') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-right-left w-5 text-center text-emerald-500"></i>
                    <span class="text-sm">Transaksi</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <?php if(hasPermission('trx_permintaan_dapur')): ?>
                <a href="<?= BASE_URL ?>gudang/transaksi/permintaan-dapur/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/permintaan-dapur/', $current_uri) ?>">
                    <i class="fa-solid fa-bell-concierge text-slate-400 w-4 text-center"></i> Permintaan Dapur
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_permintaan_barang')): ?>
                <a href="<?= BASE_URL ?>gudang/transaksi/permintaan/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/permintaan/', $current_uri) ?>">
                    <i class="fa-solid fa-hand-holding-hand text-slate-400 w-4 text-center"></i> Permintaan Barang
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_po')): ?>
                <a href="<?= BASE_URL ?>gudang/transaksi/po/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/po/', $current_uri) ?>">
                    <i class="fa-solid fa-cart-flatbed text-slate-400 w-4 text-center"></i> Purchase Order (PO)
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_pembayaran')): ?>
                <a href="<?= BASE_URL ?>gudang/transaksi/pembayaran/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/pembayaran/', $current_uri) ?>">
                    <i class="fa-solid fa-file-invoice-dollar text-slate-400 w-4 text-center"></i> Pembayaran PO
                </a>
                <?php endif; ?>
                <?php if(hasPermission('trx_supplier')): ?>
                <a href="<?= BASE_URL ?>gudang/transaksi/supplier/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/supplier/', $current_uri) ?>">
                    <i class="fa-solid fa-handshake text-slate-400 w-4 text-center"></i> Supplier & Harga
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($showLaporan): ?>
        <div x-data="{ open: <?= strpos($current_uri, '/laporan/') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-rose-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-lines w-5 text-center text-rose-500"></i>
                    <span class="text-sm">Laporan Lengkap</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <?php if(hasPermission('lap_barang_masuk')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/barang-masuk/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/barang-masuk/', $current_uri) ?>">
                    <i class="fa-solid fa-arrow-right-to-bracket text-slate-400 w-4 text-center"></i> Barang Masuk
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_barang_keluar')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/barang-keluar/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/barang-keluar/', $current_uri) ?>">
                    <i class="fa-solid fa-arrow-right-from-bracket text-slate-400 w-4 text-center"></i> Barang Keluar
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_po')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/po/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/po/', $current_uri) ?>">
                    <i class="fa-solid fa-file-contract text-slate-400 w-4 text-center"></i> Purchase Order
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_pembayaran_po')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/pembayaran-po/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/pembayaran-po/', $current_uri) ?>">
                    <i class="fa-solid fa-money-check-dollar text-slate-400 w-4 text-center"></i> Pembayaran PO
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_stok_opname')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/opname/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/opname/', $current_uri) ?>">
                    <i class="fa-solid fa-boxes-stacked text-slate-400 w-4 text-center"></i> Stok Opname
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_kartu_stok')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/kartu-stok/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/kartu-stok/', $current_uri) ?>">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400 w-4 text-center"></i> Kartu Stok
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_stok_menipis')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/stok-menipis/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/stok-menipis/', $current_uri) ?>">
                    <i class="fa-solid fa-triangle-exclamation text-slate-400 w-4 text-center"></i> Stok Menipis
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_stok_terbanyak')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/stok-terbanyak/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/stok-terbanyak/', $current_uri) ?>">
                    <i class="fa-solid fa-arrow-up-wide-short text-slate-400 w-4 text-center"></i> Stok Terbanyak
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_perbandingan_harga')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/perbandingan-harga/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/perbandingan-harga/', $current_uri) ?>">
                    <i class="fa-solid fa-scale-balanced text-slate-400 w-4 text-center"></i> Perbandingan Harga
                </a>
                <?php endif; ?>
                <?php if(hasPermission('lap_supplier')): ?>
                <a href="<?= BASE_URL ?>gudang/laporan/supplier-terakhir/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/laporan/supplier-terakhir/', $current_uri) ?>">
                    <i class="fa-solid fa-building-user text-slate-400 w-4 text-center"></i> Laporan Supplier
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if($showPengaturan): ?>
        <div x-data="{ open: <?= (strpos($current_uri, '/pengaturan/') !== false || strpos($current_uri, '/master/') !== false) ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-gears w-5 text-center text-slate-500"></i>
                    <span class="text-sm">Pengaturan</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <?php if(hasPermission('pengaturan_karyawan')): ?>
                <!-- <a href="<?= BASE_URL ?>gudang/pengaturan/karyawan/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/pengaturan/karyawan/', $current_uri) ?>">
                    <i class="fa-solid fa-user-group text-slate-400 w-4 text-center"></i> Data Karyawan
                </a> -->
                <?php endif; ?>
                <?php if(hasPermission('pengaturan_pembayaran')): ?>
                <a href="<?= BASE_URL ?>gudang/pengaturan/pembayaran/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/pengaturan/pembayaran/', $current_uri) ?>">
                    <i class="fa-solid fa-credit-card text-slate-400 w-4 text-center"></i> Master Pembayaran
                </a>
                <?php endif; ?>
                <?php if(hasPermission('manage_roles')): ?>
                <a href="<?= BASE_URL ?>gudang/pengaturan/manajemen-role/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/pengaturan/manajemen-role/', $current_uri) ?>">
                    <i class="fa-solid fa-shield-halved text-slate-400 w-4 text-center"></i> Manajemen Role
                </a>
                <?php endif; ?>
                <?php if(hasPermission('manage_users')): ?>
                <a href="<?= BASE_URL ?>gudang/pengaturan/user-management/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/pengaturan/user-management/', $current_uri) ?>">
                    <i class="fa-solid fa-user-shield text-slate-400 w-4 text-center"></i> User Management
                </a>
                <?php endif; ?>
                <?php if(hasPermission('pengaturan_profil')): ?>
                <a href="<?= BASE_URL ?>gudang/pengaturan/profil/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/pengaturan/profil/', $current_uri) ?>">
                    <i class="fa-solid fa-store text-slate-400 w-4 text-center"></i> Profil Toko
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </nav>

    <div class="p-4 border-t border-slate-200 bg-white">
        <a href="<?= BASE_URL ?>logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-rose-500 hover:bg-rose-50 transition-all font-bold">
            <i class="fa-solid fa-power-off w-5 text-center"></i> <span class="text-sm">Keluar</span>
        </a>
    </div>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 duration-300"></div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    [x-cloak] { display: none !important; }
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
}
</script>