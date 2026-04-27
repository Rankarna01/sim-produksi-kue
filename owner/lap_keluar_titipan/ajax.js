let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    toggleDateCustom();
    loadData(1);
});

function toggleDateCustom() {
    const p = document.getElementById('filter_periode').value;
    const els = document.querySelectorAll('.custom-date');
    if(p === 'custom') { els.forEach(e => e.classList.remove('hidden')); } 
    else { els.forEach(e => e.classList.add('hidden')); }
}

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + '<br><span class="text-[10px] text-slate-400">' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB</span>';
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const periode = document.getElementById('filter_periode').value;
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const reason = document.getElementById('filter_reason').value;

    const url = `logic.php?action=read&page=${currentPage}&periode=${periode}&start_date=${start}&end_date=${end}&reason=${reason}`;
    const res = await fetchAjax(url, 'GET');

    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada laporan ditarik pada periode ini.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                let badgeAlasan = `<span class="bg-rose-100 text-rose-600 px-2 py-0.5 rounded text-[9px] font-black uppercase border border-rose-200">${item.reason}</span>`;
                if(item.reason === 'Diretur UMKM') badgeAlasan = `<span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[9px] font-black uppercase border border-blue-200">${item.reason}</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                        <td class="p-5 text-xs font-bold text-slate-600 leading-tight">${formatTglTime(item.created_at)}</td>
                        <td class="p-5 text-xs font-bold text-slate-500 uppercase">${item.admin_name}</td>
                        <td class="p-5 text-xs font-bold text-slate-500 uppercase tracking-widest">${item.nama_umkm}</td>
                        <td class="p-5 font-black text-slate-800">${item.nama_barang}</td>
                        <td class="p-5 text-center font-black text-rose-600 text-lg">-${item.qty}</td>
                        <td class="p-5 text-center">${badgeAlasan}</td>
                        <td class="p-5 text-[10px] text-slate-400 italic">${item.notes || '-'}</td>
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
    const periode = document.getElementById('filter_periode').value;
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const reason = document.getElementById('filter_reason').value;
    
    let url = (type === 'pdf') ? 'export_pdf.php' : 'export_excel.php';
    url += `?periode=${periode}&start_date=${start}&end_date=${end}&reason=${reason}`;
    window.open(url, '_blank');
}