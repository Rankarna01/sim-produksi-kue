let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

function formatTgl(dateStr) {
    if(!dateStr) return '-';
    const d = new Date(dateStr);
    return `${d.getDate()}/${d.getMonth()+1}/${d.getFullYear()}`;
}

function formatRupiah(angka) {
    if(isNaN(angka) || angka === null) return "Rp 0";
    // Menggunakan fixed decimal menyesuaikan gambar (contoh: Rp 10000.00) jika diperlukan, tapi format standar IDR lebih rapi
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(parseFloat(angka));
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
    tbody.innerHTML = '<tr><td colspan="4" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="4" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada data historis pembelian.</td></tr>';
        } else {
            res.data.forEach((item) => {
                
                // RENDER DAFTAR KOTAK SUPPLIER
                let supplierBoxes = '';
                item.suppliers.forEach((sup, index) => {
                    let isBest = (index === 0); // Karena array sudah di-sort dari yg termurah di PHP
                    let boxClass = isBest ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-slate-200';
                    let textClass = isBest ? 'text-emerald-700' : 'text-slate-700';
                    
                    supplierBoxes += `
                        <div class="border ${boxClass} rounded-xl p-3 min-w-[140px] shadow-sm flex-1">
                            <p class="text-xs font-bold text-slate-500 truncate w-28" title="${sup.supplier_name}">${sup.supplier_name}</p>
                            <p class="text-sm font-black ${textClass} my-1">${formatRupiah(sup.price)}</p>
                            <p class="text-[9px] text-slate-400 font-medium bg-slate-100 px-1.5 py-0.5 rounded inline-block">Riwayat (${formatTgl(sup.date)})</p>
                        </div>
                    `;
                });

                // TAMPILAN BARIS UTAMA
                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5 align-top">
                            <h4 class="font-black text-slate-800 text-sm mb-1">${item.material_name}</h4>
                            <span class="text-xs font-medium text-slate-400 lowercase">${item.category_name}</span>
                        </td>
                        <td class="p-5 align-top">
                            <p class="text-lg font-black text-emerald-600 mb-1">${formatRupiah(item.min_price)}</p>
                            <p class="text-[11px] text-slate-600 font-bold">${item.best_supplier}</p>
                            <p class="text-[9px] text-blue-500 font-bold bg-blue-50 px-2 py-0.5 rounded mt-1 inline-block">Riwayat • ${formatTgl(item.best_date)}</p>
                        </td>
                        <td class="p-5 align-top text-xs">
                            <div class="flex justify-between mb-1"><span class="text-slate-400 font-medium">Max:</span> <span class="font-bold text-slate-700">${formatRupiah(item.max_price)}</span></div>
                            <div class="flex justify-between mb-1"><span class="text-slate-400 font-medium">Avg:</span> <span class="font-bold text-slate-700">${formatRupiah(item.avg_price)}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400 font-medium">Spread:</span> <span class="font-black text-orange-500">${item.spread}%</span></div>
                        </td>
                        <td class="p-5 align-top">
                            <div class="flex gap-3 flex-wrap">
                                ${supplierBoxes}
                            </div>
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
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
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