document.addEventListener("DOMContentLoaded", () => {
    loadProducts();
    loadMaterialsDropdown(); 
    loadUnitsDropdown(); 
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { 
    document.getElementById(id).classList.add('hidden'); 
    document.getElementById('panel-kalkulator').classList.add('hidden'); // Reset kalkulator
}

// ==========================================
// FITUR BARU: KALKULATOR KONVERSI
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

    // Hitung per pcs (Total / Hasil)
    const hasilPerPcs = totalBahan / hasilPcs;

    // Masukkan ke input utama (dibulatkan agar tidak terlalu panjang, misal 5 angka belakang koma)
    document.getElementById('quantity').value = parseFloat(hasilPerPcs.toFixed(5));
    
    // Beri efek visual sukses & tutup panel
    Swal.fire({
        title: 'Berhasil!',
        text: `Takaran terhitung: ${hasilPerPcs.toFixed(5)} per produk.`,
        icon: 'success',
        timer: 1500,
        showConfirmButton: false
    });
    toggleKalkulator();
}

// ==========================================
// LOGIC CRUD LAMA (DIPERTAHANKAN)
// ==========================================
async function loadProducts() {
    const tbody = document.getElementById('table-products');
    const response = await fetchAjax('logic.php?action=read_products', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        response.data.forEach((item, index) => {
            let badge = item.total_bahan > 0 
                ? `<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">${item.total_bahan} Bahan</span>`
                : `<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest italic">Belum Ada Resep</span>`;

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                    <td class="p-4 font-black text-slate-800 uppercase text-sm">${item.name}</td>
                    <td class="p-4 text-xs font-bold text-slate-500 tracking-wider">${item.category}</td>
                    <td class="p-4 text-center">${badge}</td>
                    <td class="p-4 text-center">
                        <button onclick="bukaModalResep(${item.id}, '${item.name.replace(/'/g, "&apos;")}')" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-xl text-xs font-black transition-all shadow-md shadow-blue-100 flex items-center justify-center mx-auto gap-2">
                            <i class="fa-solid fa-gears"></i> Atur Resep
                        </button>
                    </td>
                </tr>`;
        });
        tbody.innerHTML = html;
    }
}

function bukaModalResep(product_id, product_name) {
    document.getElementById('bom_product_id').value = product_id;
    document.getElementById('modal-product-name').innerText = `RESEP 1 PCS ${product_name.toUpperCase()}`;
    document.getElementById('quantity').value = ''; 
    document.getElementById('pilar_material_id').value = ''; 
    document.getElementById('calc_total_bahan').value = ''; 
    document.getElementById('calc_hasil_pcs').value = ''; 
    
    loadBOM(product_id);
    openModal('modal-resep');
}

async function loadBOM(product_id) {
    const tbody = document.getElementById('table-bom');
    const response = await fetchAjax(`logic.php?action=read_bom&product_id=${product_id}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        document.getElementById('total-item-badge').innerText = `${response.data.length} BAHAN`;
        
        if (response.data.length === 0) {
            html = '<tr><td class="p-10 text-center text-slate-400 italic font-bold">Resep masih kosong.</td></tr>';
        } else {
            response.data.forEach((item) => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-5 text-slate-800 font-bold uppercase text-xs">${item.name}</td>
                        <td class="py-4 px-5 text-right font-black text-blue-600 text-base">${parseFloat(item.quantity_needed)} <span class="text-[10px] text-slate-400 font-bold uppercase">${item.unit_used}</span></td>
                        <td class="py-4 px-5 text-center">
                            <button onclick="hapusBOM(${item.id}, ${product_id})" class="text-rose-400 hover:text-rose-600 transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                        </td>
                    </tr>`;
            });
        }
        tbody.innerHTML = html;
    }
}

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
        let options = '';
        response.data.forEach(u => options += `<option value="${u.name}">${u.name}</option>`);
        select.innerHTML = options;
    }
}

document.getElementById('formTambahBahan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const product_id = document.getElementById('bom_product_id').value;
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save_bom', 'POST', formData);

    if (response.status === 'success') {
        document.getElementById('quantity').value = ''; 
        loadBOM(product_id); 
        loadProducts(); // Update status resep di tabel utama
    } else {
        Swal.fire('Peringatan!', response.message, 'warning');
    }
});

async function hapusBOM(id, product_id) {
    const result = await Swal.fire({
        title: 'Hapus Bahan?',
        text: "Bahan ini akan dihapus dari resep.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus'
    });

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=delete_bom', 'POST', formData);
        if (response.status === 'success') {
            loadBOM(product_id);
            loadProducts();
        }
    }
}