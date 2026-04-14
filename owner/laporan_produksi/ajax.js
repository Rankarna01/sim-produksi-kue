let currentPage = 1;

function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", () => {
    initFilterGudang(); 
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadLaporan(1); 
});

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-secondary');
    });

    document.getElementById(tabId).classList.remove('hidden');
    
    const activeBtn = document.getElementById(`btn-${tabId}`);
    activeBtn.classList.remove('border-transparent', 'text-secondary');
    activeBtn.classList.add('border-primary', 'text-primary');

    let tabName = activeBtn.innerText.trim();
    document.getElementById('print-tab-name').innerText = tabName;
}

// Inisiasi Filter Dropdown Dapur & Store
async function initFilterGudang() {
    const response = await fetchAjax('logic.php?action=init_filter', 'GET');
    if (response.status === 'success') {
        let optStore = '<option value="">Semua Store</option>';
        response.warehouses.forEach(w => {
            optStore += `<option value="${w.id}">${w.name}</option>`;
        });
        document.getElementById('warehouse_filter').innerHTML = optStore;

        let optKitchen = '<option value="">Semua Dapur</option>';
        response.kitchens.forEach(k => {
            optKitchen += `<option value="${k.id}">${k.name}</option>`;
        });
        document.getElementById('kitchen_filter').innerHTML = optKitchen;
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('quick_filter').value = 'custom'; 
    loadLaporan(1);
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    document.getElementById('status').value = '';
    document.getElementById('warehouse_filter').value = '';
    document.getElementById('kitchen_filter').value = '';
    document.getElementById('quick_filter').value = 'this_month';
    loadLaporan(1);
}

function applyQuickFilter() {
    const filterType = document.getElementById('quick_filter').value;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const today = new Date();
    let start = '';
    let end = '';

    if (filterType === 'today') {
        start = end = today.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_week') {
        const first = today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1);
        const last = first + 6;
        const startDay = new Date(today.setDate(first));
        const endDay = new Date(today.setDate(last));
        start = startDay.toISOString().split('T')[0];
        end = endDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = firstDay.toISOString().split('T')[0];
        end = lastDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_year') {
        start = `${today.getFullYear()}-01-01`;
        end = `${today.getFullYear()}-12-31`;
    }

    if (filterType !== 'custom') {
        startDateInput.value = start;
        endDateInput.value = end;
        loadLaporan(1); 
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

async function loadLaporan(page = 1) {
    currentPage = page;
    const tbodyDetail = document.getElementById('table-laporan');
    const tbodyRekap = document.getElementById('table-rekap');
    const tbodyKaryawan = document.getElementById('table-rekap-karyawan');
    const bahanGrid = document.getElementById('bahan-grid'); 
    
    tbodyDetail.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    tbodyRekap.innerHTML = '<tr><td colspan="3" class="p-8 text-center text-secondary">Memuat rekap...</td></tr>';
    tbodyKaryawan.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary">Memuat rekap...</td></tr>';
    bahanGrid.innerHTML = '<div class="col-span-full p-8 text-center text-secondary">Memuat rincian bahan...</div>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouse = document.getElementById('warehouse_filter').value;
    const kitchen = document.getElementById('kitchen_filter').value;
    
    let statusText = status === '' ? 'Semua' : (status === 'masuk_gudang' ? 'Selesai' : status);
    document.getElementById('print-periode').innerText = `Periode: ${start || 'Awal'} s/d ${end || 'Akhir'} | Status: ${statusText.toUpperCase()}`;

    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouse}&kitchen_id=${kitchen}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        
        // Isi Infobox di layar Web
        document.getElementById('sum-total').innerHTML = `${formatNumber(response.summary.total)} <span class="text-sm font-semibold text-slate-500">Pcs</span>`;
        document.getElementById('sum-masuk').innerHTML = `${formatNumber(response.summary.masuk)} <span class="text-sm font-semibold text-success/70">Pcs</span>`;
        document.getElementById('sum-ditolak').innerHTML = `${formatNumber(response.summary.ditolak)} <span class="text-sm font-semibold text-danger/70">Pcs</span>`;
        document.getElementById('sum-expired').innerHTML = `${formatNumber(response.summary.expired)} <span class="text-sm font-semibold text-slate-400">Pcs</span>`;

        // Tab Rekap Produk
        let htmlRekap = '';
        if (response.rekap_produk.length === 0) {
            htmlRekap = '<tr><td colspan="3" class="p-8 text-center text-secondary font-medium">Tidak ada data.</td></tr>';
        } else {
            response.rekap_produk.forEach((item, index) => {
                htmlRekap += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800">${item.produk}</td>
                        <td class="p-4 text-center font-black text-primary">${formatNumber(item.total_qty)}</td>
                    </tr>
                `;
            });
        }
        tbodyRekap.innerHTML = htmlRekap;

        // Tab Rekap Karyawan (Ditambah Asal Dapur)
        let htmlKaryawan = '';
        if (response.rekap_karyawan.length === 0) {
            htmlKaryawan = '<tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Tidak ada data.</td></tr>';
        } else {
            response.rekap_karyawan.forEach((item, index) => {
                htmlKaryawan += `
                    <tr class="hover:bg-indigo-50/30 transition-colors border-b border-indigo-50">
                        <td class="p-4 text-center text-indigo-300 font-bold">${index + 1}</td>
                        <td class="p-4 font-bold text-indigo-900">${item.karyawan}</td>
                        <td class="p-4 text-slate-500 text-xs font-bold uppercase tracking-widest">${item.asal_dapur || '-'}</td>
                        <td class="p-4 font-semibold text-slate-700">${item.produk}</td>
                        <td class="p-4 text-center font-black text-indigo-600">${formatNumber(item.total_qty)}</td>
                    </tr>
                `;
            });
        }
        tbodyKaryawan.innerHTML = htmlKaryawan;

        // Tab Pemakaian Bahan (Card Grid)
        let htmlBahan = '';
        if (response.rekap_bahan.length === 0) {
            htmlBahan = '<div class="col-span-full p-8 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">Tidak ada pemakaian bahan baku pada periode ini.</div>';
        } else {
            response.rekap_bahan.forEach(group => {
                htmlBahan += `
                    <div class="border border-slate-200 rounded-2xl bg-white shadow-sm overflow-hidden flex flex-col">
                        <div class="bg-slate-50 border-b border-slate-200 p-4 flex justify-between items-center">
                            <h4 class="font-bold text-slate-800 text-sm">${group.produk}</h4>
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-lg text-xs font-bold border border-emerald-200">
                                Produksi: ${formatNumber(group.total_produksi)} Pcs
                            </span>
                        </div>
                        <div class="p-4 flex-1">
                            <ul class="space-y-3">
                `;
                group.materials.forEach(mat => {
                    htmlBahan += `
                        <li class="flex justify-between items-center text-sm border-b border-slate-50 pb-2 last:border-0 last:pb-0">
                            <span class="text-slate-600 font-medium">${mat.bahan}</span>
                            <span class="font-black text-slate-800">${formatNumber(mat.dipakai)} <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold ml-0.5">${mat.satuan}</span></span>
                        </li>
                    `;
                });
                htmlBahan += `</ul></div></div>`;
            });
        }
        bahanGrid.innerHTML = htmlBahan;

        // Tab Detail Histori (Ditambah Asal Dapur)
        let htmlDetail = '';
        if (response.data.length === 0) {
            htmlDetail = '<tr><td colspan="9" class="p-8 text-center text-secondary font-medium">Tidak ada data histori.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 10 + index + 1;
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let statusBadge = '';
                if (item.status === 'pending') {
                    statusBadge = `<span class="text-accent bg-accent/10 px-3 py-1 rounded-full text-xs font-bold flex items-center justify-center gap-1 w-24 mx-auto"><i class="fa-solid fa-clock"></i> Pending</span>`;
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="text-danger bg-danger/10 px-3 py-1 rounded-full text-xs font-bold flex items-center justify-center gap-1 w-24 mx-auto"><i class="fa-solid fa-xmark"></i> Ditolak</span>`;
                } else if (item.status === 'expired') {
                    statusBadge = `<span class="text-slate-500 bg-slate-200 px-3 py-1 rounded-full text-xs font-bold flex items-center justify-center gap-1 w-24 mx-auto"><i class="fa-solid fa-ban"></i> Expired</span>`;
                } else {
                    statusBadge = `<span class="text-success bg-success/10 px-3 py-1 rounded-full text-xs font-bold flex items-center justify-center gap-1 w-24 mx-auto"><i class="fa-solid fa-check"></i> Selesai</span>`;
                }

                htmlDetail += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-3 text-center text-slate-400 text-xs">${no}</td>
                        <td class="p-3">
                            <div class="font-semibold">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-mono text-xs font-bold text-primary">${item.invoice_no}</td>
                        <td class="p-3 text-xs font-bold uppercase tracking-widest text-slate-500">${item.asal_dapur || '-'}</td>
                        <td class="p-3 font-medium text-sm">${item.karyawan}</td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.produk}</td>
                        <td class="p-3 text-center font-black text-slate-800 text-base">${formatNumber(item.quantity)}</td>
                        <td class="p-3 text-center">${statusBadge}</td>
                        <td class="p-3 text-xs font-semibold text-slate-600">${item.gudang || '-'}</td>
                    </tr>
                `;
            });
        }
        tbodyDetail.innerHTML = htmlDetail;
        renderPagination(response.total_pages, response.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    
    if (totalPages === 0) totalPages = 1;

    html += `<button type="button" ${current > 1 ? `onclick="loadLaporan(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left mr-1"></i> Prev</button>`;

    let startPage = Math.max(1, current - 1);
    let endPage = Math.min(totalPages, current + 1);

    if (current === 1) endPage = Math.min(3, totalPages);
    if (current === totalPages) startPage = Math.max(1, totalPages - 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadLaporan(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadLaporan(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm">Next <i class="fa-solid fa-chevron-right ml-1"></i></button>`;

    container.innerHTML = html;
}

// ===========================================================================
// CETAK PDF (4 KOLOM INFOBOX)
// ===========================================================================
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Dokumen...', text: 'Mengambil seluruh data...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const activeTabId = document.querySelector('.tab-btn.text-primary').id;
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouse = document.getElementById('warehouse_filter').value;
    const kitchen = document.getElementById('kitchen_filter').value;
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouse}&kitchen_id=${kitchen}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const wrapper = document.getElementById('print-table-wrapper');
        let htmlPrint = '';

        document.getElementById('print-sum-total').innerText = `${formatNumber(response.summary.total)} Pcs`;
        document.getElementById('print-sum-masuk').innerText = `${formatNumber(response.summary.masuk)} Pcs`;
        document.getElementById('print-sum-ditolak').innerText = `${formatNumber(response.summary.ditolak)} Pcs`;
        document.getElementById('print-sum-expired').innerText = `${formatNumber(response.summary.expired)} Pcs`;

        if (activeTabId === 'btn-tab-detail') {
            htmlPrint = `<table><thead><tr><th>No</th><th>Waktu Produksi</th><th>No. Invoice</th><th>Asal Dapur</th><th>Karyawan</th><th>Produk</th><th>Qty</th><th>Status</th><th>Store Tujuan</th></tr></thead><tbody>`;
            response.data.forEach((item, index) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const st = item.status === 'masuk_gudang' ? 'Selesai' : (item.status === 'ditolak' ? 'Ditolak' : (item.status === 'expired' ? 'Expired' : 'Pending'));
                
                htmlPrint += `<tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>${tgl}</td>
                    <td>${item.invoice_no}</td>
                    <td>${item.asal_dapur || '-'}</td>
                    <td>${item.karyawan}</td>
                    <td>${item.produk}</td>
                    <td style="text-align:center; font-weight:bold;">${formatNumber(item.quantity)}</td>
                    <td style="text-align:center;"><span class="print-badge">${st}</span></td>
                    <td>${item.gudang || '-'}</td>
                </tr>`;
            });
            htmlPrint += `</tbody></table>`;
        } 
        else if (activeTabId === 'btn-tab-rekap-produk') {
            htmlPrint = `<table><thead><tr><th>No</th><th>Nama Produk</th><th>Total (Pcs)</th></tr></thead><tbody>`;
            response.rekap_produk.forEach((item, index) => {
                htmlPrint += `<tr><td style="text-align:center;">${index + 1}</td><td>${item.produk}</td><td style="text-align:center; font-weight:bold;">${formatNumber(item.total_qty)}</td></tr>`;
            });
            htmlPrint += `</tbody></table>`;
        }
        else if (activeTabId === 'btn-tab-rekap-karyawan') {
            htmlPrint = `<table><thead><tr><th>No</th><th>Nama Karyawan</th><th>Asal Dapur</th><th>Produk</th><th>Total (Pcs)</th></tr></thead><tbody>`;
            response.rekap_karyawan.forEach((item, index) => {
                htmlPrint += `<tr><td style="text-align:center;">${index + 1}</td><td>${item.karyawan}</td><td>${item.asal_dapur || '-'}</td><td>${item.produk}</td><td style="text-align:center; font-weight:bold;">${formatNumber(item.total_qty)}</td></tr>`;
            });
            htmlPrint += `</tbody></table>`;
        }
        else if (activeTabId === 'btn-tab-pemakaian-bahan') {
            htmlPrint = `<table><thead><tr><th>Produk</th><th>Total Produksi</th><th>Rincian Bahan Baku Digunakan</th></tr></thead><tbody>`;
            response.rekap_bahan.forEach(group => {
                let bahanList = group.materials.map(m => `- ${m.bahan}: <b>${formatNumber(m.dipakai)} ${m.satuan}</b>`).join('<br>');
                htmlPrint += `<tr>
                    <td style="vertical-align: top; font-weight: bold;">${group.produk}</td>
                    <td style="vertical-align: top; text-align:center; font-weight: bold; color: #059669;">${formatNumber(group.total_produksi)} Pcs</td>
                    <td style="vertical-align: top; line-height: 1.6;">${bahanList}</td>
                </tr>`;
            });
            htmlPrint += `</tbody></table>`;
        }

        wrapper.innerHTML = htmlPrint;
        Swal.close();

        setTimeout(() => { window.print(); }, 500);

    } else {
        Swal.fire('Error', 'Gagal memuat data cetak', 'error');
    }
}

function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouse = document.getElementById('warehouse_filter').value;
    const kitchen = document.getElementById('kitchen_filter').value;
    window.location.href = `logic.php?action=export_excel&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouse}&kitchen_id=${kitchen}`; 
}