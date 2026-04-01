let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    initFilterGudang(); // Tarik data gudang dulu
    applyQuickFilter(); // Baru load tabelnya
});

// Load Dropdown Gudang
async function initFilterGudang() {
    const response = await fetchAjax('logic.php?action=init_filter', 'GET');
    if (response.status === 'success') {
        let opt = '<option value="">Semua Gudang</option>';
        response.warehouses.forEach(w => {
            opt += `<option value="${w.id}">${w.name}</option>`;
        });
        document.getElementById('warehouse_filter').innerHTML = opt;
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('quick_filter').value = 'custom'; 
    loadLaporan(1);
});

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

// Fungsi Format Angka Ribuan
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

async function loadLaporan(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouse = document.getElementById('warehouse_filter').value; // Tangkap nilai gudang
    
    document.getElementById('print-periode').innerText = `Periode: ${start || 'Awal'} s/d ${end || 'Akhir'} | Status: ${status === '' ? 'Semua' : status.toUpperCase()}`;

    // Lempar warehouse_id ke backend
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouse}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        
        // 1. UPDATE KARTU RINGKASAN (SUMMARY CARDS)
        document.getElementById('sum-total').innerHTML = `${formatNumber(response.summary.total)} <span class="text-sm font-semibold text-slate-500">Pcs</span>`;
        document.getElementById('sum-masuk').innerHTML = `${formatNumber(response.summary.masuk)} <span class="text-sm font-semibold text-success/70">Pcs</span>`;
        document.getElementById('sum-gagal').innerHTML = `${formatNumber(response.summary.gagal)} <span class="text-sm font-semibold text-danger/70">Pcs</span>`;

        // 2. RENDER TABEL
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="8" class="p-8 text-center text-secondary font-medium">Tidak ada data produksi pada periode ini.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 10 + index + 1;
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let statusBadge = '';
                if (item.status === 'pending') {
                    statusBadge = `<span class="bg-accent/10 text-accent border border-accent/20 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">Pending</span>`;
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="bg-danger/10 text-danger border border-danger/20 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">Ditolak</span>`;
                } else if (item.status === 'expired') {
                    statusBadge = `<span class="bg-slate-200 text-slate-600 border border-slate-300 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">Expired</span>`;
                } else {
                    statusBadge = `<span class="bg-success/10 text-success border border-success/20 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">Selesai</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-3 text-center text-slate-400 text-xs">${no}</td>
                        <td class="p-3">
                            <div class="font-semibold">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-mono text-xs font-bold text-primary">${item.invoice_no}</td>
                        <td class="p-3 font-medium text-sm">${item.karyawan}</td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.produk}</td>
                        <td class="p-3 text-right font-black text-slate-800 text-base print:text-black">${formatNumber(item.quantity)}</td>
                        <td class="p-3 text-center">${statusBadge}</td>
                        <td class="p-3 text-xs font-semibold text-slate-600">${item.gudang}</td>
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
    
    if (totalPages === 0) totalPages = 1;

    if (current > 1) {
        html += `<button type="button" onclick="loadLaporan(${current - 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    } else {
        html += `<button type="button" disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            if (i === 1 || i === totalPages || (i >= current - 1 && i <= current + 1)) {
                html += `<button type="button" onclick="loadLaporan(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
            } else if (i === current - 2 || i === current + 2) {
                html += `<span class="px-2 text-slate-400">...</span>`;
            }
        }
    }

    if (current < totalPages) {
        html += `<button type="button" onclick="loadLaporan(${current + 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    } else {
        html += `<button type="button" disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    }

    container.innerHTML = html;
}

function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    const warehouse = document.getElementById('warehouse_filter').value;
    
    const url = `logic.php?action=export_excel&start_date=${start}&end_date=${end}&status=${status}&warehouse_id=${warehouse}`;
    window.location.href = url; 
}