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

        let optMat = '<option value="">-- Pilih Barang dari Inventory --</option>';
        res.materials.forEach(m => { 
            // Format dropdown sesuai gambar: Nama Barang (Stok: Satuan)
            const currentStock = parseFloat(m.stock);
            optMat += `<option value="${m.id}" data-unit="${m.unit}">
                ${m.material_name} (Stok: ${currentStock} ${m.unit})
            </option>`; 
        });
        document.getElementById('material_id').innerHTML = optMat;
    }
}

function updateSatuan() {
    const select = document.getElementById('material_id');
    const labelSatuan = document.getElementById('unit_label');
    
    if(!select.value) {
        labelSatuan.value = '';
        return;
    }
    const selectedOption = select.options[select.selectedIndex];
    labelSatuan.value = selectedOption.dataset.unit;
}

// ==========================================
// LOGIC CART (KERANJANG)
// ==========================================
function addToCart() {
    const select = document.getElementById('material_id');
    const mat_id = select.value;
    const qty = parseFloat(document.getElementById('qty').value);
    const notes = document.getElementById('notes').value;

    if (!mat_id || qty <= 0 || isNaN(qty)) {
        Swal.fire('Ups!', 'Pilih barang dan masukkan jumlah yang benar.', 'warning'); return;
    }

    const mat_name = select.options[select.selectedIndex].text.split(' (Stok:')[0]; // Ambil nama saja
    const unit = select.options[select.selectedIndex].dataset.unit;

    // Cek apakah barang sudah ada di keranjang
    const existIdx = cart.findIndex(c => c.material_id == mat_id);
    if(existIdx !== -1) {
        cart[existIdx].qty += qty;
        cart[existIdx].notes = notes ? notes : cart[existIdx].notes; // Update notes jika diisi
    } else {
        cart.push({ material_id: mat_id, name: mat_name, qty: qty, unit: unit, notes: notes });
    }

    // Reset Form
    document.getElementById('form-item').reset();
    updateSatuan();
    
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
        tbody.innerHTML = '<tr><td colspan="4" class="p-10 text-center text-slate-400 italic text-xs">Belum ada barang di daftar.</td></tr>';
        return;
    }

    let html = '';
    cart.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50">
                <td class="p-4 text-xs font-bold text-slate-700">${item.name}</td>
                <td class="p-4 text-xs font-black text-blue-600">${item.qty} <span class="font-bold text-slate-400 text-[10px]">${item.unit}</span></td>
                <td class="p-4 text-[10px] text-slate-500 italic max-w-[120px] truncate">${item.notes || '-'}</td>
                <td class="p-4 text-center">
                    <button type="button" onclick="removeFromCart(${idx})" class="w-6 h-6 rounded bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all">
                        <i class="fa-solid fa-trash text-[10px]"></i>
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
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const res = await fetchAjax(`logic.php?action=read&search=${search}&tab=${currentTab}&start_date=${start_date}&end_date=${end_date}&page=${currentPage}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = `<tr><td colspan="7" class="p-10 text-center text-slate-400 italic text-sm">Belum ada transaksi di tab ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                // Format: 16 Apr 2026, 12.15
                const tgl = d.toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}) + ', ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                // Styling Badge Sesuai Gambar
                let statusBadge = '';
                if(item.status === 'pending') {
                    statusBadge = '<span class="text-amber-500 font-bold text-xs"><i class="fa-regular fa-clock"></i> Menunggu</span>';
                } else if(item.status === 'processing') {
                    statusBadge = '<span class="bg-blue-50 text-blue-500 px-3 py-1.5 rounded-full text-xs font-black flex items-center justify-center gap-1 w-max mx-auto"><i class="fa-solid fa-cart-shopping"></i> Diproses PO</span>';
                } else if(item.status === 'rejected') {
                    statusBadge = '<span class="bg-rose-50 text-rose-500 px-3 py-1.5 rounded-full text-xs font-black flex items-center justify-center gap-1 w-max mx-auto"><i class="fa-regular fa-circle-xmark"></i> Ditolak/Batal</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5 text-xs text-slate-500 font-medium">${tgl}</td>
                        <td class="p-5 font-black text-slate-800 text-xs">${item.material_name}</td>
                        <td class="p-5 text-center text-xs font-medium text-slate-600">${parseFloat(item.qty).toFixed(2)} ${item.unit}</td>
                        <td class="p-5">
                            <div class="text-xs font-bold text-slate-600">${item.requester_name}</div>
                            <div class="text-[9px] text-slate-400 font-bold uppercase">Gudang</div>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5">
                            <div class="text-xs text-slate-600">${item.notes || '-'}</div>
                            ${item.po_id ? `<div class="text-[9px] font-bold text-blue-500 mt-1 uppercase">PO: PR-${item.po_id}</div>` : ''}
                        </td>
                        <td class="p-5 text-center">
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