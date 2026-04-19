let currentStatus = 'semua'; 
let currentPageData = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadData(1, currentStatus);
});

async function loadData(page = 1, status = null) {
    if (status !== null) currentStatus = status; 
    currentPageData = page;

    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center text-slate-400 font-bold animate-pulse">Menarik Data...</td></tr>';

    const res = await fetchAjax(`logic.php?action=read&page=${page}&status=${currentStatus}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 italic">Tidak ada riwayat permintaan di filter ini.</td></tr>';
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ', ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

                // Logic Badge Status
                let statusBadge = '';
                let actionButtons = '-';
                
                if (item.status === 'menunggu') {
                    statusBadge = '<span class="bg-amber-50 text-amber-500 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-amber-200">Menunggu</span>';
                    // Tombol Aksi Muncul Hanya Jika Status Menunggu
                    actionButtons = `
                        <div class="flex justify-center gap-2">
                            <button onclick="prosesTerima(${item.id}, ${item.material_id}, '${item.material_name}', ${item.qty_requested}, ${item.stok_gudang})" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Proses / Setujui">
                                <i class="fa-solid fa-check"></i>
                            </button>
                            <button onclick="prosesTolak(${item.id})" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Tolak">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    `;
                } else if (item.status === 'diproses') {
                    statusBadge = '<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-emerald-200">Diproses</span>';
                    actionButtons = `<span class="text-[10px] text-slate-400 font-bold"><i class="fa-solid fa-check-double text-emerald-500 mr-1"></i> Selesai</span>`;
                } else {
                    statusBadge = '<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-rose-200">Ditolak</span>';
                    actionButtons = `<span class="text-[10px] text-slate-400 font-bold"><i class="fa-solid fa-ban text-rose-500 mr-1"></i> Ditolak</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-5 font-medium text-slate-500 text-xs">${tgl}</td>
                        <td class="p-5">
                            <div class="font-black text-slate-800 uppercase">${item.material_name}</div>
                            <div class="text-[10px] text-slate-400 font-bold mt-1">Stok Gudang: <span class="${item.stok_gudang < item.qty_requested ? 'text-rose-500' : 'text-emerald-500'}">${parseFloat(item.stok_gudang)} ${item.unit}</span></div>
                        </td>
                        <td class="p-5">
                            <div class="font-black text-blue-600 text-base">${parseFloat(item.qty_requested)} <span class="text-xs uppercase text-slate-400">${item.unit}</span></div>
                            ${item.status === 'diproses' && item.qty_approved != item.qty_requested ? `<div class="text-[10px] text-amber-600 font-bold mt-1">Dikirim: ${parseFloat(item.qty_approved)} ${item.unit}</div>` : ''}
                        </td>
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-sm">${item.nama_staff || 'System'}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5"><i class="fa-solid fa-store mr-1"></i> ${item.nama_dapur}</div>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center">${actionButtons}</td>
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
        const active = i === cur ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        h += `<button onclick="loadData(${i})" class="w-9 h-9 rounded-xl font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = h;
}

// Fungsi Approve dengan SweetAlert Input
async function prosesTerima(id, material_id, material_name, reqQty, stokGudang) {
    if (stokGudang < reqQty) {
        Swal.fire('Peringatan!', `Stok di gudang tidak cukup untuk memenuhi semua permintaan. (Sisa: ${stokGudang})`, 'warning');
    }

    const { value: qtyAcc } = await Swal.fire({
        title: 'Proses Permintaan',
        html: `Berapa jumlah <b>${material_name}</b> yang akan dikirim ke Dapur?<br><br><i>Diminta: ${reqQty} | Sisa Gudang: ${stokGudang}</i>`,
        input: 'number',
        inputValue: reqQty > stokGudang ? stokGudang : reqQty,
        showCancelButton: true,
        confirmButtonText: 'Kirim Barang',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || value <= 0) return 'Jumlah harus lebih dari 0!'
            if (value > stokGudang) return 'Stok gudang tidak mencukupi!'
        }
    });

    if (qtyAcc) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('material_id', material_id);
        formData.append('qty_approved', qtyAcc);

        const res = await fetchAjax('logic.php?action=approve', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
            loadData(currentPageData, currentStatus);
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

// Fungsi Reject
async function prosesTolak(id) {
    const confirm = await Swal.fire({
        title: 'Tolak Permintaan?',
        text: "Permintaan dari Dapur ini tidak akan dipenuhi.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F43F5E', // Rose-500
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        const formData = new FormData();
        formData.append('id', id);
        
        const res = await fetchAjax('logic.php?action=reject', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Ditolak!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
            loadData(currentPageData, currentStatus);
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}