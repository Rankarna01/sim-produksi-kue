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
    
    // Update UI Button
    document.getElementById('btn-harian').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-periode').className = "px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300";
    document.getElementById('btn-semua').className = "px-4 py-2 hover:bg-slate-50 transition-colors";
    
    document.getElementById(`btn-${type}`).className = "px-4 py-2 bg-blue-50 text-blue-600 font-black transition-colors border-r border-slate-300";

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
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const source = document.getElementById('filter_source').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&source=${source}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic">Tidak ada data laporan.</td></tr>';
        } else {
            res.data.forEach((item) => {
                // Styling Sumber Badge
                let sourceBadge = '';
                if(item.source === 'PO') {
                    sourceBadge = `<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-black tracking-widest">${item.transaction_no}</span>`;
                } else {
                    sourceBadge = `<span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold">Manual</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-xs font-bold text-slate-600">${formatTglTime(item.created_at)}</td>
                        <td class="p-4 text-sm font-black text-slate-800">${item.material_name}</td>
                        <td class="p-4 text-sm font-black text-emerald-600">+${parseFloat(item.qty)} <span class="text-xs font-bold">${item.unit}</span></td>
                        <td class="p-4">${sourceBadge}</td>
                        <td class="p-4 text-xs font-medium text-slate-600">${item.supplier_name || '-'}</td>
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
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

function exportData(type) {
    const source = document.getElementById('filter_source').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?source=${source}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    
    window.open(url, '_blank');
}