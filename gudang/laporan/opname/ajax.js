let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatWaktu(datetime) {
    const d = new Date(datetime);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
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
    const container = document.getElementById('container-data');
    container.innerHTML = '<div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i> Memuat data...</div>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<div class="p-10 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 italic font-bold">Tidak ada data laporan stok opname.</div>';
        } else {
            res.data.forEach((opname) => {
                
                // RENDER TABEL ITEM DI DALAM CARD
                let tableRows = '';
                opname.details.forEach((item, idx) => {
                    let selisihVal = parseFloat(item.difference);
                    let selisihHTML = '';
                    if(selisihVal > 0) selisihHTML = `<span class="text-blue-600 font-black">+${selisihVal}</span>`;
                    else if(selisihVal < 0) selisihHTML = `<span class="text-rose-600 font-black">${selisihVal}</span>`;
                    else selisihHTML = `<span class="text-slate-400 font-bold">0</span>`;

                    tableRows += `
                        <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                            <td class="p-4 text-xs font-bold text-slate-400 w-12">${idx + 1}</td>
                            <td class="p-4 text-sm font-bold text-slate-700">${item.material_name}</td>
                            <td class="p-4 text-xs font-bold text-slate-500 w-24 text-center">${formatWaktu(opname.created_at)}</td>
                            <td class="p-4 text-sm font-black text-slate-600 w-28 text-center">${parseFloat(item.system_stock)}</td>
                            <td class="p-4 text-sm font-black text-slate-800 w-28 text-center">${parseFloat(item.physical_stock)}</td>
                            <td class="p-4 text-sm w-24 text-center">${selisihHTML}</td>
                        </tr>
                    `;
                });

                // KARTU OPNAME (Sesuai Gambar)
                html += `
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="p-6">
                        <h3 class="text-base font-black text-slate-800 mb-4">Opname Stok <span class="text-blue-600 ml-1">#${opname.opname_no}</span></h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-sm">
                            <div>
                                <div class="flex mb-1"><span class="w-24 font-bold text-slate-600">Tanggal</span><span class="text-slate-500">: ${formatTglTime(opname.created_at)}</span></div>
                                <div class="flex"><span class="w-24 font-bold text-slate-600">Catatan</span><span class="text-slate-500">: -</span></div>
                            </div>
                            <div>
                                <div class="flex"><span class="w-24 font-bold text-slate-600">Posted</span><span class="text-slate-500">: ${opname.admin_name}</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-xs font-black text-slate-800 bg-slate-50 border-y border-slate-200">
                                    <th class="p-4 text-center">#</th>
                                    <th class="p-4">Produk</th>
                                    <th class="p-4 text-center">Waktu</th>
                                    <th class="p-4 text-center">Qty System</th>
                                    <th class="p-4 text-center">Qty Aktual</th>
                                    <th class="p-4 text-center">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
                `;
            });
        }
        container.innerHTML = html;
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