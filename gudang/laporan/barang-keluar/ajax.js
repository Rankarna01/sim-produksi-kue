let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    // Set default date hari ini untuk custom filter
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function setFilterDate(type) {
    currentFilterDate = type;
    
    // Reset semua warna tombol
    const btns = ['harian', 'mingguan', 'bulanan', 'tahunan', 'periode', 'semua'];
    btns.forEach(btn => {
        document.getElementById(`btn-${btn}`).className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300 whitespace-nowrap";
    });
    
    // Set warna tombol aktif
    document.getElementById(`btn-${type}`).className = "px-4 py-2 bg-rose-50 text-rose-600 font-black transition-colors border-r border-slate-300 whitespace-nowrap";

    // Show/Hide Custom Date Picker
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
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic">Tidak ada data laporan barang keluar.</td></tr>';
        } else {
            res.data.forEach((item) => {
                html += `
                    <tr class="hover:bg-rose-50/30 transition-colors">
                        <td class="p-4 text-xs font-bold text-slate-600">${formatTglTime(item.created_at)}</td>
                        <td class="p-4 text-sm font-black text-slate-800">${item.material_name}</td>
                        <td class="p-4 text-sm font-black text-rose-600 text-center">-${parseFloat(item.qty)} <span class="text-xs font-bold">${item.unit}</span></td>
                        <td class="p-4 text-xs font-bold text-slate-600">${item.transaction_no}</td>
                        <td class="p-4 text-xs font-medium text-slate-600">-</td>
                        <td class="p-4 text-xs text-slate-500 italic max-w-[150px] truncate">${item.notes || '-'}</td>
                        <td class="p-4 text-xs font-bold text-slate-600 uppercase">${item.admin_name}</td>
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
        const active = i === current ? 'bg-rose-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

function exportData(type) {
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    
    window.open(url, '_blank');
}