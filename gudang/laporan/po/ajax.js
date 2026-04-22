let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

function formatTglTime(datetime) {
    if(!datetime) return '-';
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
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const status_po = document.getElementById('filter_status_po').value;
    const status_pay = document.getElementById('filter_status_pay').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&status_po=${status_po}&status_pay=${status_pay}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic">Tidak ada data laporan.</td></tr>';
        } else {
            res.data.forEach((item) => {
                // Badge Status PO
                let statusBadge = '';
                if(item.status === 'received') statusBadge = '<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[10px] font-black tracking-widest uppercase">RECEIVED</span>';
                else if(item.status === 'rejected' || item.status === 'cancelled') statusBadge = '<span class="bg-rose-100 text-rose-700 px-2 py-1 rounded text-[10px] font-black tracking-widest uppercase">REJECTED</span>';
                else statusBadge = `<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-black tracking-widest uppercase">${item.status}</span>`;

                // Render Item Bullets
                let itemsList = '';
                if(item.items && item.items.length > 0) {
                    item.items.forEach(i => {
                        let priceText = (i.price > 0) ? ` @ ${formatRupiah(i.price)}` : '';
                        itemsList += `<div class="text-xs text-slate-600 whitespace-nowrap">• ${i.material_name} (${parseFloat(i.qty)})${priceText}</div>`;
                    });
                }

                let waktuTerima = (item.status === 'received' && item.updated_at) ? formatTglTime(item.updated_at) : '-';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-xs font-bold text-blue-600">${item.po_no}</td>
                        <td class="p-4 text-xs font-medium text-slate-600">${formatTglTime(item.created_at)}</td>
                        <td class="p-4 text-xs font-medium text-slate-600">${waktuTerima}</td>
                        <td class="p-4 text-xs font-bold text-slate-700">${item.supplier_name}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4">${itemsList}</td>
                        <td class="p-4 text-right text-sm font-black text-slate-800">${formatRupiah(item.total_amount)}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${item.admin_name}</td>
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
    const status_po = document.getElementById('filter_status_po').value;
    const status_pay = document.getElementById('filter_status_pay').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?status_po=${status_po}&status_pay=${status_pay}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    
    window.open(url, '_blank');
}