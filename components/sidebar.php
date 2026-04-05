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

<aside id="main-sidebar" class="w-64 shrink-0 bg-surface border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300 flex">

    <button onclick="toggleDesktopSidebar()" class="hidden md:flex absolute -right-3 top-8 bg-white border border-slate-200 shadow-md text-secondary hover:text-primary rounded-full w-7 h-7 items-center justify-center z-50 transition-transform duration-300 group" id="desktop-toggler" title="Perkecil Sidebar">
        <i class="fa-solid fa-chevron-left text-[11px] group-hover:scale-110 transition-transform"></i>
    </button>

    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200">
        <div class="flex items-center gap-3 overflow-hidden whitespace-nowrap">
            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <i class="fa-solid fa-cake-candles text-lg"></i>
            </div>
            <h1 class="font-black text-slate-800 text-lg tracking-tight sidebar-text transition-all duration-300 opacity-100">RotiKu ERP</h1>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-danger p-2 rounded-lg bg-slate-50 hover:bg-red-50 transition-colors shrink-0">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <div class="px-5 py-4">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap overflow-hidden">Menu Owner</p>
        <div class="hidden divider-dot w-full h-[2px] bg-slate-100 rounded-full mt-2"></div>
    </div>

    <nav class="flex-1 px-3 space-y-1.5 overflow-y-auto custom-scrollbar pb-6 overflow-x-hidden">
        
        <a href="<?= BASE_URL ?>owner/dashboard/" title="Dashboard" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_gudang/" title="Data Gudang" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_gudang/', $current_uri) ?>">
            <i class="fa-solid fa-warehouse w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Data Gudang</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_produk/" title="Data Produk" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_produk/', $current_uri) ?>">
            <i class="fa-solid fa-box w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Data Produk</span>
        </a>
        
        <a href="<?= BASE_URL ?>owner/master_kategori/" title="Kategori Produk" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_kategori/', $current_uri) ?>">
            <i class="fa-solid fa-tags w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Kategori Produk</span>
        </a>

        <a href="<?= BASE_URL ?>owner/master_bahan/" title="Bahan Baku" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_bahan/', $current_uri) ?>">
            <i class="fa-solid fa-wheat-awn w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Bahan Baku</span>
        </a>

        <a href="<?= BASE_URL ?>owner/stok_opname/" title="Stok Opname Bahan" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/stok_opname/', $current_uri) ?>">
            <i class="fa-solid fa-scale-balanced w-6 text-center text-lg shrink-0 text-emerald-600"></i> 
            <span class="text-sm font-bold sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 text-emerald-700">Stok Opname</span>
        </a>
        
        <a href="<?= BASE_URL ?>owner/master_satuan/" title="Master Satuan" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_satuan/', $current_uri) ?>">
            <i class="fa-solid fa-weight-scale w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Master Satuan</span>
        </a>
        
        <a href="<?= BASE_URL ?>owner/master_resep/" title="Resep (BOM)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_resep/', $current_uri) ?>">
            <i class="fa-solid fa-list-check w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Resep (BOM)</span>
        </a>
        
        <a href="<?= BASE_URL ?>owner/master_user/" title="Manajemen User" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/master_user/', $current_uri) ?>">
            <i class="fa-solid fa-users-gear w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Manajemen User</span>
        </a>

        <div class="px-2 py-2 mt-4 border-t border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 pl-2 sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap overflow-hidden">Laporan & Analitik</p>
            <div class="hidden divider-dot w-full h-[2px] bg-slate-100 rounded-full mt-2"></div>
        </div>
       
        <a href="<?= BASE_URL ?>owner/laporan_produksi/" title="Laporan Produksi" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_produksi/', $current_uri) ?>">
            <i class="fa-solid fa-chart-line w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Laporan Produksi</span>
        </a>

        <a href="<?= BASE_URL ?>owner/laporan_keluar/" title="Laporan Produk Keluar" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_keluar/', $current_uri) ?>">
            <i class="fa-solid fa-box-open w-6 text-center text-lg shrink-0 text-danger"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Lap. Produk Keluar</span>
        </a>

        <a href="<?= BASE_URL ?>owner/audit_logs/" title="Audit Logs (Lacak)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/audit_logs/', $current_uri) ?>">
            <i class="fa-solid fa-shoe-prints w-6 text-center text-lg shrink-0 text-indigo-500"></i> 
            <span class="text-sm font-bold sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 text-indigo-600">Audit Logs (Lacak)</span>
        </a>

        <a href="<?= BASE_URL ?>owner/analisa_produk/" title="Analisa Performa" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/analisa_produk/', $current_uri) ?>">
            <i class="fa-solid fa-chart-simple w-6 text-center text-lg shrink-0 text-amber-500"></i> 
            <span class="text-sm font-bold sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 text-amber-600">Analisa Performa</span>
        </a>

        <a href="<?= BASE_URL ?>owner/laporan_bahan/" title="Laporan Bahan Baku" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_bahan/', $current_uri) ?>">
            <i class="fa-solid fa-wheat-awn-circle-exclamation w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Laporan Bahan Baku</span>
        </a>

        <a href="<?= BASE_URL ?>owner/laporan_produk/" title="Laporan Produk Jadi" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_produk/', $current_uri) ?>">
            <i class="fa-solid fa-boxes-stacked w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Laporan Produk Jadi</span>
        </a>
        
        <a href="<?= BASE_URL ?>owner/laporan_bom/" title="Laporan Resep (BOM)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_bom/', $current_uri) ?>">
            <i class="fa-solid fa-clipboard-list w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Laporan Resep (BOM)</span>
        </a>

         <a href="<?= BASE_URL ?>owner/laporan_opname/" title="Laporan Opname" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/owner/laporan_opname/', $current_uri) ?>">
            <i class="fa-solid fa-clipboard-check w-6 text-center text-lg shrink-0 text-emerald-500"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 text-emerald-600">Laporan Opname</span>
        </a>

    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 duration-300"></div>

<style>
    /* Styling Scrollbar Khusus untuk Sidebar agar elegan */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #cbd5e1; }
</style>

<script>
    // State Tracker untuk Desktop
    let isDesktopCollapsed = false;

    // FUNGSI 1: BUKA/TUTUP DI MOBILE
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

    // FUNGSI 2: PERKECIL/PERBESAR DI DESKTOP
    function toggleDesktopSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const texts = document.querySelectorAll('.sidebar-text');
        const dots = document.querySelectorAll('.divider-dot');
        const togglerBtn = document.getElementById('desktop-toggler');
        const togglerIcon = togglerBtn.querySelector('i');

        isDesktopCollapsed = !isDesktopCollapsed;

        if (isDesktopCollapsed) {
            // Ubah Lebar Sidebar
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-[85px]'); // Sekitar ukuran icon + padding
            
            // Sembunyikan Teks
            texts.forEach(el => {
                el.classList.remove('opacity-100');
                el.classList.add('opacity-0', 'w-0', 'invisible');
            });
            
            // Munculkan titik pembatas
            dots.forEach(el => el.classList.remove('hidden'));

            // Putar icon toggler
            togglerIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            togglerBtn.setAttribute('title', 'Perbesar Sidebar');

        } else {
            // Kembalikan Lebar Sidebar
            sidebar.classList.remove('w-[85px]');
            sidebar.classList.add('w-64');
            
            // Munculkan Teks Kembali
            texts.forEach(el => {
                el.classList.remove('opacity-0', 'w-0', 'invisible');
                el.classList.add('opacity-100');
            });

            // Sembunyikan titik pembatas
            dots.forEach(el => el.classList.add('hidden'));

            // Putar icon toggler
            togglerIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            togglerBtn.setAttribute('title', 'Perkecil Sidebar');
        }
    }
</script>