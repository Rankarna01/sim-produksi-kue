<?php
$current_uri = $_SERVER['REQUEST_URI'];
function getNavClass($path, $current_uri) {
    if (strpos($current_uri, $path) !== false) return 'bg-primary/10 text-primary font-bold';
    return 'text-secondary hover:bg-slate-50 hover:text-primary font-medium';
}
?>

<aside id="main-sidebar" class="w-64 bg-surface border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">
        <h1 class="font-bold text-primary text-xl"><i class="fa-solid fa-cake-candles mr-2"></i> RotiKu</h1>
        <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-danger">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
    
    <div class="px-4 py-3 mt-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menu Admin Gudang</p>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
        <a href="<?= BASE_URL ?>admin/scan_barcode/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/admin/scan_barcode/', $current_uri) ?>">
            <i class="fa-solid fa-barcode w-5 text-center"></i> <span class="text-sm">Scan Validasi Struk</span>
        </a>
    
        <a href="<?= BASE_URL ?>admin/riwayat_validasi/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/admin/riwayat_validasi/', $current_uri) ?>">
            <i class="fa-solid fa-list-check w-5 text-center"></i> <span class="text-sm">Riwayat Validasi</span>
        </a>
    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity"></div>