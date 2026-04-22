<?php
require_once '../../config/auth.php';
require_once '../../config/database.php'; 
checkPermission('master_bahan');

// 1. CEK SESSION KITCHEN ID
$user_kitchen_id = $_SESSION['kitchen_id'] ?? null;
$is_owner = is_null($user_kitchen_id);

// 2. AMBIL DATA DAPUR
if ($is_owner) {
    $stmtKitchens = $pdo->query("SELECT * FROM kitchens ORDER BY id ASC");
    $kitchens = $stmtKitchens->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmtKitchens = $pdo->prepare("SELECT * FROM kitchens WHERE id = ?");
    $stmtKitchens->execute([$user_kitchen_id]);
    $kitchens = $stmtKitchens->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8" x-data="{ tabStatus: 'semua' }">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Bahan Baku Dapur</h2>
                    <p class="text-sm text-secondary mt-1">Kelola stok dapur dan ajukan permintaan bahan ke Gudang Pilar.</p>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    
                    <select id="filter-dapur" onchange="loadSemuaData()" 
                            class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-primary outline-none <?= !$is_owner ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-dashed' : 'bg-surface' ?>" 
                            <?= !$is_owner ? 'disabled title="Akses Terkunci pada Dapur Anda"' : '' ?>>
                        
                        <?php if (count($kitchens) > 0): ?>
                            <?php foreach ($kitchens as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($user_kitchen_id == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['name']) ?> <?= !$is_owner ? '(Terkunci)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Belum Ada Dapur Dibuat</option>
                        <?php endif; ?>
                    </select>

                    <?php if (hasPermission('edit_master_bahan')): ?>
                    <button onclick="openModalRequest();" class="flex-1 sm:flex-none bg-primary hover:opacity-90 text-surface px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Permintaan
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-700 text-sm"><i class="fa-solid fa-boxes-stacked text-primary mr-2"></i> Stok Aktual Dapur</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold text-center w-16">No</th>
                                <th class="p-4 font-semibold">Kode</th>
                                <th class="p-4 font-semibold">Nama Bahan</th>
                                <th class="p-4 font-semibold text-center">Satuan</th>
                                <th class="p-4 font-semibold text-right">Stok Aktual</th>
                                <th class="p-4 font-semibold text-right">Min. Stok</th>
                                <th class="p-4 font-semibold text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="7" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Riwayat Pengajuan ke Pilar</h3>
                <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                    <button @click="tabStatus = 'semua'; loadRequests('semua', 1)" :class="tabStatus === 'semua' ? 'bg-primary text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Semua</button>
                    <button @click="tabStatus = 'menunggu'; loadRequests('menunggu', 1)" :class="tabStatus === 'menunggu' ? 'bg-amber-500 text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Menunggu</button>
                    <button @click="tabStatus = 'berhasil'; loadRequests('berhasil', 1)" :class="tabStatus === 'berhasil' ? 'bg-emerald-500 text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Berhasil (Approved)</button>
                    <button @click="tabStatus = 'ditolak'; loadRequests('ditolak', 1)" :class="tabStatus === 'ditolak' ? 'bg-danger text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Ditolak</button>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-background border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-semibold text-center w-16">No</th>
                                    <th class="p-4 font-semibold">Waktu Request</th>
                                    <th class="p-4 font-semibold">Nama Bahan (Pilar)</th>
                                    <th class="p-4 font-semibold text-center">Qty Diminta</th>
                                    <th class="p-4 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="table-requests" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="5" class="p-8 text-center text-secondary">Memuat riwayat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="pagination-requests" class="mt-4 flex items-center justify-center gap-2"></div>
            </div>

        </main>
    </div>

    <div id="modal-bahan" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-bahan')"></div>
        <div class="bg-surface w-full max-w-md rounded-2xl shadow-xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 id="modal-title" class="text-lg font-bold text-slate-800">Edit Bahan Baku</h3>
                <button onclick="closeModal('modal-bahan')" class="text-secondary hover:text-danger transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 overflow-y-auto">
                <form id="formBahan" class="space-y-4">
                    <input type="hidden" id="material_id" name="id">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Bahan</label>
                        <input type="text" id="code" name="code" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-slate-50 uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Bahan Baku</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-slate-50">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                            <input type="text" id="unit" name="unit" readonly class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-100 text-slate-500 font-bold outline-none cursor-not-allowed" title="Satuan mengikuti master Gudang Pilar" placeholder="Unit">
                            <p class="text-[9px] text-slate-400 mt-1 leading-tight">*Terkunci ke Gudang</p>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stok</label>
                            <input type="number" step="0.01" id="stock" name="stock" value="0" min="0" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-slate-50">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Min. Stok</label>
                            <input type="number" step="0.01" id="min_stock" name="min_stock" value="0" min="0" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-slate-50">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-bahan')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-surface bg-primary hover:opacity-90 rounded-xl flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-request" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-request')"></div>
        <div class="bg-surface w-full max-w-md rounded-2xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-blue-50/50 rounded-t-2xl">
                <h3 class="text-lg font-black text-blue-800">Ajukan Permintaan Bahan</h3>
                <button onclick="closeModal('modal-request')" class="text-secondary hover:text-danger transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 overflow-y-auto">
                <form id="formRequest" class="space-y-4">
                    <input type="hidden" id="req_warehouse_id" name="warehouse_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bahan di Gudang Pilar <span class="text-danger">*</span></label>
                        <select id="pilar_material_id" name="pilar_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-slate-50 font-semibold text-slate-700">
                            <option value="">-- Memuat Stok Pilar --</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">Jumlah</label>
                            <input type="number" step="any" id="req_qty" name="qty" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-white text-lg font-black text-blue-600" placeholder="0">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pilih Satuan</label>
                            <select id="req_unit" name="req_unit" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none bg-white font-bold text-slate-700">
                                <option value="default">Sesuai Gudang</option>
                                <option value="gram">Gram (gr)</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="pcs">Pcs</option>
                            </select>
                        </div>
                        <div class="col-span-2 text-[10px] text-slate-400 font-medium">
                            *Jika Anda memilih Gram/Ml, sistem akan otomatis membaginya menjadi Kg/Liter saat memotong stok Gudang Pusat.
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-request')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-surface bg-blue-600 hover:bg-blue-700 rounded-xl flex items-center gap-2 shadow-md">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const canEdit = <?= hasPermission('edit_master_bahan') ? 'true' : 'false' ?>;
        const canDelete = <?= hasPermission('hapus_master_bahan') ? 'true' : 'false' ?>;
        const isOwner = <?= $is_owner ? 'true' : 'false' ?>;
    </script>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>