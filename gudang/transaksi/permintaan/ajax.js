let currentStatus = 'semua'; // State global untuk menyimpan filter saat ini

document.addEventListener('DOMContentLoaded', () => {
    loadData(1, currentStatus);
});

// Tambahkan parameter status agar aman dipanggil dari tombol Alpine.js
async function loadData(page = 1, status = null) {
    if (status !== null) {
        currentStatus = status; // Update state global jika ada filter baru
    }

    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="5" class="p-10 text-center text-slate-400 font-bold animate-pulse">Sinkronisasi Data...</td></tr>';

    const res = await fetchAjax(`logic.php?action=read&page=${page}&status=${currentStatus}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="5" class="p-10 text-center text-slate-300 italic">Tidak ada data permintaan ditemukan.</td></tr>';
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ', ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

                let statusBadge = '';
                if(item.status === 'menunggu') statusBadge = '<span class="text-amber-500 font-black tracking-tighter uppercase">Menunggu</span>';
                else if(item.status === 'diproses') statusBadge = '<span class="text-emerald-500 font-black tracking-tighter uppercase">Diproses</span>';
                else statusBadge = '<span class="text-red-500 font-black tracking-tighter uppercase">Ditolak</span>';

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5 font-medium text-slate-400 text-xs">${tgl}</td>
                        <td class="p-5 font-black text-slate-700 uppercase">${item.material_name}</td>
                        <td class="p-5 font-black text-blue-600">${parseFloat(item.qty_requested)} ${item.unit}</td>
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-xs">${item.nama_staff || 'System'}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">${item.nama_dapur}</div>
                        </td>
                        <td class="p-5 text-center text-[10px]">${statusBadge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function renderPagination(total, cur) {
    const container = document.getElementById('pagination');
    let h = '';
    if (total <= 1) { container.innerHTML = ''; return; }
    for (let i = 1; i <= total; i++) {
        const active = i === cur ? 'bg-blue-600 text-white' : 'bg-white text-slate-400 hover:bg-slate-100 border border-slate-200';
        h += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs shadow-sm transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = h;
}