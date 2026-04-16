let currentStatus = 'pending'; 
let currentPage = 1;

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
    loadData(1); 
});

async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectStore = document.getElementById('warehouse_id');
            let optStore = '<option value="">Semua Store</option>';
            response.warehouses.forEach(w => {
                optStore += `<option value="${w.id}">${w.name}</option>`;
            });
            if(selectStore) selectStore.innerHTML = optStore;

            // Jika dia bukan admin produksi (punya dropdown dapur)
            const selectKitchen = document.getElementById('kitchen_id');
            if(selectKitchen && selectKitchen.tagName === 'SELECT') {
                let optKitchen = '<option value="">Semua Dapur</option>';
                response.kitchens.forEach(k => {
                    optKitchen += `<option value="${k.id}">${k.name}</option>`;
                });
                selectKitchen.innerHTML = optKitchen;
            }
        }
    } catch (e) {
        console.error("Gagal memuat filter data");
    }
}

function switchTab(status) {
    currentStatus = status;
    currentPage = 1; 
    
    const tabs = ['pending', 'masuk_gudang', 'ditolak', 'dibatalkan', 'expired'];
    tabs.forEach(t => {
        const btn = document.getElementById(`tab-btn-${t}`);
        if(btn) {
            btn.classList.remove('border-accent', 'text-accent', 'border-success', 'text-success', 'border-danger', 'text-danger', 'border-slate-500', 'text-slate-600');
            btn.classList.add('border-transparent', 'text-secondary');
        }
    });

    const activeBtn = document.getElementById(`tab-btn-${status}`);
    if(activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-secondary');
        
        if(status === 'pending') activeBtn.classList.add('border-accent', 'text-accent');
        else if(status === 'masuk_gudang') activeBtn.classList.add('border-success', 'text-success');
        else if(status === 'ditolak') activeBtn.classList.add('border-danger', 'text-danger');
        else if(status === 'dibatalkan') activeBtn.classList.add('border-slate-500', 'text-slate-600');
        else if(status === 'expired') activeBtn.classList.add('border-slate-500', 'text-slate-600');
    }

    loadData(1);
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault(); 
    loadData(1);
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    document.getElementById('warehouse_id').value = '';
    const selectKitchen = document.getElementById('kitchen_id');
    if(selectKitchen && selectKitchen.tagName === 'SELECT') selectKitchen.value = '';
    loadData(1);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=read&status=${currentStatus}&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = `<tr><td colspan="7" class="p-8 text-center text-secondary font-medium">Tidak ada data untuk status ini.</td></tr>`;
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 15 + index + 1;
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let statusBadge = '';
                
                if (item.status === 'pending') {
                    statusBadge = `<span class="bg-accent/10 text-accent px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-clock"></i> Pending</span>`;
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="bg-danger/10 text-danger px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-rotate-left"></i> Ditolak Admin</span>`;
                } else if (item.status === 'dibatalkan') {
                    statusBadge = `<span class="bg-slate-200 text-slate-500 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-trash-can"></i> Batal / Dihapus</span>`;
                } else if (item.status === 'expired') {
                    statusBadge = `<span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-ban"></i> Expired</span>`;
                } else {
                    statusBadge = `<span class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-check-double"></i> Selesai</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 font-bold text-sm">${no}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary">${jam} WIB</div>
                        </td>
                        <td class="p-4 font-mono font-bold text-primary">${item.invoice_no}</td>
                        <td class="p-4 text-xs font-bold uppercase tracking-widest text-slate-500">${item.asal_dapur || '-'}</td>
                        <td class="p-4 font-medium">${item.karyawan}</td>
                        <td class="p-4 font-bold text-slate-800">${item.produk}</td>
                        <td class="p-4 text-center font-black text-lg text-slate-800">
                            ${formatNumber(item.quantity)}<br>
                            ${statusBadge}
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    } else {
        tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-danger font-medium">Terjadi kesalahan sistem.</td></tr>`;
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages === 0) totalPages = 1;

    html += `<button type="button" ${current > 1 ? `onclick="loadData(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold shadow-sm"><i class="fa-solid fa-chevron-left mr-1"></i> Prev</button>`;

    let startPage = Math.max(1, current - 1);
    let endPage = Math.min(totalPages, current + 1);

    if (current === 1) endPage = Math.min(3, totalPages);
    if (current === totalPages) startPage = Math.max(1, totalPages - 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadData(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadData(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold shadow-sm">Next <i class="fa-solid fa-chevron-right ml-1"></i></button>`;
    container.innerHTML = html;
}

async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Data...', text: 'Mengekstrak seluruh halaman...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=read&status=${currentStatus}&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let labelStatus = '';
        if(currentStatus === 'masuk_gudang') labelStatus = 'SELESAI (MASUK STORE)';
        else if(currentStatus === 'dibatalkan') labelStatus = 'DIBATALKAN (REFUND STOK)';
        else labelStatus = currentStatus.toUpperCase();

        const warehouseSelect = document.getElementById('warehouse_id');
        const warehouseName = warehouseId ? warehouseSelect.options[warehouseSelect.selectedIndex].text : 'Semua Store';

        document.getElementById('print-subtitle').innerText = `Status Data: ${labelStatus}`;
        document.getElementById('print-periode').innerText = `Filter Tanggal: ${start || 'Awal'} s/d ${end || 'Akhir'} | Lokasi: ${warehouseName.toUpperCase()}`;

        let htmlPrint = `<table>
                            <thead>
                                <tr>
                                    <th style="width:40px; text-align:center;">No</th>
                                    <th>Waktu</th>
                                    <th>No. Invoice</th>
                                    <th>Asal Dapur</th>
                                    <th>Pembuat</th>
                                    <th>Produk</th>
                                    <th style="text-align:center;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>`;
        
        if(response.data.length === 0){
             htmlPrint += `<tr><td colspan="7" style="text-align:center; padding:20px;">Tidak ada data.</td></tr>`;
        } else {
            response.data.forEach((item, i) => {
                htmlPrint += `
                    <tr>
                        <td style="text-align:center;">${i + 1}</td>
                        <td>${item.created_at}</td>
                        <td>${item.invoice_no}</td>
                        <td>${item.asal_dapur || '-'}</td>
                        <td>${item.karyawan}</td>
                        <td>${item.produk}</td>
                        <td style="text-align:center; font-weight:bold;">${formatNumber(item.quantity)}</td>
                    </tr>
                `;
            });
        }
        htmlPrint += `</tbody></table>`;
        
        document.getElementById('print-table-wrapper').innerHTML = htmlPrint;
        Swal.close();

        setTimeout(() => { window.print(); }, 500);
    } else {
        Swal.fire('Error', 'Gagal menyiapkan data cetak', 'error');
    }
}

function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    window.location.href = `logic.php?action=export_excel&status=${currentStatus}&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}`;
}