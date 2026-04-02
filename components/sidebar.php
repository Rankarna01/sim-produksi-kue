<?php
// Deteksi URL saat ini untuk state Active NavLink
$current_uri = $_SERVER['REQUEST_URI'];

function getNavClass($path, $current_uri) {
    // Pengecekan dibuat lebih dinamis agar kebal terhadap perubahan nama root folder
    if (strpos($current_uri, $path) !== false) {
        return 'bg-primary/10 text-primary font-bold'; 
    }
    return 'text-secondary hover:bg-slate-50 hover:text-primary font-medium'; 
}
?>

<aside id="main-sidebar" class="w-64 bg-surface border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">
        <h1 class="font-bold text-primary text-xl"><i class="fa-solid fa-cake-candles mr-2"></i> Stok Roti</h1>
        <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-danger p-2 rounded-lg bg-slate-50 hover:bg-red-50 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <div class="px-4 py-3 mt-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menu Owner</p>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto pb-6">
        <a href="<?= BASE_URL ?>owner/dashboard/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center"></i> <span class="text-sm">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_gudang/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_gudang/', $current_uri) ?>">
            <i class="fa-solid fa-warehouse w-5 text-center"></i> <span class="text-sm">Data Gudang</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_produk/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_produk/', $current_uri) ?>">
            <i class="fa-solid fa-box w-5 text-center"></i> <span class="text-sm">Data Produk</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_bahan/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_bahan/', $current_uri) ?>">
            <i class="fa-solid fa-wheat-awn w-5 text-center"></i> <span class="text-sm">Bahan Baku</span>
        </a>
        <a href="<?= BASE_URL ?>owner/master_satuan/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_satuan/', $current_uri) ?>">
            <i class="fa-solid fa-weight-scale w-5 text-center"></i> <span class="text-sm">Master Satuan</span>
        </a>
        <a href="<?= BASE_URL ?>owner/master_resep/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_resep/', $current_uri) ?>">
            <i class="fa-solid fa-list-check w-5 text-center"></i> <span class="text-sm">Resep (BOM)</span>
        </a>
        <a href="<?= BASE_URL ?>owner/master_user/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_user/', $current_uri) ?>">
            <i class="fa-solid fa-users-gear w-5 text-center"></i> <span class="text-sm">Manajemen User</span>
        </a>

        <div class="px-2 py-3 mt-4 border-t border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 pl-2">Laporan & Analitik</p>

            <a href="<?= BASE_URL ?>owner/laporan_produksi/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan_produksi/', $current_uri) ?>">
                <i class="fa-solid fa-chart-line w-5 text-center"></i> <span class="text-sm">Laporan Produksi</span>
            </a>

            <a href="<?= BASE_URL ?>owner/laporan_keluar/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan_keluar/', $current_uri) ?>">
                <i class="fa-solid fa-box-open w-5 text-center text-danger"></i> <span class="text-sm">Lap. Produk Keluar</span>
            </a>

            <a href="<?= BASE_URL ?>owner/audit_logs/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/audit_logs/', $current_uri) ?>">
            <i class="fa-solid fa-shoe-prints w-5 text-center text-indigo-500"></i> <span class="text-sm font-bold text-indigo-600">Audit Logs (Lacak)</span>
        </a>

            <a href="<?= BASE_URL ?>owner/laporan_bahan/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan_bahan/', $current_uri) ?>">
                <i class="fa-solid fa-wheat-awn-circle-exclamation w-5 text-center"></i> <span class="text-sm">Laporan Bahan Baku</span>
            </a>

            <a href="<?= BASE_URL ?>owner/laporan_produk/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan_produk/', $current_uri) ?>">
                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i> <span class="text-sm">Laporan Produk Jadi</span>
            </a>
            <a href="<?= BASE_URL ?>owner/laporan_bom/" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan_bom/', $current_uri) ?>">
                <i class="fa-solid fa-clipboard-list w-5 text-center"></i> <span class="text-sm">Laporan Resep (BOM)</span>
            </a>
        </div>
    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 duration-300"></div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        sidebar.classList.toggle('-translate-x-full');

        if (sidebar.classList.contains('-translate-x-full')) {
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        } else {
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
            }, 10);
        }
    }

    function closeSidebarMobile() {
        if (window.innerWidth < 768) {
            toggleSidebar();
        }
    }
</script>