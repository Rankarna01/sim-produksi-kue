let currentPage = 1;
let searchTimeout = null;

document.addEventListener("DOMContentLoaded", () => {
    // Set default filter date 1 bulan terakhir
    const today = new Date();
    const lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);
    
    document.getElementById('end_date').value = today.toISOString().split('T')[0];
    document.getElementById('start_date').value = lastMonth.toISOString().split('T')[0];
    
    loadData(1);
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

function formatTgl(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const status = document.getElementById('filter_status').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&page=${currentPage}&status=${status}&start_date=${start_date}&end_date=${end_date}&search=${search}`;
    const res = await fetchAjax(url, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 font-bold italic">Tidak ada data opname ditemukan pada filter ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const no = (currentPage - 1) * 10 + idx + 1;
                
                let badgeStatus = '';
                if(item.status === 'approved') badgeStatus = '<span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Approved</span>';
                else if(item.status === 'rejected') badgeStatus = '<span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-ban mr-1"></i> Rejected</span>';
                else badgeStatus = '<span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Pending</span>';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${no}</td>
                        <td class="p-5 font-bold text-slate-600 text-xs">${formatTgl(item.opname_date)}</td>
                        <td class="p-5 font-black text-purple-600 text-xs tracking-widest">${item.opname_no}</td>
                        <td class="p-5 text-center font-bold text-slate-700">${item.total_items} <span class="text-[10px] text-slate-400 font-medium">Items</span></td>
                        <td class="p-5 font-bold text-slate-700 text-xs"><i class="fa-solid fa-user-check text-slate-300 mr-1"></i> ${item.pic_name}</td>
                        <td class="p-5 text-center">${badgeStatus}</td>
                        <td class="p-5 text-center">
                            <button onclick="lihatDetail(${item.id}, '${item.opname_no}', '${item.opname_date}')" class="bg-slate-100 hover:bg-purple-100 text-purple-600 px-3 py-1.5 rounded-lg text-xs font-black transition-colors flex items-center gap-2 mx-auto shadow-sm">
                                <i class="fa-solid fa-magnifying-glass"></i> Detail
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

// FUNGSI LOAD DETAIL KE DALAM MODAL
async function lihatDetail(id, opname_no, opname_date) {
    document.getElementById('detail-subtitle').innerText = `Dokumen: ${opname_no} | Tanggal: ${formatTgl(opname_date)}`;
    
    const tbody = document.getElementById('table-detail-items');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-2xl"></i></td></tr>';
    
    openModal('modal-detail');

    const res = await fetchAjax(`logic.php?action=get_detail&id=${id}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if(res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-slate-400 italic">Tidak ada detail barang pada dokumen ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                
                // Logika Warna Selisih
                let diffClass = 'text-slate-500';
                let diffText = '0';
                
                if (item.difference < 0) {
                    diffClass = 'text-rose-600';
                    diffText = item.difference;
                } else if (item.difference > 0) {
                    diffClass = 'text-emerald-600';
                    diffText = '+' + item.difference;
                }

                html += `
                    <tr class="hover:bg-white transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4">
                            <div class="font-black text-slate-800 text-xs">${item.material_name}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-0.5">${item.sku_code}</div>
                        </td>
                        <td class="p-4 text-center font-bold text-slate-600 border-l border-slate-200 bg-slate-50/50">${parseFloat(item.system_stock)} <span class="text-[10px]">${item.unit}</span></td>
                        <td class="p-4 text-center font-black text-blue-600 bg-blue-50/20">${parseFloat(item.physical_stock)} <span class="text-[10px]">${item.unit}</span></td>
                        <td class="p-4 text-center font-black ${diffClass} text-sm border-r border-slate-200 bg-slate-50/50">${diffText}</td>
                        <td class="p-4 text-xs text-slate-500 italic">${item.notes || '-'}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}