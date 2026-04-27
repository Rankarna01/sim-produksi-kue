let currentPage = 1;

document.addEventListener("DOMContentLoaded", () => {
    initForm();
    loadData(1);
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openModalTambah() {
    document.getElementById('formData').reset();
    document.getElementById('stok_info').classList.add('hidden');
    openModal('modal-form');
}

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + '<br><span class="text-[10px] text-slate-400">' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB</span>';
}

async function initForm() {
    const res = await fetchAjax('logic.php?action=init', 'GET');
    if (res.status === 'success') {
        const select = document.getElementById('titipan_id');
        let opt = '<option value="">-- Pilih Barang Titipan --</option>';
        res.items.forEach(i => {
            opt += `<option value="${i.id}" data-stok="${i.stok}">${i.nama_barang} (${i.nama_umkm})</option>`;
        });
        select.innerHTML = opt;
    }
}

function setMaksStok() {
    const select = document.getElementById('titipan_id');
    const info = document.getElementById('stok_info');
    const maxVal = document.getElementById('stok_max_val');
    const inputQty = document.getElementById('qty');
    
    if (select.value) {
        const stok = select.options[select.selectedIndex].dataset.stok;
        maxVal.innerText = stok;
        info.classList.remove('hidden');
        inputQty.max = stok;
    } else {
        info.classList.add('hidden');
        inputQty.max = '';
    }
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>';
    
    const res = await fetchAjax(`logic.php?action=read&page=${currentPage}`, 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-bold">Belum ada riwayat penarikan barang titipan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                let badgeAlasan = `<span class="bg-rose-100 text-rose-600 px-2 py-0.5 rounded text-[9px] font-black uppercase border border-rose-200">${item.reason}</span>`;
                if(item.reason === 'Diretur UMKM') badgeAlasan = `<span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[9px] font-black uppercase border border-blue-200">${item.reason}</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                        <td class="p-5 text-xs font-bold text-slate-600 leading-tight">${formatTglTime(item.created_at)}</td>
                        <td class="p-5 font-mono text-xs text-rose-600 font-bold">${item.out_no}</td>
                        <td class="p-5">
                            <div class="font-black text-slate-800">${item.nama_barang}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">${item.nama_umkm}</div>
                        </td>
                        <td class="p-5 text-center font-black text-rose-600 text-lg">-${item.qty}</td>
                        <td class="p-5 text-center">${badgeAlasan}<br><span class="text-[10px] text-slate-400 italic">${item.notes || '-'}</span></td>
                        <td class="p-5 text-xs font-bold text-slate-500 uppercase">${item.admin_name}</td>
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
        const active = i === current ? 'bg-rose-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadData(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

document.getElementById('formData').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
    
    const formData = new FormData(this);
    
    // PERBAIKAN: Menambahkan ?action=save langsung di URL fetchAjax
    const res = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (res.status === 'success') {
        closeModal('modal-form');
        initForm(); // Refresh dropdown agar stok terbaru muncul
        loadData(1);
        Swal.fire('Berhasil!', res.message, 'success');
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});