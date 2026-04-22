let currentTab = 'semua';
let currentPage = 1;
let searchTimeout = null;
let materialsData = [];

document.addEventListener("DOMContentLoaded", async () => {
    await initFormDropdowns();
    loadData(1);
});

async function initFormDropdowns() {
    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;

        let optMat = '<option value="">-- Pilih Barang --</option>';
        res.materials.forEach(m => { 
            optMat += `<option value="${m.id}">${m.sku_code} - ${m.material_name}</option>`; 
        });
        document.getElementById('material_id').innerHTML = optMat;
    }
}

function updateSatuan() {
    const matId = document.getElementById('material_id').value;
    const labelSatuan = document.getElementById('satuan_label');
    const infoStok = document.getElementById('stock_info');
    
    if(!matId) {
        labelSatuan.value = '-';
        infoStok.innerText = 'Pilih barang untuk melihat sisa stok.';
        infoStok.className = 'text-[10px] text-slate-400 mt-1 pl-1';
        return;
    }
    const mat = materialsData.find(m => m.id == matId);
    if(mat) {
        const currentStock = parseFloat(mat.stock);
        labelSatuan.value = mat.unit;
        infoStok.innerText = `Sisa Stok saat ini: ${currentStock} ${mat.unit}`;
        
        if (currentStock <= 0) {
            infoStok.className = 'text-[10px] text-rose-500 font-bold mt-1 pl-1';
        } else {
            infoStok.className = 'text-[10px] text-slate-500 font-medium mt-1 pl-1';
        }
    }
}

function openModalKeluar() {
    document.getElementById('formKeluar').reset();
    document.getElementById('satuan_label').value = '-';
    document.getElementById('stock_info').innerText = 'Pilih barang untuk melihat sisa stok.';
    openModal('modal-keluar');
}
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function closeModalKeluar() { closeModal('modal-keluar'); }

function switchTab(tab) {
    currentTab = tab;
    loadData(currentPage);
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadData(1); }, 500); 
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    const res = await fetchAjax(`logic.php?action=read&search=${search}&tab=${currentTab}&start_date=${start_date}&end_date=${end_date}&page=${currentPage}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = `<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Belum ada transaksi keluar di filter ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                // Status Kondisi Barang (Rusak / Expired / Lainnya)
                let statusBadge = '';
                if(item.status === 'Rusak') statusBadge = '<span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-rose-200 shadow-inner">Rusak</span>';
                else if(item.status === 'Expired') statusBadge = '<span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-amber-200 shadow-inner">Expired</span>';
                else statusBadge = '<span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-inner">Lainnya</span>';

                // Lencana Approval Status
                let approvalVal = item.approval_status || 'approved'; // default if missing from old data
                let approvalBadge = '';
                if(approvalVal === 'pending') approvalBadge = '<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Pending</span>';
                else if(approvalVal === 'rejected') approvalBadge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';
                else approvalBadge = '<span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Approved</span>';

                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-xs">${tgl}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-1 font-mono">${item.transaction_no}</div>
                        </td>
                        <td class="p-5 uppercase text-xs">
                            <span class="font-black text-slate-800">${item.material_name}</span>
                            <span class="text-[9px] text-slate-400 block font-mono">#${item.sku_code}</span>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5 text-center font-black text-rose-600 text-lg">-${parseFloat(item.qty)}</td>
                        <td class="p-5 text-center text-slate-500 font-bold uppercase text-xs tracking-widest">${item.unit}</td>
                        <td class="p-5 text-center">${approvalBadge}</td>
                        <td class="p-5 text-xs text-slate-500 italic max-w-[180px] truncate" title="${safeNotes}">${safeNotes}</td>
                        <td class="p-5 text-xs font-bold text-slate-600">${item.admin_name}</td>
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

document.getElementById('formKeluar').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const matId = document.getElementById('material_id').value;
    const qtyInput = parseFloat(document.getElementById('qty').value);
    const mat = materialsData.find(m => m.id == matId);

    if(!mat) { Swal.fire('Error', 'Pilih barang terlebih dahulu!', 'error'); return; }
    
    if (qtyInput > parseFloat(mat.stock)) {
        Swal.fire({
            title: 'Stok Tidak Cukup!',
            text: `Kamu ingin mengeluarkan ${qtyInput} ${mat.unit}, tapi stok ${mat.material_name} saat ini hanya sisa ${parseFloat(mat.stock)} ${mat.unit}.`,
            icon: 'error',
            confirmButtonColor: '#F43F5E',
        });
        return;
    }

    Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const res = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (res.status === 'success') {
        closeModalKeluar();
        await initFormDropdowns();
        loadData(1); 
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

function cetakLaporan() {
    const search = document.getElementById('search').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    window.open(`print.php?tab=${currentTab}&search=${search}&start_date=${start_date}&end_date=${end_date}`, '_blank');
}