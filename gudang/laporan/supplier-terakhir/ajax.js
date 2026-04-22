let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", async () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    await initFilter();
    loadData(1);
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if(res.status === 'success') {
        let opt = '<option value="semua">Semua Supplier</option>';
        res.suppliers.forEach(s => { opt += `<option value="${s.id}">${s.name}</option>`; });
        document.getElementById('filter_supplier').innerHTML = opt;
    }
}

function formatTgl(dateStr) {
    if(!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parseFloat(angka) || 0);
}

function setFilterDate(type) {
    currentFilterDate = type;
    
    document.getElementById('btn-harian').className = "px-4 py-2 hover:bg-slate-200 transition-colors border-r border-slate-300 bg-slate-50 text-slate-600";
    document.getElementById('btn-periode').className = "px-4 py-2 hover:bg-slate-200 transition-colors border-r border-slate-300 bg-slate-50 text-slate-600";
    document.getElementById('btn-semua').className = "px-4 py-2 hover:bg-slate-200 transition-colors bg-slate-50 text-slate-600";
    
    document.getElementById(`btn-${type}`).className = "px-4 py-2 bg-blue-600 text-white font-black transition-colors border-r border-blue-600";

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
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const supplier = document.getElementById('filter_supplier').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&supplier_id=${supplier}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada transaksi pemasok pada filter ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const no = (currentPage - 1) * 10 + idx + 1;
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${no}</td>
                        <td class="p-5 font-black text-slate-700">${item.supplier_name}</td>
                        <td class="p-5 text-center font-bold text-blue-600 bg-blue-50/30">${item.total_transaksi} <span class="text-[10px] text-blue-400 font-medium">Transaksi</span></td>
                        <td class="p-5 text-right font-black text-emerald-600">${formatRupiah(item.total_pembelian)}</td>
                        <td class="p-5 font-medium text-slate-500 text-xs">${formatTgl(item.transaksi_terakhir)}</td>
                        <td class="p-5 text-center">
                            <button onclick="lihatDetail('${item.supplier_id}', '${item.supplier_name}')" class="bg-slate-100 hover:bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-black transition-colors flex items-center justify-center mx-auto shadow-sm gap-1">
                                <i class="fa-solid fa-list"></i> Detail
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

// FUNGSI UNTUK MEMBUKA MODAL DETAIL
async function lihatDetail(supplier_id, supplier_name) {
    document.getElementById('detail-supplier-name').innerText = `Supplier: ${supplier_name}`;
    const tbody = document.getElementById('table-detail-items');
    tbody.innerHTML = '<tr><td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    openModal('modal-detail');

    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=get_detail&supplier_id=${supplier_id}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}`;
    const res = await fetchAjax(url, 'GET');

    if(res.status === 'success') {
        let html = '';
        if(res.data.length === 0){
            html = '<tr><td colspan="5" class="p-8 text-center text-slate-400 italic">Tidak ada detail transaksi.</td></tr>';
        } else {
            res.data.forEach((po, idx) => {
                let itemBadges = '';
                po.items.forEach(i => {
                    let priceText = i.price > 0 ? ` @ ${formatRupiah(i.price)}` : '';
                    itemBadges += `<div class="bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded shadow-sm text-[10px] font-bold mb-1">${i.material_name} <span class="text-blue-500">(${parseFloat(i.qty)} ${i.unit})</span> <span class="text-emerald-500">${priceText}</span></div>`;
                });

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-600">${formatTgl(po.updated_at)}</td>
                        <td class="p-4 font-black text-blue-600 text-xs tracking-widest">${po.po_no}</td>
                        <td class="p-4"><div class="flex flex-col gap-1">${itemBadges}</div></td>
                        <td class="p-4 text-right font-black text-slate-800 bg-slate-50/50">${formatRupiah(po.total_amount)}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
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
    const supplier = document.getElementById('filter_supplier').value;
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?supplier_id=${supplier}&filter_date=${currentFilterDate}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    window.open(url, '_blank');
}