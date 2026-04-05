<?php
// Deteksi URL saat ini untuk state Active NavLink
$current_uri = $_SERVER['REQUEST_URI'];

function getNavClass($path, $current_uri) {
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

    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 shrink-0">
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

    <div class="px-5 py-4 shrink-0">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest sidebar-text transition-all duration-300 opacity-100 whitespace-nowrap overflow-hidden">Panel Admin Gudang</p>
        <div class="hidden divider-dot w-full h-[2px] bg-slate-100 rounded-full mt-2"></div>
    </div>

    <nav class="flex-1 px-3 space-y-1.5 overflow-y-auto custom-scrollbar pb-6 overflow-x-hidden">
        
        <a href="<?= BASE_URL ?>admin/scan_barcode/" title="Scan Barcode (Validasi Masuk)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/admin/scan_barcode/', $current_uri) ?>">
            <i class="fa-solid fa-barcode w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Scan Barcode</span>
        </a>

        
        <a href="<?= BASE_URL ?>admin/riwayat_validasi/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/admin/riwayat_validasi/', $current_uri) ?>">
            <i class="fa-solid fa-list-check w-5 text-center"></i> <span class="text-sm">Riwayat Validasi</span>
        </a>

        <a href="<?= BASE_URL ?>admin/antrean_validasi/" title="Antrean Validasi (Pending)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-colors <?= getNavClass('/admin/antrean_validasi/', $current_uri) ?>">
            <i class="fa-solid fa-hourglass-half w-6 text-center text-lg shrink-0 text-amber-500"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100 font-bold <?= strpos($current_uri, '/admin/antrean_validasi/') !== false ? 'text-primary' : 'text-amber-600' ?>">Antrean Validasi</span>
        </a>

        <div class="px-2 py-2 mt-4 border-t border-slate-100"></div>

        <a href="<?= BASE_URL ?>logout.php" title="Keluar Sistem" onclick="return confirm('Yakin ingin keluar?')" class="md:hidden flex items-center gap-3 px-3 py-3 rounded-xl transition-colors text-danger hover:bg-red-50 hover:text-red-700 font-medium">
            <i class="fa-solid fa-right-from-bracket w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Logout</span>
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