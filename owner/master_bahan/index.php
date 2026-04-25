<?php
require_once '../../config/auth.php';
require_once '../../config/database.php'; 
checkPermission('master_bahan');

$user_kitchen_id = $_SESSION['kitchen_id'] ?? null;
$is_owner = is_null($user_kitchen_id);

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
                    <p class="text-sm text-secondary mt-1">Kelola stok dapur dan ajukan permintaan bahan ke Gudang Pusat.</p>
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
                            <tr class="bg-background border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-4 text-center w-16">No</th>
                                <th class="p-4">Kode</th>
                                <th class="p-4">Nama Bahan</th>
                                <th class="p-4 text-center">Satuan</th>
                                <th class="p-4 text-right">Stok Aktual</th>
                                <th class="p-4 text-right">Min. Stok</th>
                                <th class="p-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="7" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Riwayat Pengajuan ke Pusat</h3>
                <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
                    <button @click="tabStatus = 'semua'; loadRequests('semua', 1)" :class="tabStatus === 'semua' ? 'bg-primary text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Semua</button>
                    <button @click="tabStatus = 'menunggu'; loadRequests('menunggu', 1)" :class="tabStatus === 'menunggu' ? 'bg-amber-500 text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Menunggu</button>
                    <button @click="tabStatus = 'berhasil'; loadRequests('berhasil', 1)" :class="tabStatus === 'berhasil' ? 'bg-emerald-500 text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Berhasil</button>
                    <button @click="tabStatus = 'ditolak'; loadRequests('ditolak', 1)" :class="tabStatus === 'ditolak' ? 'bg-danger text-white shadow-md' : 'bg-surface text-slate-500 hover:bg-slate-100 border border-slate-200'" class="px-5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">Ditolak</button>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-background border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-4 text-center w-16">No</th>
                                    <th class="p-4">Tanggal Pengajuan</th>
                                    <th class="p-4">No. Request</th>
                                    <th class="p-4 text-center">Total Item</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center w-28">Detail</th>
                                </tr>
                            </thead>
                            <tbody id="table-requests" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="6" class="p-8 text-center text-secondary">Memuat riwayat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="pagination-requests" class="mt-4 flex items-center justify-center gap-2 mb-10"></div>
            </div>

        </main>
    </div>

    <div id="modal-request" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-request')"></div>
        <div class="bg-surface w-full max-w-4xl rounded-[2rem] shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-blue-50 rounded-t-[2rem]">
                <div>
                    <h3 class="text-xl font-black text-blue-800 flex items-center gap-2"><i class="fa-solid fa-cart-flatbed"></i> Ajukan Permintaan Bahan</h3>
                    <p class="text-xs font-bold text-blue-600/70 mt-1">Anda dapat meminta lebih dari satu bahan dalam satu formulir.</p>
                </div>
                <button onclick="closeModal('modal-request')" class="w-10 h-10 rounded-full flex items-center justify-center text-blue-400 hover:bg-rose-50 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto bg-slate-50/50 flex-1 custom-scrollbar">
                <form id="formRequest">
                    <input type="hidden" id="req_warehouse_id" name="warehouse_id">
                    
                    <div id="req-item-container" class="space-y-4">
                        </div>

                    <button type="button" onclick="addRequestRow()" class="mt-5 bg-white hover:bg-slate-100 text-blue-600 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-2 border border-slate-300 border-dashed w-full sm:w-auto justify-center shadow-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Bahan Lainnya
                    </button>
                </form>
            </div>
            
            <div class="p-6 border-t border-slate-100 flex justify-end gap-3 rounded-b-[2rem] bg-white">
                <button type="button" onclick="closeModal('modal-request')" class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-all">Batal</button>
                <button type="submit" form="formRequest" class="px-8 py-2.5 text-sm font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl flex items-center gap-2 shadow-md transition-all">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Semua Pengajuan
                </button>
            </div>
        </div>
    </div>

    <div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-detail')"></div>
        <div class="bg-surface w-full max-w-2xl rounded-3xl shadow-xl z-10 transform transition-all flex flex-col max-h-[80vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800">Detail Permintaan: <span id="det-req-no" class="text-blue-600"></span></h3>
                <button onclick="closeModal('modal-detail')" class="text-slate-400 hover:text-rose-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-6 overflow-y-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="p-3">No</th>
                            <th class="p-3">Bahan Baku</th>
                            <th class="p-3 text-center">Qty Diminta</th>
                        </tr>
                    </thead>
                    <tbody id="table-detail-req" class="divide-y divide-slate-50"></tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>