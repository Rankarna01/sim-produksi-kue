let currentTab = 'semua';
let currentPage = 1;
let searchTimeout = null;
let materialsData = [];
let draftMasuk = []; // Array penampung keranjang barang masuk

document.addEventListener("DOMContentLoaded", async () => {
    await initFormDropdowns();
    loadData(1);
});

async function initFormDropdowns() {
    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;

        let optSupp = '<option value="">Tanpa Supplier (Lain-lain / Bonus)</option>';
        res.suppliers.forEach(s => { optSupp += `<option value="${s.id}">${s.name}</option>`; });
        document.getElementById('supplier_id').innerHTML = optSupp;
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
        document.getElementById('satuan_label').value = '-';
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
        div.className = "p-3 border-b border-slate-50 hover:bg-blue-50 cursor-pointer transition-colors";
        div.innerHTML = `
            <div class="font-black text-slate-800 text-xs">${m.material_name}</div>
            <div class="text-[10px] text-slate-500 font-mono font-bold mt-0.5">[${m.sku_code}] • Beli: ${m.unit}</div>
        `;
        div.onclick = () => { pilihMaterial(m.id, m.material_name, m.unit); };
        listContainer.appendChild(div);
    });

    listContainer.classList.remove('hidden');
}

function pilihMaterial(id, name, unit) {
    document.getElementById('material_id').value = id;
    document.getElementById('search_material').value = name;
    document.getElementById('satuan_label').value = unit;
    document.getElementById('material_list').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('search_material');
    const listContainer = document.getElementById('material_list');
    if (e.target !== searchInput && !listContainer.contains(e.target)) {
        listContainer.classList.add('hidden');
    }
});

// ===============================================
// FITUR KERANJANG DRAFT
// ===============================================
function tambahKeDraft() {
    const matId = document.getElementById('material_id').value;
    const matName = document.getElementById('search_material').value;
    const unit = document.getElementById('satuan_label').value;
    const qty = parseFloat(document.getElementById('qty').value);
    const expDate = document.getElementById('expiry_date').value;

    if (!matId || isNaN(qty) || qty <= 0 || !expDate) {
        Swal.fire('Ups!', 'Silakan pilih barang, isi jumlah masuk, dan tanggal kadaluarsa terlebih dahulu!', 'warning');
        return;
    }

    // Gabungkan jika barang yang sama dengan expired date yang sama sudah ada di keranjang
    const existIdx = draftMasuk.findIndex(d => d.material_id == matId && d.expiry_date == expDate);
    if (existIdx !== -1) {
        draftMasuk[existIdx].qty += qty;
    } else {
        draftMasuk.push({ material_id: matId, material_name: matName, unit: unit, qty: qty, expiry_date: expDate });
    }

    // Reset Inputan
    document.getElementById('material_id').value = '';
    document.getElementById('search_material').value = '';
    document.getElementById('qty').value = '';
    document.getElementById('satuan_label').value = '-';
    // Expiry date tidak direset, biar user gampang kalau barang selanjutnya punya tgl exp sama
    
    renderDraft();
}

function hapusDraft(index) {
    draftMasuk.splice(index, 1);
    renderDraft();
}

function renderDraft() {
    const tbody = document.getElementById('table-draft');
    document.getElementById('draft-count').innerText = `${draftMasuk.length} ITEM`;

    if (draftMasuk.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center text-slate-400 italic font-bold text-xs">Belum ada barang ditambahkan.</td></tr>';
        return;
    }

    let html = '';
    draftMasuk.forEach((item, idx) => {
        const d = new Date(item.expiry_date);
        const tglExp = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});

        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-3 pl-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                <td class="p-3 font-black text-slate-700 text-xs">${item.material_name}</td>
                <td class="p-3 text-xs font-bold text-rose-500">${tglExp}</td>
                <td class="p-3 text-right">
                    <span class="font-black text-emerald-600 text-sm">+${item.qty}</span>
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
    if (draftMasuk.length === 0) {
        Swal.fire('Ups!', 'Daftar barang masuk masih kosong. Tambahkan minimal 1 barang ke daftar.', 'warning');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Ajukan Transaksi?',
        text: "Pastikan data barang, jumlah, dan tanggal kadaluarsa sudah benar.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Ya, Ajukan!'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

        const supplier_id = document.getElementById('supplier_id').value;
        const notes = document.getElementById('notes').value;

        const formData = new FormData();
        formData.append('supplier_id', supplier_id);
        formData.append('notes', notes);
        formData.append('drafts', JSON.stringify(draftMasuk));

        const res = await fetchAjax('logic.php?action=save', 'POST', formData);
        
        if (res.status === 'success') {
            closeModal('modal-masuk');
            loadData(1); 
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

// ===============================================
// GLOBAL LOGIC LAMA (TETAP AMAN)
// ===============================================
function openModalMasuk() {
    draftMasuk = []; // Reset keranjang
    renderDraft();
    
    document.getElementById('material_id').value = '';
    document.getElementById('search_material').value = '';
    document.getElementById('qty').value = '';
    document.getElementById('satuan_label').value = '-';
    document.getElementById('expiry_date').value = '';
    document.getElementById('supplier_id').value = '';
    document.getElementById('notes').value = '';
    
    openModal('modal-masuk');
}
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function switchTab(tab) { currentTab = tab; loadData(1); }

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
            html = `<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Belum ada transaksi masuk di filter ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                let sourceBadge = item.source === 'PO' ? 
                    '<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">PO System</span>' : 
                    '<span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">Manual</span>';

                let statusVal = item.status || 'approved'; 
                let statusBadge = '';
                if(statusVal === 'pending') statusBadge = '<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Pending</span>';
                else if(statusVal === 'rejected') statusBadge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';
                else statusBadge = '<span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Approved</span>';

                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                let safeSupplier = (item.supplier_name || 'Lain-lain').replace(/'/g, "&apos;");

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-xs">${tgl}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-1">${item.transaction_no}</div>
                        </td>
                        <td class="p-5 font-black text-slate-800 uppercase text-xs">${item.material_name}</td>
                        <td class="p-5 text-center">${sourceBadge}</td>
                        <td class="p-5 text-xs font-bold text-slate-600">${safeSupplier}</td>
                        <td class="p-5 text-center">
                            <div class="inline-flex items-center gap-1 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                <span class="font-black text-emerald-600 text-sm">+${parseFloat(item.qty)}</span>
                                <span class="text-[10px] font-bold text-emerald-400 uppercase">${item.unit}</span>
                            </div>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-xs text-slate-500 italic max-w-[130px] truncate">${safeNotes}</span>
                                <button onclick="lihatDetail('${item.source}', '${safeSupplier}', '${safeNotes}')" class="text-[10px] bg-slate-100 hover:bg-blue-100 text-blue-600 px-2 py-1 rounded font-black transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-expand"></i> Detail
                                </button>
                            </div>
                        </td>
                        <td class="p-5 text-xs font-bold text-slate-600">${item.admin_name}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function lihatDetail(sumber, supplier, notes) {
    let iconPO = sumber === 'PO' ? '<i class="fa-solid fa-file-contract text-blue-500 mr-2"></i>' : '<i class="fa-solid fa-pen-to-square text-slate-400 mr-2"></i>';
    
    Swal.fire({
        title: 'Detail Informasi Masuk',
        html: `
            <div class="text-left space-y-4 text-sm mt-4">
                <div class="flex gap-4">
                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Sumber Masuk</span>
                        <span class="font-bold text-slate-800">${sumber}</span>
                    </div>
                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Supplier</span>
                        <span class="font-bold text-slate-800">${supplier}</span>
                    </div>
                </div>
                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                    <span class="block text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2">Keterangan / Nomor Dokumen PO</span>
                    <span class="font-bold text-slate-700 leading-relaxed">${iconPO} ${notes}</span>
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#3b82f6',
        customClass: { popup: 'rounded-3xl' }
    });
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