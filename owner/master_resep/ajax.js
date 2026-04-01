document.addEventListener("DOMContentLoaded", () => {
    loadProducts();
    loadMaterialsDropdown(); 
    loadUnitsDropdown(); // TAMBAHAN: Muat satuan dinamis
});

async function loadProducts() {
    const tbody = document.getElementById('table-products');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read_products', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary">Belum ada data produk. Silakan isi Master Produk dulu.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                let badge = item.total_bahan > 0 
                    ? `<span class="bg-success/10 text-success px-3 py-1 rounded-lg text-xs font-bold">${item.total_bahan} Bahan</span>`
                    : `<span class="bg-danger/10 text-danger px-3 py-1 rounded-lg text-xs font-bold">Resep Kosong</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-semibold text-slate-800">${item.name}</td>
                        <td class="p-4 text-slate-600">${item.category}</td>
                        <td class="p-4 text-center">${badge}</td>
                        <td class="p-4 text-center">
                            <button onclick="bukaModalResep(${item.id}, '${item.name}')" class="bg-primary/10 text-primary hover:bg-primary hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center mx-auto gap-2">
                                <i class="fa-solid fa-gears"></i> Atur Resep
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

function bukaModalResep(product_id, product_name) {
    document.getElementById('bom_product_id').value = product_id;
    document.getElementById('modal-product-name').innerText = `1 Pcs ${product_name}`;
    document.getElementById('quantity').value = ''; 
    
    loadBOM(product_id);
    openModal('modal-resep');
}

async function loadBOM(product_id) {
    const tbody = document.getElementById('table-bom');
    tbody.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-xs text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat resep...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_bom&product_id=${product_id}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="3" class="py-6 text-center text-sm text-secondary border border-dashed border-slate-300 rounded bg-slate-50">Belum ada bahan untuk produk ini.<br>Silakan tambah bahan di atas.</td></tr>';
        } else {
            response.data.forEach((item) => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                        <td class="py-3 px-4 text-slate-700 font-medium">${item.name}</td>
                        <td class="py-3 px-4 text-right text-slate-800 font-bold">${item.quantity_needed} <span class="text-xs text-slate-500 font-normal ml-1">${item.unit_used}</span></td>
                        <td class="py-3 px-4 text-center">
                            <button type="button" onclick="hapusBOM(${item.id}, ${product_id})" class="text-danger/70 hover:text-danger p-1 transition-colors" title="Hapus Bahan">
                                <i class="fa-solid fa-circle-minus"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        loadProducts(); // Refresh badge tabel di luar modal
    }
}

async function loadMaterialsDropdown() {
    const select = document.getElementById('material_id');
    const response = await fetchAjax('logic.php?action=get_materials', 'GET');
    
    if (response.status === 'success') {
        let options = '<option value="">-- Pilih Bahan Baku --</option>';
        response.data.forEach(m => {
            options += `<option value="${m.id}">${m.name} (Stok: ${m.unit})</option>`;
        });
        select.innerHTML = options;
    }
}

// TAMBAHAN BARU: FUNGSI MEMUAT DROPDOWN SATUAN
async function loadUnitsDropdown() {
    const select = document.getElementById('unit_used');
    const response = await fetchAjax('logic.php?action=get_units', 'GET');
    
    if (response.status === 'success') {
        let options = '';
        response.data.forEach(u => {
            options += `<option value="${u.name}">${u.name}</option>`;
        });
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
        document.getElementById('material_id').value = ''; 
        loadBOM(product_id); 
    } else {
        alert(response.message);
    }
});

async function hapusBOM(id, product_id) {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetchAjax('logic.php?action=delete_bom', 'POST', formData);
    if (response.status === 'success') {
        loadBOM(product_id); 
    }
}