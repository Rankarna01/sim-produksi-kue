// ============================================================
// RESEP ITEM CUSTOM POS — ajax_custom.js
// File terpisah dari ajax.js (Resep Produk Jadi)
// ============================================================

let draftBOMCustom = [];

// ==========================================
// INIT
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    loadCustomItems();
    loadMaterialsDropdownCustom();
    loadUnitsDropdownCustom();

    document.getElementById('formTambahBahanCustom').addEventListener('submit', function(e) {
        e.preventDefault();

        const selectMat = document.getElementById('custom_material_id');
        const mat_id    = selectMat.value;
        const mat_name  = selectMat.options[selectMat.selectedIndex].text.split(' (')[0];
        const qty       = parseFloat(document.getElementById('custom_quantity').value);
        const unit      = document.getElementById('custom_unit_used').value;

        if (!mat_id || isNaN(qty) || qty <= 0 || !unit) {
            Swal.fire('Ups!', 'Lengkapi semua field terlebih dahulu.', 'warning');
            return;
        }

        if (draftBOMCustom.findIndex(d => d.material_id == mat_id) !== -1) {
            Swal.fire('Ups!', 'Bahan ini sudah ada di racikan. Hapus yang lama terlebih dahulu.', 'warning');
            return;
        }

        draftBOMCustom.push({ material_id: mat_id, name: mat_name, quantity_needed: qty, unit_used: unit });

        selectMat.value = '';
        document.getElementById('custom_quantity').value = '';
        document.getElementById('custom_calc_total').value = '';
        document.getElementById('custom_calc_pcs').value = '';

        renderDraftBOMCustom();
    });
});

// ==========================================
// LOAD DAFTAR ITEM CUSTOM
// ==========================================
async function loadCustomItems() {
    const tbody = document.getElementById('table-custom-items');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary text-xs"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data item custom...</td></tr>';

    const response = await fetchAjax('logic_custom.php?action=read_custom_items', 'GET');

    if (response.status === 'success') {
        if (response.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400 italic font-bold text-xs">Belum ada item custom di sistem POS.</td></tr>';
            return;
        }

        let html = '';
        response.data.forEach((item, index) => {
            const badge = item.total_bahan > 0
                ? `<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">${item.total_bahan} Bahan</span>`
                : `<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest italic border border-rose-100">Belum Ada Resep</span>`;

            const harga = parseInt(item.price).toLocaleString('id-ID');

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                    <td class="p-4 font-black text-slate-800 uppercase text-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center text-xs flex-shrink-0">
                                <i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            ${item.name}
                        </div>
                    </td>
                    <td class="p-4 text-xs font-bold text-slate-500">
                        <span class="bg-slate-100 px-2 py-1 rounded-lg text-slate-600">Custom POS</span>
                    </td>
                    <td class="p-4 text-xs font-bold text-slate-600">Rp ${harga}</td>
                    <td class="p-4 text-center">${badge}</td>
                    <td class="p-4 text-center">
                        <button onclick="bukaModalResepCustom(${item.id}, '${item.name.replace(/'/g, "&apos;")}')" 
                            class="bg-violet-600 text-white hover:bg-violet-700 px-4 py-2 rounded-xl text-xs font-black transition-all shadow-md shadow-violet-100 flex items-center justify-center mx-auto gap-2">
                            <i class="fa-solid fa-gears"></i> Atur Resep
                        </button>
                    </td>
                </tr>`;
        });
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-rose-500 font-bold text-xs">${response.message || 'Gagal memuat data.'}</td></tr>`;
    }
}

// ==========================================
// BUKA MODAL RESEP CUSTOM
// ==========================================
function bukaModalResepCustom(custom_item_id, item_name) {
    document.getElementById('bom_custom_item_id').value = custom_item_id;
    document.getElementById('modal-custom-product-name').innerText = `RESEP 1 PCS — ${item_name.toUpperCase()}`;

    document.getElementById('custom_quantity').value = '';
    document.getElementById('custom_material_id').value = '';
    document.getElementById('custom_calc_total').value = '';
    document.getElementById('custom_calc_pcs').value = '';
    document.getElementById('panel-kalkulator-custom').classList.add('hidden');

    loadDraftBOMCustom(custom_item_id);
    openModal('modal-resep-custom');
}

// ==========================================
// LOAD DRAFT BOM CUSTOM DARI DB
// ==========================================
async function loadDraftBOMCustom(custom_item_id) {
    draftBOMCustom = [];
    const tbody = document.getElementById('table-bom-custom');
    tbody.innerHTML = '<tr><td class="p-5 text-center text-secondary text-xs"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Menyiapkan draft...</td></tr>';

    const response = await fetchAjax(`logic_custom.php?action=read_bom_custom&custom_item_id=${custom_item_id}`, 'GET');

    if (response.status === 'success') {
        response.data.forEach(item => {
            draftBOMCustom.push({
                material_id: item.material_id,
                name: item.name,
                quantity_needed: item.quantity_needed,
                unit_used: item.unit_used
            });
        });
        renderDraftBOMCustom();
    }
}

// ==========================================
// RENDER TABEL DRAFT BOM CUSTOM
// ==========================================
function renderDraftBOMCustom() {
    const tbody = document.getElementById('table-bom-custom');
    document.getElementById('total-item-badge-custom').innerText = `${draftBOMCustom.length} BAHAN`;

    if (draftBOMCustom.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-slate-400 italic font-bold text-xs">Belum ada bahan. Silakan tambahkan di atas.</td></tr>';
        return;
    }

    let html = '';
    draftBOMCustom.forEach((item, index) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-4 px-5 text-slate-800 font-black uppercase text-xs">${item.name}</td>
                <td class="py-4 px-5 text-right font-black text-violet-600 text-base">
                    ${parseFloat(item.quantity_needed)} 
                    <span class="text-[10px] text-slate-400 font-bold uppercase">${item.unit_used}</span>
                </td>
                <td class="py-4 px-5 text-center">
                    <button type="button" onclick="hapusDraftBOMCustom(${index})" 
                        class="text-rose-400 hover:text-rose-600 transition-colors bg-rose-50 w-8 h-8 rounded-lg flex items-center justify-center mx-auto">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </td>
            </tr>`;
    });
    tbody.innerHTML = html;
}

function hapusDraftBOMCustom(index) {
    draftBOMCustom.splice(index, 1);
    renderDraftBOMCustom();
}

// ==========================================
// KALKULATOR KONVERSI (Custom)
// ==========================================
function toggleKalkulatorCustom() {
    document.getElementById('panel-kalkulator-custom').classList.toggle('hidden');
}

function hitungKonversiCustom() {
    const totalBahan = parseFloat(document.getElementById('custom_calc_total').value);
    const hasilPcs   = parseFloat(document.getElementById('custom_calc_pcs').value);

    if (isNaN(totalBahan) || isNaN(hasilPcs) || hasilPcs <= 0) {
        Swal.fire('Ups!', 'Masukkan angka yang valid.', 'warning');
        return;
    }

    const hasil = totalBahan / hasilPcs;
    document.getElementById('custom_quantity').value = parseFloat(hasil.toFixed(5));
    Swal.fire({ title: 'Berhasil!', text: `Takaran per pcs: ${hasil.toFixed(5)}`, icon: 'success', timer: 1500, showConfirmButton: false });
    toggleKalkulatorCustom();
}

// ==========================================
// SIMPAN RESEP CUSTOM (Langsung, Tanpa Approval)
// ==========================================
async function simpanResepCustom() {
    const custom_item_id = document.getElementById('bom_custom_item_id').value;

    if (draftBOMCustom.length === 0) {
        Swal.fire('Ups!', 'Racikan resep tidak boleh kosong!', 'warning');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Simpan Resep?',
        html: `Resep item custom ini akan <strong>langsung disimpan dan menggantikan resep sebelumnya</strong> (jika ada).`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonText: 'Batal',
        confirmButtonText: '<i class="fa-solid fa-floppy-disk mr-1"></i> Ya, Simpan!'
    });

    if (!confirm.isConfirmed) return;

    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData();
    formData.append('custom_item_id', custom_item_id);
    formData.append('drafts', JSON.stringify(draftBOMCustom));

    const response = await fetchAjax('logic_custom.php?action=save_bom_custom', 'POST', formData);

    if (response.status === 'success') {
        closeModal('modal-resep-custom');
        loadCustomItems();
        Swal.fire({ title: 'Tersimpan!', text: response.message, icon: 'success', timer: 2000, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
}

// ==========================================
// HAPUS SEMUA RESEP ITEM CUSTOM
// ==========================================
async function hapusResepCustom() {
    const custom_item_id = document.getElementById('bom_custom_item_id').value;

    const confirm = await Swal.fire({
        title: 'Hapus Resep?',
        text: 'Semua bahan resep item custom ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Ya, Hapus!'
    });

    if (!confirm.isConfirmed) return;

    const formData = new FormData();
    formData.append('custom_item_id', custom_item_id);
    const response = await fetchAjax('logic_custom.php?action=delete_bom_custom', 'POST', formData);

    if (response.status === 'success') {
        draftBOMCustom = [];
        renderDraftBOMCustom();
        loadCustomItems();
        Swal.fire({ title: 'Terhapus!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
}

// ==========================================
// DROPDOWN LOADERS
// ==========================================
async function loadMaterialsDropdownCustom() {
    const select = document.getElementById('custom_material_id');
    const response = await fetchAjax('logic_custom.php?action=get_materials', 'GET');
    if (response.status === 'success') {
        let options = '<option value="">-- Pilih Bahan Baku --</option>';
        response.data.forEach(m => options += `<option value="${m.id}">${m.name.toUpperCase()} (Stok: ${m.unit})</option>`);
        select.innerHTML = options;
    }
}

async function loadUnitsDropdownCustom() {
    const select = document.getElementById('custom_unit_used');
    const response = await fetchAjax('logic_custom.php?action=get_units', 'GET');
    if (response.status === 'success') {
        let options = '<option value="">Satuan</option>';
        response.data.forEach(u => options += `<option value="${u.name}">${u.name}</option>`);
        select.innerHTML = options;
    }
}
