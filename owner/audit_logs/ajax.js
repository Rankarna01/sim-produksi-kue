document.addEventListener("DOMContentLoaded", () => {
    loadLogs(1);
});

function toggleCustomDate() {
    const period = document.getElementById('filter-period').value;
    const container = document.getElementById('custom-date-container');
    container.classList.toggle('hidden', period !== 'custom');
}

async function loadLogs(page = 1) {
    const tbody = document.getElementById('table-logs');
    const period = document.getElementById('filter-period').value;
    const start = document.getElementById('start-date').value;
    const end = document.getElementById('end-date').value;

    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="p-10 text-center">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-primary mb-2"></i>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sinkronisasi Data...</p>
            </td>
        </tr>`;

    try {
        const response = await fetchAjax(`logic.php?action=read&page=${page}&period=${period}&start=${start}&end=${end}`, 'GET');

        if (response.status === 'success') {
            renderTable(response.data, page);
            renderPagination(response.total_pages, response.current_page);
        } else {
            showError(response.message);
        }
    } catch (err) {
        showError("Terjadi kesalahan pada server atau kolom database tidak ditemukan.");
    }
}

function renderTable(data, page) {
    const tbody = document.getElementById('table-logs');
    let html = '';

    if (data.length === 0) {
        html = '<tr><td colspan="5" class="p-8 text-center text-secondary italic">Belum ada aktivitas terekam.</td></tr>';
    } else {
        data.forEach((item, index) => {
            const no = (page - 1) * 15 + index + 1;
            const d = new Date(item.waktu);
            const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

            let menuColor = 'bg-slate-100 text-slate-600 border-slate-200';
            if (item.menu === 'PRODUKSI') menuColor = 'bg-indigo-50 text-indigo-600 border-indigo-100';
            else if (item.menu === 'OPNAME') menuColor = 'bg-emerald-50 text-emerald-600 border-emerald-100';
            else if (item.menu === 'PRODUK KELUAR') menuColor = 'bg-red-50 text-red-600 border-red-100';

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center text-slate-400 font-mono text-xs">${no}</td>
                    <td class="p-4 text-xs font-bold text-slate-500">${tgl}</td>
                    <td class="p-4">
                        <div class="font-bold text-slate-800 text-sm">${item.pegawai || 'SYSTEM'}</div>
                        <div class="text-[10px] text-primary font-bold uppercase tracking-tighter">@${item.role || 'bot'}</div>
                    </td>
                    <td class="p-4">
                        <span class="${menuColor} px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest border">${item.menu}</span>
                    </td>
                    <td class="p-4">
                        <div class="text-xs text-slate-700 font-medium italic border-l-2 border-slate-200 pl-3">
                            ${item.tindakan}
                        </div>
                    </td>
                </tr>`;
        });
    }
    tbody.innerHTML = html;
}

function renderPagination(total, cur) {
    const container = document.getElementById('pagination');
    let h = '';
    if (total <= 1) { container.innerHTML = ''; return; }
    
    for (let i = 1; i <= total; i++) {
        const active = i === cur ? 'bg-primary text-white border-primary' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100';
        h += `<button onclick="loadLogs(${i})" class="w-8 h-8 rounded-lg font-bold text-xs shadow-sm border ${active}">${i}</button>`;
    }
    container.innerHTML = h;
}

function showError(msg) {
    const tbody = document.getElementById('table-logs');
    tbody.innerHTML = `<tr><td colspan="5" class="p-10 text-center text-danger font-bold"><i class="fa-solid fa-triangle-exclamation mb-2 text-2xl block"></i> ERROR: ${msg}</td></tr>`;
}