let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", async () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    await initFilter();
    loadData(1);
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if(res.status === 'success') {
        let opt = '<option value="semua">Semua Metode</option>';
        res.methods.forEach(m => { opt += `<option value="${m.id}">${m.name}</option>`; });
        document.getElementById('filter_method').innerHTML = opt;
    }
}

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parseFloat(angka) || 0);
}

function setFilterDate(type) {
    currentFilterDate = type;
    
    document.getElementById('btn-harian').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-periode').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-semua').className = "px-4 py-2 hover:bg-slate-50 transition-colors";
    
    document.getElementById(`btn-${type}`).className = "px-4 py-2 bg-blue-50 text-blue-600 font-black transition-colors border-r border-slate-300";

    const datePicker = document.getElementById('custom-date-filter');
    if (type === 'periode') {
        datePicker.classList.remove('hidden');
        datePicker.classList.add('flex');
    } else {
        datePicker.classList.add('hidden');
        datePicker.classList.remove('flex');
    }

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
    
    const method = document.getElementById('filter_method').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&method=${method}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-medium">Tidak ada data laporan pembayaran.</td></tr>';
            document.getElementById('total-pembayaran').innerText = 'Rp 0';
        } else {
            res.data.forEach((item) => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-xs font-bold text-slate-600">${formatTglTime(item.payment_date)}</td>
                        <td class="p-4 text-xs font-black text-blue-600">${item.po_no}</td>
                        <td class="p-4 text-xs text-slate-700">${item.supplier_name}</td>
                        <td class="p-4 text-xs text-slate-700">${item.method_name}</td>
                        <td class="p-4 text-[10px] text-slate-500 italic max-w-[150px] truncate">${item.notes || '-'}</td>
                        <td class="p-4 text-xs font-bold text-slate-500 uppercase">${item.admin_name}</td>
                        <td class="p-4 text-right text-sm font-black text-slate-800">${formatRupiah(item.amount)}</td>
                    </tr>
                `;
            });
            document.getElementById('total-pembayaran').innerText = formatRupiah(res.grand_total);
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

function exportData(type) {
    const method = document.getElementById('filter_method').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?method=${method}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    
    window.open(url, '_blank');
}