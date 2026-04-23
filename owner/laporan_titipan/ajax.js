let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    initFilter();
    
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - 30);
    
    document.getElementById('end_date').value = end.toISOString().split('T')[0];
    document.getElementById('start_date').value = start.toISOString().split('T')[0];
    
    loadLaporan(1);
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init_filter', 'GET');
    if (res.status === 'success') {
        let optWarehouse = '<option value="">Semua Store</option>';
        res.warehouses.forEach(w => optWarehouse += `<option value="${w.id}">${w.name}</option>`);
        document.getElementById('filter_store').innerHTML = optWarehouse;
    }
}

const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadLaporan(1);
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - 30);
    document.getElementById('end_date').value = end.toISOString().split('T')[0];
    document.getElementById('start_date').value = start.toISOString().split('T')[0];
    loadLaporan(1);
}

function getFilterQuery() {
    return `&start_date=${document.getElementById('start_date').value}&end_date=${document.getElementById('end_date').value}&warehouse_id=${document.getElementById('filter_store').value}&status=${document.getElementById('filter_status').value}`;
}

async function loadLaporan(page) {
    currentPage = page;
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="10" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const url = `logic.php?action=read&page=${page}${getFilterQuery()}`;
    
    try {
        const res = await fetchAjax(url, 'GET');
        
        if (res.status === 'success') {
            document.getElementById('sum-qty').innerHTML = `${res.summary.qty} <span class="text-sm text-slate-500 font-bold">Pcs</span>`;
            document.getElementById('sum-omset').innerText = formatRupiah(res.summary.omset);
            document.getElementById('sum-profit').innerText = formatRupiah(res.summary.profit);

            let html = '';
            if (res.data.length === 0) {
                html = '<tr><td colspan="10" class="p-10 text-center text-slate-400 italic">Tidak ada data ditemukan pada filter ini.</td></tr>';
            } else {
                let startNum = (res.pagination.page - 1) * res.pagination.limit + 1;
                res.data.forEach(item => {
                    const dateArr = item.created_at.split(' ');
                    
                    let badgeStatus = '';
                    let dimClass = '';
                    if(item.status === 'pending') badgeStatus = '<span class="bg-amber-50 text-amber-600 px-2 py-1 rounded-md text-[10px] font-black uppercase"><i class="fa-solid fa-clock"></i> Pending</span>';
                    else if(item.status === 'received') badgeStatus = '<span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded-md text-[10px] font-black uppercase"><i class="fa-solid fa-check"></i> Valid</span>';
                    else if(item.status === 'ditolak') {
                        badgeStatus = '<span class="bg-rose-50 text-rose-600 px-2 py-1 rounded-md text-[10px] font-black uppercase"><i class="fa-solid fa-rotate-left"></i> Ditolak</span>';
                        dimClass = 'opacity-50';
                    }
                    else {
                        badgeStatus = '<span class="bg-slate-100 text-slate-500 px-2 py-1 rounded-md text-[10px] font-black uppercase"><i class="fa-solid fa-ban"></i> Batal</span>';
                        dimClass = 'opacity-50';
                    }

                    html += `
                        <tr class="hover:bg-slate-50 transition-colors ${dimClass}">
                            <td class="p-4 text-center font-mono text-slate-400 text-xs">${startNum++}</td>
                            <td class="p-4">
                                <span class="font-bold text-slate-800">${dateArr[0]}</span><br>
                                <span class="text-[10px] font-black text-slate-400">${item.invoice_no}</span>
                            </td>
                            <td class="p-4 text-center">${badgeStatus}</td>
                            <td class="p-4 font-bold text-blue-600 text-xs uppercase tracking-wider">${item.store_name || '-'}</td>
                            <td class="p-4">
                                <span class="font-black text-slate-700">${item.nama_barang}</span><br>
                                <span class="text-[10px] font-bold text-slate-400"><i class="fa-solid fa-shop mr-1"></i> ${item.nama_umkm}</span>
                            </td>
                            <td class="p-4 text-center font-black text-slate-800 text-lg">${item.quantity}</td>
                            <td class="p-4 text-right font-bold text-slate-500">${formatRupiah(item.harga_modal)}</td>
                            <td class="p-4 text-right font-bold text-emerald-600">${formatRupiah(item.harga_jual)}</td>
                            <td class="p-4 text-right font-black text-slate-800">${formatRupiah(item.total_omset)}</td>
                            <td class="p-4 text-right font-black text-amber-500">+${formatRupiah(item.profit)}</td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;
            renderPagination(res.pagination);
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="10" class="p-10 text-center text-rose-500">Gagal memuat data!</td></tr>';
    }
}

function renderPagination(pg) {
    const info = document.getElementById('page-info');
    const controls = document.getElementById('pagination-controls');
    
    if(pg.total_rows === 0) {
        info.innerText = "0 - 0 dari 0";
        controls.innerHTML = "";
        return;
    }

    const start = (pg.page - 1) * pg.limit + 1;
    const end = Math.min(pg.page * pg.limit, pg.total_rows);
    info.innerText = `${start} - ${end} dari ${pg.total_rows}`;

    let btnHtml = '';
    if (pg.page > 1) {
        btnHtml += `<button onclick="loadLaporan(${pg.page - 1})" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-100">&laquo; Prev</button>`;
    }
    if (pg.page < pg.total_pages) {
        btnHtml += `<button onclick="loadLaporan(${pg.page + 1})" class="px-3 py-1 bg-white border border-slate-200 rounded hover:bg-slate-100">Next &raquo;</button>`;
    }
    controls.innerHTML = btnHtml;
}

function exportExcel() { window.location.href = `logic.php?action=export_excel${getFilterQuery()}`; }
function printPDF() { window.open(`print_laporan.php?${getFilterQuery().substring(1)}`, 'PrintLaporan', 'width=900,height=600'); }