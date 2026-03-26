<?php
// Deteksi URL saat ini untuk state Active NavLink
$current_uri = $_SERVER['REQUEST_URI'];

// Fungsi kecil untuk menentukan class aktif
function getNavClass($path, $current_uri) {
    if (strpos($current_uri, $path) !== false) {
        return 'bg-primary/10 text-primary font-bold'; // Style Aktif
    }
    return 'text-secondary hover:bg-slate-50 hover:text-primary font-medium'; // Style Inaktif
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
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menu Owner</p>
    </div>

    <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
        <a href="/sim-produksi-kue/owner/dashboard/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center"></i> <span class="text-sm">Dashboard</span>
        </a>
        
        <a href="/sim-produksi-kue/owner/master_gudang/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_gudang/', $current_uri) ?>">
            <i class="fa-solid fa-warehouse w-5 text-center"></i> <span class="text-sm">Data Gudang</span>
        </a>

        <a href="/sim-produksi-kue/owner/master_produk/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_produk/', $current_uri) ?>">
            <i class="fa-solid fa-box w-5 text-center"></i> <span class="text-sm">Data Produk</span>
        </a>

        <a href="/sim-produksi-kue/owner/master_bahan/" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/master_bahan/', $current_uri) ?>">
            <i class="fa-solid fa-wheat-awn w-5 text-center"></i> <span class="text-sm">Bahan Baku</span>
        </a>

        <div class="px-2 py-3 mt-2 border-t border-slate-100">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Laporan</p>
            <a href="/sim-produksi-kue/owner/laporan/" class="flex items-center gap-3 px-2 py-2.5 rounded-xl transition-colors <?= getNavClass('/owner/laporan/', $current_uri) ?>">
                <i class="fa-solid fa-file-invoice w-5 text-center"></i> <span class="text-sm">Laporan Produksi</span>
            </a>
        </div>
    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity"></div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    // Toggle class untuk geser masuk/keluar
    sidebar.classList.toggle('-translate-x-full');
    
    // Toggle overlay
    if (sidebar.classList.contains('-translate-x-full')) {
        overlay.classList.add('hidden');
    } else {
        overlay.classList.remove('hidden');
    }
}
</script>