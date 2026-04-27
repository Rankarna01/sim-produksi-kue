let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    // Set default date hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + '<br><span class="text-[10px] text-slate-400">' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB</span>';
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;

    const url = `logic.php?action=read&page=${currentPage}&start_date=${start}&end_date=${end}&status=${status}`;
    const res = await fetchAjax(url, 'GET');

    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada riwayat retur pada filter ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                
                let badgeStatus = '';
                if(item.status === 'pending') badgeStatus = `<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-clock mr-1"></i> Menunggu</span>`;
                else if(item.status === 'approved') badgeStatus = `<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Disetujui</span>`;
                else badgeStatus = `<span class="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${(currentPage - 1) * 15 + idx + 1}</td>
                        <td class="p-5 text-xs font-bold text-slate-600 leading-tight">${formatTglTime(item.created_at)}</td>
                        <td class="p-5">
                            <div class="font-black text-blue-600 tracking-widest">${item.po_no}</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">${item.supplier_name}</div>
                        </td>
                        <td class="p-5 font-black text-slate-800 uppercase text-xs">${item.material_name}</td>
                        <td class="p-5 text-center font-black text-rose-600 text-lg">
                            -${parseFloat(item.qty_return)} <span class="text-[10px] text-slate-400 uppercase tracking-widest">${item.unit}</span>
                        </td>
                        <td class="p-5 text-right font-black text-slate-700">
                            <span class="text-rose-500">- ${formatRupiah(item.total_potongan)}</span><br>
                            <span class="text-[9px] text-slate-400 uppercase tracking-widest">(${formatRupiah(item.price)}/item)</span>
                        </td>
                        <td class="p-5 text-center">${badgeStatus}</td>
                        <td class="p-5 text-xs text-slate-500 italic max-w-[150px] truncate" title="${item.reason}">${item.reason}</td>
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
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?start_date=${start}&end_date=${end}&status=${status}`;
    window.open(url, '_blank');
}