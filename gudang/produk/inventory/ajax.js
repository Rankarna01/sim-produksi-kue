let currentPage = 1;
let currentTab = 'active';
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", async () => {
    await initFormDropdowns();
    loadData(1);
});

async function initFormDropdowns() {
    try {
        const res = await fetchAjax('logic.php?action=init_form', 'GET');
        if (res.status === 'success') {
            // Render Kategori
            let optCat = '<option value="">-- Pilih Kategori --</option>';
            res.categories.forEach(c => { optCat += `<option value="${c.id}">${c.name}</option>`; });
            document.getElementById('category_id').innerHTML = optCat;

            // Render Satuan (mengambil name-nya)
            let optUnit = '<option value="">-- Pilih Satuan --</option>';
            res.units.forEach(u => { optUnit += `<option value="${u.name}">${u.name}</option>`; });
            document.getElementById('unit').innerHTML = optUnit;

            // Render Lokasi Rak
            let optRack = '<option value="">-- Tidak Ada / Kosong --</option>';
            res.racks.forEach(r => { optRack += `<option value="${r.id}">${r.name}</option>`; });
            document.getElementById('rack_id').innerHTML = optRack;
        }
    } catch (e) {
        console.error("Gagal load dropdown:", e);
    }
}

function switchTab(tab) {
    currentTab = tab;
    ['active', 'inactive', 'all'].forEach(t => {
        const btn = document.getElementById(`tab-${t}`);
        if(t === tab) {
            btn.className = "px-4 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition-all";
        } else {
            btn.className = "px-4 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-700 transition-all";
        }
    });
    loadData(1);
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500); 
}

function resetForm() {
    document.getElementById('formInventory').reset();
    document.getElementById('id').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('modal-title').innerText = 'Tambah Master Barang';
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const search = document.getElementById('search').value;
    const response = await fetchAjax(`logic.php?action=read&search=${search}&tab=${currentTab}&page=${currentPage}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Data barang kosong.</td></tr>';
        } else {
            const today = new Date();
            
            response.data.forEach((item) => {
                // LOGIKA UI: STOK MENIPIS & KADALUARSA
                let stockClass = "bg-slate-100 text-slate-700";
                let expInfo = "";
                
                const stockVal = parseFloat(item.stock);
                const minStockVal = parseFloat(item.min_stock);

                // Cek Stok Merah
                if (stockVal <= minStockVal) {
                    stockClass = "bg-danger/10 text-danger font-black border border-danger/20 shadow-sm";
                }

                // Cek Kadaluarsa
                if (item.expiry_date) {
                    const expDate = new Date(item.expiry_date);
                    const diffTime = Math.abs(expDate - today);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                    
                    if (expDate <= today) {
                        expInfo = `<br><span class="text-[10px] font-bold text-danger"><i class="fa-solid fa-triangle-exclamation"></i> KADALUARSA!</span>`;
                    } else if (diffDays <= 30) { // Kurang dari 30 hari
                        expInfo = `<br><span class="text-[10px] font-bold text-danger"><i class="fa-regular fa-clock"></i> ${item.expiry_date} (Expiring!)</span>`;
                    } else {
                        expInfo = `<br><span class="text-[10px] text-slate-400">${item.expiry_date}</span>`;
                    }
                } else {
                    expInfo = `<br><span class="text-[10px] text-slate-400">Tanpa Exp</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors ${item.status === 'inactive' ? 'opacity-50 grayscale' : ''}">
                        <td class="p-4 font-mono text-xs font-bold text-slate-500">#${item.sku_code}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 text-sm">${item.material_name}</div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">${item.category_name || 'Tanpa Kategori'} • ${item.unit}</div>
                        </td>
                        <td class="p-4 font-bold text-slate-600 text-sm">${item.rack_name || '-'}</td>
                        <td class="p-4 text-center">
                            <span class="${stockClass} px-3 py-1 rounded-full text-sm inline-block">
                                ${stockVal}
                            </span>
                        </td>
                        <td class="p-4 text-sm font-semibold text-slate-600 leading-tight">
                            ${expInfo}
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editData(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors" title="Edit Data">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                ${item.status === 'active' ? `
                                <button onclick="archiveData(${item.id})" class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 hover:bg-slate-500 hover:text-white transition-colors" title="Arsipkan Barang">
                                    <i class="fa-solid fa-box-archive text-xs"></i>
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    html += `<button type="button" ${current > 1 ? `onclick="loadData(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i></button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadData(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadData(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

document.getElementById('formInventory').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', text: 'Memproses data', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-inventory');
        loadData(currentPage); 
        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

function editData(item) {
    resetForm();
    document.getElementById('id').value = item.id;
    document.getElementById('sku_code').value = item.sku_code;
    document.getElementById('material_name').value = item.material_name;
    document.getElementById('category_id').value = item.category_id || '';
    document.getElementById('unit').value = item.unit;
    document.getElementById('rack_id').value = item.rack_id || '';
    document.getElementById('stock').value = item.stock;
    document.getElementById('min_stock').value = item.min_stock;
    document.getElementById('expiry_date').value = item.expiry_date || '';
    document.getElementById('status').value = item.status;
    
    document.getElementById('modal-title').innerText = 'Edit Master Barang';
    openModal('modal-inventory');
}

async function archiveData(id) {
    const result = await Swal.fire({
        title: 'Arsipkan Barang?',
        text: "Barang tidak akan terhapus, tapi disembunyikan dari daftar aktif agar data transaksi lama tidak rusak.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#64748B',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Arsipkan!'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=archive', 'POST', formData);
        
        if (response.status === 'success') {
            loadData(currentPage);
            Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}