let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    loadData(1);
});

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const threshold = document.getElementById('threshold').value || 10;
    
    const url = `logic.php?action=read&page=${currentPage}&threshold=${threshold}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 italic font-bold">Stok aman! Tidak ada barang yang menipis pada batas ini.</td></tr>';
        } else {
            res.data.forEach((item) => {
                let stockVal = parseFloat(item.stock);
                
                // Status Badge
                let statusBadge = '';
                if (stockVal <= 0) {
                    statusBadge = '<span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-200">Habis</span>';
                } else {
                    statusBadge = '<span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-orange-200">Kritis</span>';
                }

                // Warna Angka Stok
                let stockClass = stockVal <= 0 ? 'text-rose-600' : 'text-orange-500';

                html += `
                    <tr class="hover:bg-rose-50/30 transition-colors">
                        <td class="p-5 font-mono text-xs font-bold text-slate-500">${item.sku_code}</td>
                        <td class="p-5 text-sm font-black text-slate-800">${item.material_name}</td>
                        <td class="p-5 text-xs font-bold text-slate-500">
                            ${item.category_name || '-'} <br>
                            <span class="text-[9px] uppercase font-bold text-slate-400 mt-0.5 inline-block"><i class="fa-solid fa-server"></i> Rak: ${item.rack_name || '-'}</span>
                        </td>
                        <td class="p-5 text-center text-xl font-black ${stockClass}">
                            ${stockVal} <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${item.unit}</span>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center">
                            <a href="../../transaksi/permintaan/" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm transition-all inline-block">
                                <i class="fa-solid fa-cart-plus mr-1"></i> Restock
                            </a>
                        </td>
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
    const threshold = document.getElementById('threshold').value || 10;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?threshold=${threshold}&search=${search}`;
    
    window.open(url, '_blank');
}