<header class="h-16 bg-surface border-b border-slate-200 flex items-center justify-between px-6 shadow-sm z-10">
  <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-primary">
    <i class="fa-solid fa-bars text-xl"></i>
</button>
    
    <div class="flex items-center gap-4 ml-auto">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['name'] ?? 'Owner Name'); ?></p>
            <p class="text-xs text-secondary capitalize"><?= htmlspecialchars($_SESSION['role'] ?? 'owner'); ?></p>
        </div>
        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold cursor-pointer hover:bg-primary/20 transition-colors">
            <i class="fa-regular fa-user"></i>
        </div>
        <a href="/sim-produksi-kue/logout.php" class="text-danger hover:text-red-700 ml-2" title="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</header>