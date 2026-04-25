let currentStatus = 'semua'; 
let currentPageData = 1;

document.addEventListener('DOMContentLoaded', () => {
    loadData(1, currentStatus);
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

async function loadData(page = 1, status = null) {
    if (status !== null) currentStatus = status; 
    currentPageData = page;

    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center text-slate-400 font-bold animate-pulse">Menarik Data...</td></tr>';

    const res = await fetchAjax(`logic.php?action=read&page=${page}&status=${currentStatus}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic">Tidak ada riwayat invoice permintaan.</td></tr>';
        } else {
            res.data.forEach((item, index) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + '<br><span class="text-[10px] text-slate-400 font-bold">' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + '</span>';

                let statusBadge = '';
                if (item.status === 'menunggu') statusBadge = '<span class="bg-amber-50 text-amber-500 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-amber-200">Menunggu</span>';
                else if (item.status === 'diproses') statusBadge = '<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-emerald-200">Selesai/Diproses</span>';
                else statusBadge = '<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full font-black tracking-tighter uppercase border border-rose-200">Ditolak Semua</span>';

                html += `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-5 font-black text-slate-400 text-xs text-center">${(page-1)*10 + index + 1}</td>
                        <td class="p-5 font-medium text-slate-700 text-sm">${tgl}</td>
                        <td class="p-5 font-black text-blue-600 text-base">${item.request_no}</td>
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-sm">${item.nama_staff || 'System'}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5"><i class="fa-solid fa-store mr-1"></i> ${item.nama_dapur}</div>
                        </td>
                        <td class="p-5 text-center font-black text-slate-800 text-lg">${item.total_item} <span class="text-xs text-slate-400 uppercase">Item</span></td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center">
                            <button onclick="lihatDetailReq(${item.id}, '${item.request_no}', '${item.nama_dapur}')" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-black transition-all shadow-md mx-auto flex items-center justify-center gap-2">
                                <i class="fa-solid fa-list-check"></i> Proses Detail
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

// BUKA MODAL DETAIL
async function lihatDetailReq(header_id, req_no, dapur_name) {
    document.getElementById('modal_req_no').innerText = req_no;
    document.getElementById('modal_dapur_name').innerText = dapur_name;
    
    await loadTableDetail(header_id);
    openModal('modal-proses');
}

async function loadTableDetail(header_id) {
    const tbody = document.getElementById('table-detail');
    tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400 font-bold"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat detail barang...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_detail&header_id=${header_id}`, 'GET');
    
    if(response.status === 'success') {
        let html = '';
        response.data.forEach((item, idx) => {
            
            let statusBadge = '';
            let actionButtons = '-';
            
            if (item.status === 'menunggu') {
                statusBadge = '<span class="text-amber-500 font-bold text-xs"><i class="fa-solid fa-clock"></i> Pending</span>';
                actionButtons = `
                    <div class="flex justify-center gap-2">
                        <button onclick="prosesTerima(${item.id}, ${header_id}, ${item.material_id}, '${item.material_name}', ${item.qty_requested}, ${item.stok_gudang})" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Proses">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <button onclick="prosesTolak(${item.id}, ${header_id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Tolak">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                `;
            } else if (item.status === 'diproses') {
                statusBadge = '<span class="text-emerald-500 font-bold text-xs"><i class="fa-solid fa-check-double"></i> Dikirim</span>';
                actionButtons = `<span class="text-[10px] text-emerald-500 font-bold">${parseFloat(item.qty_approved)} ${item.unit}</span>`;
            } else {
                statusBadge = '<span class="text-rose-500 font-bold text-xs"><i class="fa-solid fa-ban"></i> Ditolak</span>';
            }

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center text-xs font-bold text-slate-400">${idx+1}</td>
                    <td class="p-4 font-black text-slate-700">${item.material_name}</td>
                    <td class="p-4 text-center">
                        <span class="font-bold ${item.stok_gudang < item.qty_requested ? 'text-rose-500' : 'text-slate-600'} text-base">${parseFloat(item.stok_gudang)}</span> 
                        <span class="text-[10px] text-slate-400 font-bold uppercase">${item.unit}</span>
                    </td>
                    <td class="p-4 text-center">
                        <span class="font-black text-blue-600 text-lg">${parseFloat(item.qty_requested)}</span> 
                        <span class="text-[10px] text-slate-400 font-bold uppercase">${item.unit}</span>
                    </td>
                    <td class="p-4 text-center">${statusBadge}</td>
                    <td class="p-4 text-center">${actionButtons}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
}

// Fungsi Approve per Item
async function prosesTerima(id, header_id, material_id, material_name, reqQty, stokGudang) {
    if (stokGudang < reqQty) {
        Swal.fire('Peringatan!', `Stok gudang tidak cukup untuk memenuhi permintaan ini. (Sisa: ${stokGudang})`, 'warning');
    }

    const { value: qtyAcc } = await Swal.fire({
        title: 'Kirim Barang',
        html: `Berapa jumlah <b>${material_name}</b> yang akan dikirim?<br><br><span class="text-xs text-slate-500">Diminta: ${reqQty} | Sisa Gudang: ${stokGudang}</span>`,
        input: 'number',
        inputValue: reqQty > stokGudang ? stokGudang : reqQty,
        showCancelButton: true,
        confirmButtonText: 'Kirim & Potong Stok',
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
        formData.append('header_id', header_id);
        formData.append('material_id', material_id);
        formData.append('qty_approved', qtyAcc);

        const res = await fetchAjax('logic.php?action=approve', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
            loadTableDetail(header_id); // Refresh detail modal
            loadData(currentPageData, currentStatus); // Refresh tabel background
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

// Fungsi Reject per Item
async function prosesTolak(id, header_id) {
    const confirm = await Swal.fire({
        title: 'Tolak Barang Ini?',
        text: "Barang tidak akan dikirim ke dapur.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F43F5E',
        confirmButtonText: 'Ya, Tolak'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        const formData = new FormData();
        formData.append('id', id);
        formData.append('header_id', header_id);
        
        const res = await fetchAjax('logic.php?action=reject', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Ditolak!', text: res.message, icon: 'success', timer: 1000, showConfirmButton: false });
            loadTableDetail(header_id); 
            loadData(currentPageData, currentStatus); 
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}