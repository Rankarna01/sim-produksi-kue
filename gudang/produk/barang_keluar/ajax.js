let currentTab = 'semua';
let currentPage = 1;
let searchTimeout = null;
let materialsData = [];
let draftKeluar = []; // Array keranjang barang keluar

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
        resetInputBahan();
        return;
    }

    const filtered = materialsData.filter(m => 
        m.material_name.toLowerCase().includes(keyword) || 
        m.sku_code.toLowerCase().includes(keyword)
    );

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="p-3 text-xs text-slate-400 italic font-bold">Barang tidak ditemukan.</div>`;
        listContainer.classList.remove('hidden');
        return;
    }

    filtered.forEach(m => {
        const div = document.createElement('div');
        div.className = "p-3 border-b border-slate-50 hover:bg-rose-50 cursor-pointer transition-colors";
        div.innerHTML = `
            <div class="font-black text-slate-800 text-xs">${m.material_name}</div>
            <div class="text-[10px] text-slate-500 font-mono font-bold mt-0.5">[${m.sku_code}] • Sisa Stok: <span class="${m.stock <= 0 ? 'text-rose-500' : 'text-emerald-500'}">${m.stock} ${m.unit}</span></div>
        `;
        div.onclick = () => { pilihMaterial(m.id, m.material_name, m.unit, m.stock); };
        listContainer.appendChild(div);
    });

    listContainer.classList.remove('hidden');
}

function pilihMaterial(id, name, unit, stock) {
    document.getElementById('material_id').value = id;
    document.getElementById('search_material').value = name;
    document.getElementById('satuan_label').value = unit;
    
    // Tampilkan Stok Info
    document.getElementById('max_stock').value = stock;
    const infoStok = document.getElementById('stock_info');
    infoStok.innerText = `Sisa Stok saat ini: ${stock} ${unit}`;
    infoStok.className = stock <= 0 ? 'text-[10px] text-rose-500 font-bold mt-1 pl-1' : 'text-[10px] text-emerald-600 font-bold mt-1 pl-1';
    
    document.getElementById('material_list').classList.add('hidden');
}

function resetInputBahan() {
    document.getElementById('material_id').value = '';
    document.getElementById('satuan_label').value = '-';
    document.getElementById('max_stock').value = '0';
    document.getElementById('stock_info').innerText = 'Pilih barang untuk melihat sisa stok.';
    document.getElementById('stock_info').className = 'text-[10px] text-slate-400 mt-1 pl-1 font-bold';
}

document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('search_material');
    const listContainer = document.getElementById('material_list');
    if (e.target !== searchInput && !listContainer.contains(e.target)) {
        listContainer.classList.add('hidden');
    }
});

// ===============================================
// FITUR KERANJANG DRAFT KELUAR
// ===============================================
function tambahKeDraft() {
    const matId = document.getElementById('material_id').value;
    const matName = document.getElementById('search_material').value;
    const unit = document.getElementById('satuan_label').value;
    const qty = parseFloat(document.getElementById('qty').value);
    const maxStock = parseFloat(document.getElementById('max_stock').value);

    if (!matId || isNaN(qty) || qty <= 0) {
        Swal.fire('Ups!', 'Silakan pilih barang dan isi jumlah keluar dengan benar!', 'warning');
        return;
    }

    // Validasi Stok Awal
    if (qty > maxStock) {
        Swal.fire('Stok Tidak Cukup!', `Anda ingin mengeluarkan ${qty}, tapi stok ${matName} hanya tersisa ${maxStock} ${unit}.`, 'error');
        return;
    }

    // Gabungkan jika barang yang sama sudah ada di keranjang, lalu validasi totalnya
    const existIdx = draftKeluar.findIndex(d => d.material_id == matId);
    if (existIdx !== -1) {
        const totalQty = draftKeluar[existIdx].qty + qty;
        if(totalQty > maxStock) {
            Swal.fire('Stok Tidak Cukup!', `Total ${matName} di daftar Anda (${totalQty}) melebihi stok yang ada (${maxStock} ${unit}).`, 'error');
            return;
        }
        draftKeluar[existIdx].qty = totalQty;
    } else {
        draftKeluar.push({ material_id: matId, material_name: matName, unit: unit, qty: qty });
    }

    // Reset Inputan
    document.getElementById('search_material').value = '';
    document.getElementById('qty').value = '';
    resetInputBahan();
    
    renderDraft();
}

function hapusDraft(index) {
    draftKeluar.splice(index, 1);
    renderDraft();
}

function renderDraft() {
    const tbody = document.getElementById('table-draft');
    document.getElementById('draft-count').innerText = `${draftKeluar.length} ITEM`;

    if (draftKeluar.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="p-6 text-center text-slate-400 italic font-bold text-xs">Belum ada barang ditambahkan.</td></tr>';
        return;
    }

    let html = '';
    draftKeluar.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-3 pl-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                <td class="p-3 font-black text-slate-700 text-xs">${item.material_name}</td>
                <td class="p-3 text-right">
                    <span class="font-black text-rose-600 text-sm">-${item.qty}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase ml-1">${item.unit}</span>
                </td>
                <td class="p-3 pr-5 text-center">
                    <button type="button" onclick="hapusDraft(${idx})" class="w-7 h-7 rounded bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all mx-auto flex items-center justify-center">
                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

async function simpanTransaksi() {
    if (draftKeluar.length === 0) {
        Swal.fire('Ups!', 'Daftar barang keluar masih kosong. Tambahkan minimal 1 barang ke daftar.', 'warning');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Ajukan Keluar Barang?',
        text: "Pastikan data barang, jumlah, dan alasan sudah benar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        confirmButtonText: 'Ya, Ajukan Keluar!'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

        const status = document.getElementById('status').value;
        const notes = document.getElementById('notes').value;

        const formData = new FormData();
        formData.append('status', status);
        formData.append('notes', notes);
        formData.append('drafts', JSON.stringify(draftKeluar));

        const res = await fetchAjax('logic.php?action=save', 'POST', formData);
        
        if (res.status === 'success') {
            closeModalKeluar();
            await initFormDropdowns(); // Tarik ulang data stok material terbaru
            loadData(1); 
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

// ===============================================
// GLOBAL LOGIC LAMA
// ===============================================
function openModalKeluar() {
    draftKeluar = []; // Reset keranjang
    renderDraft();
    
    document.getElementById('search_material').value = '';
    document.getElementById('qty').value = '';
    document.getElementById('status').value = 'Rusak';
    document.getElementById('notes').value = '';
    resetInputBahan();
    
    openModal('modal-keluar');
}
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function closeModalKeluar() { closeModal('modal-keluar'); }

function switchTab(tab) { currentTab = tab; loadData(currentPage); }

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500); 
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const res = await fetchAjax(`logic.php?action=read&search=${search}&tab=${currentTab}&start_date=${start_date}&end_date=${end_date}&page=${currentPage}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = `<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Belum ada transaksi keluar di filter ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                let statusBadge = '';
                if(item.status === 'Rusak') statusBadge = '<span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-rose-200 shadow-inner">Rusak</span>';
                else if(item.status === 'Expired') statusBadge = '<span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-amber-200 shadow-inner">Expired</span>';
                else statusBadge = '<span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-inner">Lainnya</span>';

                let approvalVal = item.approval_status || 'approved'; 
                let approvalBadge = '';
                if(approvalVal === 'pending') approvalBadge = '<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Pending</span>';
                else if(approvalVal === 'rejected') approvalBadge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';
                else approvalBadge = '<span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Approved</span>';

                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-xs">${tgl}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-1 font-mono">${item.transaction_no}</div>
                        </td>
                        <td class="p-5 uppercase text-xs">
                            <span class="font-black text-slate-800">${item.material_name}</span>
                            <span class="text-[9px] text-slate-400 block font-mono">#${item.sku_code}</span>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center font-black text-rose-600 text-lg">-${parseFloat(item.qty)}</td>
                        <td class="p-5 text-center text-slate-500 font-bold uppercase text-xs tracking-widest">${item.unit}</td>
                        <td class="p-5 text-center">${approvalBadge}</td>
                        <td class="p-5 text-xs text-slate-500 italic max-w-[180px] truncate" title="${safeNotes}">${safeNotes}</td>
                        <td class="p-5 text-xs font-bold text-slate-600">${item.admin_name}</td>
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

function cetakLaporan() {
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    window.open(`print.php?tab=${currentTab}&search=${search}&start_date=${start_date}&end_date=${end_date}`, '_blank');
}