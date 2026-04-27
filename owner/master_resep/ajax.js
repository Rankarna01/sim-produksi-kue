let draftBOM = []; // Variabel penampung racikan sementara

document.addEventListener("DOMContentLoaded", () => {
    loadProducts();
    loadMaterialsDropdown(); 
    loadUnitsDropdown(); 
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { 
    document.getElementById(id).classList.add('hidden'); 
    document.getElementById('panel-kalkulator').classList.add('hidden'); 
}

// ==========================================
// FITUR KALKULATOR KONVERSI (TETAP SAMA)
// ==========================================
function toggleKalkulator() {
    const panel = document.getElementById('panel-kalkulator');
    panel.classList.toggle('hidden');
}

function hitungKonversi() {
    const totalBahan = parseFloat(document.getElementById('calc_total_bahan').value);
    const hasilPcs = parseFloat(document.getElementById('calc_hasil_pcs').value);

    if (isNaN(totalBahan) || isNaN(hasilPcs) || hasilPcs <= 0) {
        Swal.fire('Ups!', 'Masukkan angka total bahan dan hasil jadi yang valid.', 'warning');
        return;
    }

    const hasilPerPcs = totalBahan / hasilPcs;
    document.getElementById('quantity').value = parseFloat(hasilPerPcs.toFixed(5));
    
    Swal.fire({ title: 'Berhasil!', text: `Takaran terhitung: ${hasilPerPcs.toFixed(5)} per produk.`, icon: 'success', timer: 1500, showConfirmButton: false });
    toggleKalkulator();
}

// ==========================================
// MENAMPILKAN DAFTAR PRODUK DI LAYAR UTAMA
// ==========================================
async function loadProducts() {
    const tbody = document.getElementById('table-products');
    const response = await fetchAjax('logic.php?action=read_products', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        response.data.forEach((item, index) => {
            
            // Badge Resep Saat Ini
            let badge = item.total_bahan > 0 
                ? `<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">${item.total_bahan} Bahan</span>`
                : `<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest italic border border-rose-100">Resep Kosong</span>`;

            // FITUR BARU: Badge Status Menunggu Persetujuan
            let badgePending = item.pending_status === 'pending' 
                ? `<br><span class="inline-block mt-2 bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest"><i class="fa-solid fa-clock mr-1"></i> Menunggu ACC Owner</span>` 
                : '';

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                    <td class="p-4 font-black text-slate-800 uppercase text-sm">${item.name}</td>
                    <td class="p-4 text-xs font-bold text-slate-500 tracking-wider">${item.category}</td>
                    <td class="p-4 text-center">${badge} ${badgePending}</td>
                    <td class="p-4 text-center">
                        <button onclick="bukaModalResep(${item.id}, '${item.name.replace(/'/g, "&apos;")}')" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-xl text-xs font-black transition-all shadow-md shadow-blue-100 flex items-center justify-center mx-auto gap-2">
                            <i class="fa-solid fa-gears"></i> Ubah / Atur Resep
                        </button>
                    </td>
                </tr>`;
        });
        tbody.innerHTML = html;
    }
}

// ==========================================
// LOGIKA DRAFT (PENGGANTI SIMPAN LANGSUNG)
// ==========================================
function bukaModalResep(product_id, product_name) {
    document.getElementById('bom_product_id').value = product_id;
    document.getElementById('modal-product-name').innerText = `RESEP 1 PCS ${product_name.toUpperCase()}`;
    
    // Reset Form
    document.getElementById('quantity').value = ''; 
    document.getElementById('pilar_material_id').value = ''; 
    document.getElementById('calc_total_bahan').value = ''; 
    document.getElementById('calc_hasil_pcs').value = ''; 
    document.getElementById('req_notes').value = ''; 
    
    // Ambil resep yang sudah ada di database untuk dijadikan base draft
    loadDraftBOM(product_id);
    openModal('modal-resep');
}

async function loadDraftBOM(product_id) {
    draftBOM = []; // Kosongkan array lokal
    const tbody = document.getElementById('table-bom');
    tbody.innerHTML = '<tr><td class="p-5 text-center text-secondary text-xs"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Menyiapkan draft...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_bom&product_id=${product_id}`, 'GET');
    
    if (response.status === 'success') {
        // Salin data dari DB ke dalam Array JavaScript (Local Draft)
        response.data.forEach(item => {
            draftBOM.push({
                material_id: item.material_id,
                name: item.name,
                quantity_needed: item.quantity_needed,
                unit_used: item.unit_used
            });
        });
        renderDraftBOM();
    }
}

function renderDraftBOM() {
    const tbody = document.getElementById('table-bom');
    document.getElementById('total-item-badge').innerText = `${draftBOM.length} BAHAN`;
    
    if (draftBOM.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="p-6 text-center text-slate-400 italic font-bold text-xs">Belum ada bahan. Silakan tambahkan di atas.</td></tr>';
        return;
    }

    let html = '';
    draftBOM.forEach((item, index) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-4 px-5 text-slate-800 font-black uppercase text-xs">${item.name}</td>
                <td class="py-4 px-5 text-right font-black text-blue-600 text-base">${parseFloat(item.quantity_needed)} <span class="text-[10px] text-slate-400 font-bold uppercase">${item.unit_used}</span></td>
                <td class="py-4 px-5 text-center">
                    <button type="button" onclick="hapusDraftBOM(${index})" class="text-rose-400 hover:text-rose-600 transition-colors bg-rose-50 w-8 h-8 rounded-lg flex items-center justify-center mx-auto"><i class="fa-solid fa-trash-can text-xs"></i></button>
                </td>
            </tr>`;
    });
    tbody.innerHTML = html;
}

// Saat tombol "Tambah ke Draft" di-submit
document.getElementById('formTambahBahan').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selectMat = document.getElementById('pilar_material_id');
    const mat_id = selectMat.value;
    const mat_name = selectMat.options[selectMat.selectedIndex].text.split(' (')[0]; // Buang info unit di belakang nama
    const qty = parseFloat(document.getElementById('quantity').value);
    const unit = document.getElementById('unit_used').value;

    // Cek apakah bahan sudah ada di racikan
    const existIdx = draftBOM.findIndex(d => d.material_id == mat_id);
    if(existIdx !== -1) {
        Swal.fire('Ups!', 'Bahan ini sudah ada di racikan. Silakan hapus yang lama dulu jika ingin merubah takarannya.', 'warning');
        return;
    }

    // Push ke array lokal
    draftBOM.push({
        material_id: mat_id,
        name: mat_name,
        quantity_needed: qty,
        unit_used: unit
    });

    // Reset inputan
    document.getElementById('quantity').value = ''; 
    selectMat.value = '';
    document.getElementById('calc_total_bahan').value = '';
    document.getElementById('calc_hasil_pcs').value = '';
    
    renderDraftBOM(); // Render ulang tabel
});

function hapusDraftBOM(index) {
    draftBOM.splice(index, 1);
    renderDraftBOM();
}

// ==========================================
// KIRIM DRAFT SEBAGAI PENGAJUAN (FINAL SUBMIT)
// ==========================================
async function ajukanResep() {
    const product_id = document.getElementById('bom_product_id').value;
    const notes = document.getElementById('req_notes').value;

    if (draftBOM.length === 0) {
        Swal.fire('Ups!', 'Racikan resep tidak boleh kosong!', 'warning');
        return;
    }

    if (!notes) {
        Swal.fire('Ups!', 'Mohon isi Catatan/Alasan mengapa resep ini diubah agar Owner mengerti.', 'warning');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Kirim Pengajuan?',
        text: "Resep baru ini akan dikirim ke Owner untuk disetujui. Resep lama tetap aktif sampai disetujui.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Ya, Kirim Pengajuan'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Mengirim...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

        const formData = new FormData();
        formData.append('product_id', product_id);
        formData.append('notes', notes);
        formData.append('drafts', JSON.stringify(draftBOM));

        const response = await fetchAjax('logic.php?action=submit_bom_request', 'POST', formData);

        if (response.status === 'success') {
            closeModal('modal-resep');
            loadProducts(); // Render ulang tabel agar label kuning 'Menunggu ACC' muncul
            Swal.fire('Terkirim!', response.message, 'success');
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}

// DROPDOWN LOADERS
async function loadMaterialsDropdown() {
    const select = document.getElementById('pilar_material_id');
    const response = await fetchAjax('logic.php?action=get_materials', 'GET');
    if (response.status === 'success') {
        let options = '<option value="">-- Pilih Bahan Baku --</option>';
        response.data.forEach(m => options += `<option value="${m.id}">${m.name.toUpperCase()} (Beli: ${m.unit})</option>`);
        select.innerHTML = options;
    }
}

async function loadUnitsDropdown() {
    const select = document.getElementById('unit_used');
    const response = await fetchAjax('logic.php?action=get_units', 'GET');
    if (response.status === 'success') {
        let options = '<option value="">Satuan</option>';
        response.data.forEach(u => options += `<option value="${u.name}">${u.name}</option>`);
        select.innerHTML = options;
    }
}