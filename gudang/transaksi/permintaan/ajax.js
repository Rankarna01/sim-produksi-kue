let currentTab = 'semua';
let currentPage = 1;
let searchTimeout = null;
let materialsData = [];
let cart = [];

document.addEventListener("DOMContentLoaded", async () => {
    await initFormDropdowns();
    loadData(1);
});

async function initFormDropdowns() {
    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;
    }
}

// ===============================================
// FITUR AUTOCOMPLETE PENCARIAN BARANG
// ===============================================
function filterMaterialList() {
    const keyword = document.getElementById('search_material').value.toLowerCase();
    const listContainer = document.getElementById('material_list');
    
    listContainer.innerHTML = '';
    
    if (keyword.length < 1) {
        listContainer.classList.add('hidden');
        document.getElementById('material_id').value = '';
        document.getElementById('unit_label').value = '';
        return;
    }

    const filtered = materialsData.filter(m => 
        m.material_name.toLowerCase().includes(keyword) || 
        m.sku_code.toLowerCase().includes(keyword)
    );

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="p-3 text-xs text-slate-400 italic font-bold">Barang tidak ditemukan di Inventory.</div>`;
        listContainer.classList.remove('hidden');
        return;
    }

    filtered.forEach(m => {
        const div = document.createElement('div');
        div.className = "p-3 border-b border-slate-50 hover:bg-blue-50 cursor-pointer transition-colors";
        div.innerHTML = `
            <div class="font-black text-slate-800 text-xs">${m.material_name}</div>
            <div class="text-[10px] text-slate-500 font-mono font-bold mt-0.5">[${m.sku_code}] • Stok Gudang: ${parseFloat(m.stock)} ${m.unit}</div>
        `;
        div.onclick = () => { pilihMaterial(m.id, m.material_name, m.unit); };
        listContainer.appendChild(div);
    });

    listContainer.classList.remove('hidden');
}

function pilihMaterial(id, name, unit) {
    document.getElementById('material_id').value = id;
    document.getElementById('search_material').value = name;
    document.getElementById('unit_label').value = unit;
    
    // Tutup list
    document.getElementById('material_list').classList.add('hidden');
}

// Tutup list autocomplete jika user klik sembarangan di luar input
document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('search_material');
    const listContainer = document.getElementById('material_list');
    if (e.target !== searchInput && !listContainer.contains(e.target)) {
        listContainer.classList.add('hidden');
    }
});

// ==========================================
// LOGIC CART (KERANJANG)
// ==========================================
function addToCart() {
    const mat_id = document.getElementById('material_id').value;
    const mat_name = document.getElementById('search_material').value; 
    const qty = parseFloat(document.getElementById('qty').value);
    const notes = document.getElementById('notes').value;
    const unit = document.getElementById('unit_label').value;

    if (!mat_id || qty <= 0 || isNaN(qty)) {
        Swal.fire('Ups!', 'Pilih barang dari daftar pencarian dan masukkan jumlah yang benar.', 'warning'); 
        return;
    }

    // Cek apakah barang sudah ada di keranjang
    const existIdx = cart.findIndex(c => c.material_id == mat_id);
    if(existIdx !== -1) {
        cart[existIdx].qty += qty;
        cart[existIdx].notes = notes ? notes : cart[existIdx].notes; // Update notes jika diisi
    } else {
        cart.push({ material_id: mat_id, name: mat_name, qty: qty, unit: unit, notes: notes });
    }

    // Reset Form Input (Kecuali form action)
    document.getElementById('form-item').reset();
    document.getElementById('material_id').value = '';
    document.getElementById('unit_label').value = '';
    
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cart-table');
    document.getElementById('cart-count').innerText = cart.length;

    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="p-10 text-center text-slate-400 italic text-xs font-bold">Belum ada barang di daftar.</td></tr>';
        return;
    }

    let html = '';
    cart.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4">
                    <div class="text-xs font-black text-slate-800">${item.name}</div>
                    ${item.notes ? `<div class="text-[10px] text-slate-500 italic mt-1 max-w-[150px] truncate" title="${item.notes}">Catatan: ${item.notes}</div>` : ''}
                </td>
                <td class="p-4 text-sm font-black text-blue-600 whitespace-nowrap">
                    ${item.qty} <span class="font-bold text-slate-400 text-[10px] uppercase tracking-widest">${item.unit}</span>
                </td>
                <td class="p-4 text-center">
                    <button type="button" onclick="removeFromCart(${idx})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto shadow-sm">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

async function submitCart() {
    if(cart.length === 0) { Swal.fire('Ups!', 'Daftar permintaan masih kosong.', 'warning'); return; }

    Swal.fire({ title: 'Mengirim Permintaan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('cart', JSON.stringify(cart));

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
        clearCart();
        
        // Pindah view kembali ke list dan refresh tabel
        const mainContainer = document.querySelector('main');
        mainContainer.__x.$data.view = 'list';
        loadData(1);
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
}

// ==========================================
// LOGIC LIST VIEW & TABEL
// ==========================================
function switchTab(tab) {
    currentTab = tab;
    loadData(1);
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500); 
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const res = await fetchAjax(`logic.php?action=read&search=${search}&tab=${currentTab}&start_date=${start_date}&end_date=${end_date}&page=${currentPage}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = `<tr><td colspan="6" class="p-10 text-center text-slate-400 italic text-sm font-bold">Belum ada transaksi di filter ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}) + ', ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                // Styling Badge Sesuai Gambar
                let statusBadge = '';
                if(item.status === 'pending') {
                    statusBadge = '<span class="text-amber-500 font-bold text-xs"><i class="fa-regular fa-clock"></i> Menunggu</span>';
                } else if(item.status === 'processing') {
                    statusBadge = '<span class="bg-blue-50 text-blue-500 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1 w-max mx-auto"><i class="fa-solid fa-cart-shopping"></i> Diproses PO</span>';
                } else if(item.status === 'rejected') {
                    statusBadge = '<span class="bg-rose-50 text-rose-500 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1 w-max mx-auto"><i class="fa-solid fa-xmark"></i> Ditolak/Batal</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5 text-xs text-slate-500 font-bold">${tgl}</td>
                        <td class="p-5 font-black text-slate-800 text-xs uppercase">${item.material_name}</td>
                        <td class="p-5 text-center text-xs font-black text-blue-600">${parseFloat(item.qty)} <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">${item.unit}</span></td>
                        <td class="p-5">
                            <div class="text-xs font-bold text-slate-600">${item.requester_name}</div>
                            <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Gudang Admin</div>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5">
                            <div class="text-xs text-slate-600 italic truncate max-w-[150px]" title="${item.notes || '-'}">${item.notes || '-'}</div>
                            ${item.po_id ? `<div class="text-[9px] font-bold text-blue-500 mt-1 uppercase tracking-widest">Terkait PO: PR-${item.po_id}</div>` : ''}
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    for (let i = 1; i <= totalPages; i++) {
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}