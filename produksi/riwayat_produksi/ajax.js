let currentPage = 1;
let currentPin = ""; // Menampung PIN sementara

function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadFilterGudang(); 
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadHistory(1);
});

// --- LOGIKA NUMPAD PIN ---
function pressPin(num) {
    if (currentPin.length < 6) {
        currentPin += num;
        document.getElementById('pin-display').value = currentPin;
    }
}
function clearPin() { currentPin = ""; document.getElementById('pin-display').value = ""; }
function backspacePin() { currentPin = currentPin.slice(0, -1); document.getElementById('pin-display').value = currentPin; }
function closePinModal() { document.getElementById('modal-pin-supervisor').classList.add('hidden'); }

// --- LOGIKA FILTER & HISTORY (SAMA DENGAN SEBELUMNYA) ---
async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectGudang = document.getElementById('warehouse_id');
            let options = '<option value="">Semua Gudang</option>';
            response.warehouses.forEach(w => { options += `<option value="${w.id}">${w.name}</option>`; });
            selectGudang.innerHTML = options;
        }
    } catch (e) { console.error("Gagal memuat filter gudang"); }
}

document.getElementById('formFilter').addEventListener('submit', function(e) { e.preventDefault(); loadHistory(1); });
function resetFilter() {
    document.getElementById('formFilter').reset();
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadHistory(1);
}

async function loadHistory(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-history');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouseId}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary font-medium">Tidak ada data ditemukan pada filter ini.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 10 + index + 1;
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const namaGudang = item.gudang ? `<div class="text-[10px] text-primary font-bold mt-1 uppercase">Tujuan: ${item.gudang}</div>` : '';

                let statusBadge = '';
                let actionButtons = '';
                let btnPrint = `<button onclick="cetakUlangStruk(${item.prod_id})" title="Print Struk" class="bg-slate-800 hover:bg-slate-900 text-white w-9 h-9 rounded-lg flex items-center justify-center shadow-md"><i class="fa-solid fa-print text-xs"></i></button>`;
                let btnBatal = `<button onclick="triggerBatalkan(${item.prod_id})" title="Batalkan 1 Invoice" class="bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-danger w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-sm"><i class="fa-solid fa-trash text-xs"></i></button>`;
                let btnEdit = `<button onclick="bukaEdit(${item.prod_id})" title="Revisi Data" class="bg-danger hover:bg-red-700 text-white w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-md"><i class="fa-solid fa-pen text-xs"></i></button>`;

                if (item.status === 'pending') {
                    statusBadge = `<span class="bg-accent/10 text-accent px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-clock"></i> Pending</span>`;
                    actionButtons = btnPrint + btnBatal; 
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="bg-danger/10 text-danger px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1 animate-pulse"><i class="fa-solid fa-triangle-exclamation"></i> Ditolak</span>`;
                    actionButtons = btnPrint + btnEdit + btnBatal;
                } else {
                    // Masuk Gudang, Dibatalkan, Expired
                    let icon = item.status === 'masuk_gudang' ? 'fa-check-double' : (item.status === 'expired' ? 'fa-ban' : 'fa-trash-can');
                    let color = item.status === 'masuk_gudang' ? 'text-success' : 'text-slate-500';
                    statusBadge = `<span class="bg-slate-100 ${color} px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid ${icon}"></i> ${item.status.replace('_', ' ')}</span>`;
                    actionButtons = btnPrint; 
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${no}</td>
                        <td class="p-4"><div class="font-semibold text-slate-700">${tgl}</div><div class="text-xs text-secondary">${waktu} WIB</div>${namaGudang}</td>
                        <td class="p-4 font-mono text-sm text-slate-600 font-bold">${item.invoice_no}</td>
                        <td class="p-4 text-sm text-slate-700 leading-relaxed">${item.product_list.replace(/, /g, '<br>')}</td>
                        <td class="p-4 text-center font-black text-primary text-xl">${item.total_qty}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4 text-center"><div class="flex items-center justify-center gap-2">${actionButtons}</div></td>
                    </tr>`;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    }
}

// --- LOGIKA MODAL EDIT/REVISI (SAMA DENGAN SEBELUMNYA) ---
async function bukaEdit(prod_id) {
    document.getElementById('edit_prod_id').value = prod_id;
    const container = document.getElementById('edit-produk-list');
    container.innerHTML = '<div class="text-center py-8 text-secondary"><i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2 block"></i> Memuat detail...</div>';
    openModal('modal-edit');
    const res = await fetchAjax(`logic.php?action=get_details&prod_id=${prod_id}`, 'GET');
    if (res.status === 'success') {
        let htmlList = '';
        res.data.forEach((item, index) => {
            htmlList += `<div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4 shadow-sm"><input type="hidden" name="detail_id[]" value="${item.detail_id}"><div class="flex-1"><label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Produk ${index+1}</label><div class="font-bold text-slate-800 text-sm">${item.product_name}</div></div><div class="w-24 shrink-0"><label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider text-center">Qty (Pcs)</label><input type="number" name="quantity[]" value="${item.quantity}" required min="1" class="w-full px-2 py-2 border-2 border-slate-200 rounded-lg focus:border-primary outline-none font-black text-primary text-center text-lg bg-slate-50 focus:bg-white transition-colors"></div></div>`;
        });
        container.innerHTML = htmlList;
    }
}
document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=update_revisi', 'POST', formData);
    if (response.status === 'success') { Swal.fire('Berhasil!', response.message, 'success'); closeModal('modal-edit'); loadHistory(currentPage); }
    else { Swal.fire('Gagal!', response.message, 'error'); }
});

// --- LOGIKA BATALKAN DENGAN PIN ---
function triggerBatalkan(id) {
    document.getElementById('temp_prod_id').value = id;
    clearPin();
    document.getElementById('modal-pin-supervisor').classList.remove('hidden');
}

async function confirmCancelWithPin() {
    const prod_id = document.getElementById('temp_prod_id').value;
    if (currentPin.length === 0) { Swal.fire('Peringatan', 'Masukkan PIN terlebih dahulu!', 'warning'); return; }

    const btn = document.getElementById('btn-verify-pin');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> MEMPROSES...';

    const formData = new FormData();
    formData.append('prod_id', prod_id);
    formData.append('pin', currentPin);

    const response = await fetchAjax('logic.php?action=cancel_produksi', 'POST', formData);
    
    btn.disabled = false;
    btn.innerHTML = originalText;

    if (response.status === 'success') {
        Swal.fire('Dibatalkan!', response.message, 'success');
        closePinModal();
        loadHistory(currentPage);
    } else {
        Swal.fire('Gagal!', response.message, 'error');
        clearPin();
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = ''; if (totalPages === 0) totalPages = 1;
    if (current > 1) html += `<button onclick="loadHistory(${current - 1})" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold">Prev</button>`;
    for (let i = 1; i <= totalPages; i++) {
        if (i === current) html += `<button class="px-4 py-2 bg-primary text-white border border-primary rounded-lg text-sm font-bold">${i}</button>`;
        else if (i === 1 || i === totalPages || (i >= current - 1 && i <= current + 1)) html += `<button onclick="loadHistory(${i})" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold">${i}</button>`;
    }
    if (current < totalPages) html += `<button onclick="loadHistory(${current + 1})" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold">Next</button>`;
    container.innerHTML = html;
}

function cetakUlangStruk(id) { window.open(`../input_produksi/print.php?id=${id}`, 'CetakStruk', 'width=400,height=600'); }