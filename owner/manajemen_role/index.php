<?php
require_once '../../config/auth.php';
require_once '../../config/database.php'; // Tambahkan ini untuk akses DB

// Gunakan permission master_user atau buat permission baru khusus master_role
checkPermission('master_user');

// --- SUNTIKAN: AMBIL DATA DAPUR DINAMIS ---
$stmtKitchens = $pdo->query("SELECT * FROM kitchens ORDER BY id ASC");
$kitchens = $stmtKitchens->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Style khusus untuk checkbox utama agar terlihat seperti Card */
        .perm-card {
            transition: all 0.2s;
        }

        .perm-checkbox:checked+.perm-card {
            background-color: #eff6ff;
            /* blue-50 */
            border-color: #3b82f6;
            /* blue-500 */
        }

        .perm-checkbox:checked+.perm-card .perm-icon {
            color: #2563eb;
            /* blue-600 */
        }

        /* Style untuk sub-checkbox (Edit/Hapus) */
        .sub-perm-checkbox:checked+.sub-perm-label {
            background-color: #fef3c7;
            /* amber-50 */
            color: #d97706;
            /* amber-600 */
            border-color: #f59e0b;
            /* amber-500 */
        }

        .sub-perm-checkbox-danger:checked+.sub-perm-label {
            background-color: #fef2f2;
            /* red-50 */
            color: #dc2626;
            /* red-600 */
            border-color: #ef4444;
            /* red-500 */
        }
    </style>
</head>

<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full relative">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Role & Akses</h2>
                    <p class="text-sm text-secondary mt-1">Buat jabatan baru dan atur menu apa saja yang boleh mereka akses.</p>
                </div>
                <button onclick="openModalAdd()" class="bg-primary hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-plus"></i> Tambah Jabatan
                </button>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                <th class="p-4 font-bold w-12 text-center">No</th>
                                <th class="p-4 font-bold">Nama Jabatan (Role)</th>
                                <th class="p-4 font-bold">Kode Sistem (Slug)</th>
                                <th class="p-4 font-bold text-center">Total Akses Menu</th>
                                <th class="p-4 font-bold text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                            <tr>
                                <td colspan="5" class="p-8 text-center text-secondary">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-role" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-role')"></div>
        <div class="relative bg-surface w-full max-w-4xl rounded-3xl shadow-xl z-10 flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl shrink-0">
                <h3 class="text-lg font-bold text-slate-800" id="modal-title"><i class="fa-solid fa-shield-halved text-primary mr-2"></i> Tambah Jabatan Baru</h3>
                <button onclick="closeModal('modal-role')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                <form id="formRole">
                    <input type="hidden" id="form_mode" value="add">
                    <input type="hidden" id="old_slug" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Nama Jabatan <span class="text-danger">*</span></label>
                            <input type="text" id="role_name" required placeholder="Cth: Supervisor Gudang" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Kode Slug Sistem <span class="text-danger">*</span></label>
                            <input type="text" id="role_slug" required placeholder="Cth: spv_gudang" pattern="[a-z0-9_]+" title="Hanya huruf kecil, angka, dan underscore (_)" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-slate-50">
                            <p class="text-[10px] text-secondary mt-1">Tanpa spasi. Gunakan underscore (_). Cth: admin_dapur</p>
                        </div>
                    </div>

                    <div class="mb-2">
                        <h4 class="text-sm font-bold text-slate-800 border-b border-slate-200 pb-2 mb-4">Pengaturan Hak Akses Menu</h4>
                    </div>

                    <div class="space-y-6">

                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3"><i class="fa-solid fa-store mr-1"></i> Akses Dapur & Cabang</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?php
                                // Looping data dapur dari database
                                foreach ($kitchens as $k):
                                ?>
                                    <?= renderCheckbox('akses_dapur_' . $k['id'], 'Akses ' . htmlspecialchars($k['name']), 'fa-shop') ?>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2 italic">* Centang dapur mana saja yang boleh dilihat/dikelola oleh jabatan ini.</p>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3"><i class="fa-solid fa-database mr-1"></i> Data Master & Pengaturan</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?= renderCheckboxGroup('manajemen_dapur', 'Manajemen Dapur', 'fa-store') ?>
                                <?= renderCheckboxGroup('master_gudang', 'Data Gudang', 'fa-warehouse') ?>
                                <?= renderCheckboxGroup('master_produk', 'Data Produk', 'fa-box') ?>
                                <?= renderCheckboxGroup('master_kategori', 'Kategori Produk', 'fa-tags') ?>
                                <?= renderCheckboxGroup('master_bahan', 'Data Bahan Baku', 'fa-wheat-awn') ?>

                                <?= renderCheckboxGroup('master_titipan', 'Master Barang Titipan UMKM', 'fa-store') ?>

                                <?= renderCheckboxGroup('master_satuan', 'Master Satuan', 'fa-weight-scale') ?>
                                <?= renderCheckbox('master_resep', 'Data Resep (BOM)', 'fa-list-check') ?>
                                <?= renderCheckbox('master_user', 'Manajemen User & Role', 'fa-users-gear') ?>
                                <?= renderCheckboxGroup('master_stok_pusat', 'Master Stok Gudang (Pilar)', 'fa-cubes-stacked') ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3"><i class="fa-solid fa-industry mr-1"></i> Operasional & Transaksi</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?= renderCheckbox('view_dashboard', 'Akses Dashboard', 'fa-chart-pie') ?>
                                <?= renderCheckbox('persetujuan_owner', 'Persetujuan Owner (Resep/BOM)', 'fa-clipboard-check') ?>
                                <?= renderCheckbox('stok_opname', 'Stok Opname', 'fa-scale-balanced') ?>
                                <?= renderCheckbox('otorisasi', 'Otorisasi Akses PIN', 'fa-key') ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3"><i class="fa-solid fa-file-lines mr-1"></i> Laporan & Analitik</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <?= renderCheckbox('laporan_produksi', 'Laporan Produksi', 'fa-chart-line') ?>
                                <?= renderCheckbox('laporan_keluar', 'Laporan Produk Keluar', 'fa-box-open') ?>
                                <?= renderCheckbox('audit_logs', 'Audit Logs (Lacak)', 'fa-shoe-prints') ?>
                                <?= renderCheckbox('analisa_produk', 'Analisa Performa', 'fa-chart-simple') ?>
                                <?= renderCheckbox('laporan_bahan', 'Laporan Bahan Baku', 'fa-wheat-awn-circle-exclamation') ?>
                                <?= renderCheckbox('laporan_produk_jadi', 'Laporan Produk Jadi', 'fa-boxes-stacked') ?>
                                <?= renderCheckbox('laporan_bom', 'Laporan Resep (BOM)', 'fa-clipboard-list') ?>
                                <?= renderCheckbox('laporan_titipan', 'Laporan Titipan UMKM', 'fa-chart-line') ?>
                                <?= renderCheckbox('laporan_opname', 'Laporan Opname', 'fa-clipboard-check') ?>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="p-5 border-t border-slate-100 bg-slate-50 shrink-0 flex justify-end gap-3 rounded-b-3xl">
                <button type="button" onclick="closeModal('modal-role')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">Batal</button>
                <button type="button" onclick="saveData()" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                    <i class="fa-solid fa-save mr-1"></i> Simpan Jabatan
                </button>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
    <script>
        // Script kecil agar jika induknya di-uncheck, anak-anaknya juga ikut di-uncheck otomatis
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('master-perm')) {
                if (!e.target.checked) {
                    const parentCard = e.target.closest('.group-card');
                    if (parentCard) {
                        parentCard.querySelectorAll('.sub-perm-checkbox, .sub-perm-checkbox-danger').forEach(sub => {
                            sub.checked = false;
                        });
                    }
                }
            }
        });
    </script>
</body>

</html>

<?php
// Fungsi Helper LAMA (Hanya akses halaman)
function renderCheckbox($value, $label, $icon)
{
    return '
    <label class="cursor-pointer relative block">
        <input type="checkbox" name="permissions[]" value="' . $value . '" class="perm-checkbox peer absolute opacity-0 w-0 h-0">
        <div class="perm-card flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 bg-white shadow-sm h-full">
            <div class="w-5 text-center text-slate-400 perm-icon transition-colors"><i class="fa-solid ' . $icon . '"></i></div>
            <div class="text-xs font-bold text-slate-700 select-none">' . $label . '</div>
        </div>
    </label>
    ';
}

// Fungsi Helper BARU (Akses Halaman + Opsi Edit & Hapus)
function renderCheckboxGroup($value, $label, $icon)
{
    return '
    <div class="border border-slate-200 rounded-xl bg-white shadow-sm group-card flex flex-col h-full overflow-hidden">
        <label class="cursor-pointer relative flex-1">
            <input type="checkbox" name="permissions[]" value="' . $value . '" class="perm-checkbox master-perm peer absolute opacity-0 w-0 h-0">
            <div class="perm-card flex items-center gap-3 p-3 transition-colors h-full">
                <div class="w-5 text-center text-slate-400 perm-icon transition-colors"><i class="fa-solid ' . $icon . '"></i></div>
                <div class="text-xs font-bold text-slate-700 select-none">' . $label . '</div>
            </div>
        </label>
        <div class="bg-slate-50 border-t border-slate-100 p-2 flex gap-2">
            <label class="cursor-pointer flex-1 text-center">
                <input type="checkbox" name="permissions[]" value="edit_' . $value . '" class="sub-perm-checkbox peer absolute opacity-0 w-0 h-0">
                <div class="sub-perm-label text-[10px] font-bold text-slate-500 border border-slate-200 bg-white py-1 rounded transition-colors select-none">Edit</div>
            </label>
            <label class="cursor-pointer flex-1 text-center">
                <input type="checkbox" name="permissions[]" value="hapus_' . $value . '" class="sub-perm-checkbox-danger peer absolute opacity-0 w-0 h-0">
                <div class="sub-perm-label text-[10px] font-bold text-slate-500 border border-slate-200 bg-white py-1 rounded transition-colors select-none">Hapus</div>
            </label>
        </div>
    </div>
    ';
}
?>