let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    loadData(1);
});

function closeModal() { 
    document.getElementById('modal-success').classList.add('hidden'); 
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-2xl"></i></td></tr>';
    
    const res = await fetchAjax(`logic.php?action=read&page=${currentPage}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = `<tr><td colspan="5" class="p-10 text-center text-slate-400 italic font-bold">Belum ada riwayat kode akses.</td></tr>`;
        } else {
            res.data.forEach((item, index) => {
                const no = (page - 1) * 10 + index + 1;
                
                // Format Tanggal
                const dBuat = new Date(item.created_at);
                const dExp = new Date(item.valid_until);
                const formatTgl = (d) => d.toLocaleDateString('id-ID', {day:'2-digit', month:'2-digit', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

                // Badge Status
                let statusBadge = '';
                if(item.status === 'active') {
                    statusBadge = '<span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-100">Aktif</span>';
                } else if(item.status === 'expired') {
                    statusBadge = '<span class="bg-slate-100 text-slate-400 px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-200">Expired</span>';
                } else {
                    statusBadge = '<span class="bg-rose-50 text-rose-500 px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest border border-rose-100">Terpakai</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors ${item.status !== 'active' ? 'opacity-60' : ''}">
                        <td class="p-5 text-center text-slate-400 font-bold text-xs">${no}</td>
                        <td class="p-5 font-black text-indigo-600 text-lg tracking-[0.2em]">${item.access_code}</td>
                        <td class="p-5 text-xs font-bold text-slate-600">${formatTgl(dBuat)}</td>
                        <td class="p-5 text-xs font-bold text-slate-500">${formatTgl(dExp)}</td>
                        <td class="p-5 text-center">${statusBadge}</td>
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
        const active = i === current ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

async function generateKey() {
    Swal.fire({ title: 'Membuat Kode...', text: 'Mohon tunggu', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const res = await fetchAjax('logic.php?action=generate', 'POST');
    Swal.close(); // Tutup loading sweetalert

    if (res.status === 'success') {
        // Tampilkan Modal Custom kita
        document.getElementById('new-pin-code').innerText = res.pin;
        document.getElementById('new-pin-expiry').innerText = `(Berlaku sampai ${res.valid_until_formatted})`;
        document.getElementById('modal-success').classList.remove('hidden');
        
        loadData(1); // Refresh tabel di belakang
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
}