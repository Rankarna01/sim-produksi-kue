let materialsData = [];
let drafts = [];

document.addEventListener('DOMContentLoaded', async () => {
    await initData();
});

// ==========================================
// 1. VERIFIKASI PIN
// ==========================================
document.getElementById('form-pin').addEventListener('submit', async function(e) {
    e.preventDefault();
    const pin = document.getElementById('input-pin').value;
    
    if(pin.length < 6) { Swal.fire('Ups!', 'PIN harus 6 digit!', 'warning'); return; }

    Swal.fire({ title: 'Memeriksa Akses...', icon: 'info', showConfirmButton: false });

    const formData = new FormData();
    formData.append('action', 'verify_pin');
    formData.append('pin', pin);

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        Swal.close();
        // PERBAIKAN: Buka Gembok Alpine.js menggunakan Custom Event
        window.dispatchEvent(new CustomEvent('unlock-session'));
    } else {
        Swal.fire('Akses Ditolak!', res.message, 'error');
        document.getElementById('input-pin').value = ''; // Reset Input
    }
});

// ==========================================
// 2. INIT DATA BARANG
// ==========================================
async function initData() {
    const res = await fetchAjax('logic.php?action=init_data', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;
        
        const select = document.getElementById('material_id');
        let options = '<option value="">Ketik nama barang atau SKU...</option>';
        res.materials.forEach(m => {
            options += `<option value="${m.id}" data-unit="${m.unit}" data-stock="${m.stock}">[${m.sku_code}] ${m.material_name}</option>`;
        });
        select.innerHTML = options;

        // Auto update label unit saat pilih barang
        select.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.getElementById('unit_label').innerText = selected.dataset.unit || '-';
        });
    }
}

// ==========================================
// 3. TAMBAH KE DAFTAR DRAFT
// ==========================================
function tambahKeDaftar() {
    const select = document.getElementById('material_id');
    const mat_id = select.value;
    const phys_qty = document.getElementById('phys_qty').value;
    const notes = document.getElementById('notes').value;

    if (!mat_id || phys_qty === '') {
        Swal.fire('Ups!', 'Pilih barang dan isi jumlah fisiknya!', 'warning'); return;
    }

    const selectedOption = select.options[select.selectedIndex];
    const mat_name = selectedOption.text;
    const sys_qty = parseFloat(selectedOption.dataset.stock);
    const p_qty = parseFloat(phys_qty);
    const unit = selectedOption.dataset.unit;
    const diff = p_qty - sys_qty;

    // Cek apakah barang sudah ada di draft (kalau ada timpa / update)
    const existIdx = drafts.findIndex(d => d.material_id == mat_id);
    if(existIdx !== -1) {
        drafts[existIdx] = { material_id: mat_id, material_name: mat_name, system_stock: sys_qty, physical_stock: p_qty, difference: diff, unit: unit, notes: notes };
    } else {
        drafts.push({ material_id: mat_id, material_name: mat_name, system_stock: sys_qty, physical_stock: p_qty, difference: diff, unit: unit, notes: notes });
    }

    // Reset Form Input
    select.value = '';
    document.getElementById('phys_qty').value = '';
    document.getElementById('notes').value = '';
    document.getElementById('unit_label').innerText = '-';

    renderDraft();
}

function hapusDraft(index) {
    drafts.splice(index, 1);
    renderDraft();
}

// ==========================================
// 4. RENDER TABEL DRAFT
// ==========================================
function renderDraft() {
    const tbody = document.getElementById('draft-table');
    document.getElementById('draft-count').innerText = `${drafts.length} Item`;

    if (drafts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-bold">Draft masih kosong. Tambahkan barang di atas.</td></tr>';
        return;
    }

    let html = '';
    drafts.forEach((item, idx) => {
        // Logika Warna Selisih
        let diffHTML = '';
        if (item.difference > 0) diffHTML = `<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded font-black text-xs">+${item.difference}</span>`;
        else if (item.difference < 0) diffHTML = `<span class="bg-rose-100 text-rose-700 px-2 py-1 rounded font-black text-xs">${item.difference}</span>`;
        else diffHTML = `<span class="bg-slate-100 text-slate-500 px-2 py-1 rounded font-black text-xs">Sesuai (0)</span>`;

        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="p-4 font-black text-slate-700 text-xs uppercase">${item.material_name}</td>
                <td class="p-4 text-center font-bold text-slate-500">${item.system_stock} <span class="text-[10px]">${item.unit}</span></td>
                <td class="p-4 text-center font-black text-indigo-600 text-lg">${item.physical_stock} <span class="text-[10px] text-indigo-400">${item.unit}</span></td>
                <td class="p-4 text-center">${diffHTML}</td>
                <td class="p-4 text-xs text-slate-500 italic">${item.notes || '-'}</td>
                <td class="p-4 text-center">
                    <button onclick="hapusDraft(${idx})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// ==========================================
// 5. SIMPAN HASIL OPNAME (FINAL SUBMIT)
// ==========================================
async function simpanOpname() {
    if(drafts.length === 0) { Swal.fire('Ups!', 'Belum ada barang di daftar draft.', 'warning'); return; }

    const confirm = await Swal.fire({
        title: 'Simpan Hasil Opname?',
        text: 'Stok sistem akan ditimpa dengan stok fisik yang Anda masukkan ini!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4F46E5', // Indigo-600
        confirmButtonText: 'Ya, Simpan & Sesuaikan Stok!'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

        const formData = new FormData();
        formData.append('action', 'save_opname');
        formData.append('drafts', JSON.stringify(drafts));

        const res = await fetchAjax('logic.php', 'POST', formData);

        if (res.status === 'success') {
            Swal.fire('Selesai!', res.message, 'success');
            drafts = []; // Bersihkan draft
            renderDraft();
            initData(); // Refresh data stok untuk scan selanjutnya
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}