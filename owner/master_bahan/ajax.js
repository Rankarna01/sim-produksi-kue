let currentRequestPage = 1;
let currentRequestStatus = 'semua';

document.addEventListener("DOMContentLoaded", () => {
    loadSemuaData(); 
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function formatDesimal(angka) {
    const num = parseFloat(angka);
    return num % 1 !== 0 ? num.toFixed(2) : num;
}

function loadSemuaData() {
    loadData();
    loadRequests('semua', 1);
}

// 1. LOAD STOK DAPUR
async function loadData() {
    const tbody = document.getElementById('table-body');
    const filterElement = document.getElementById('filter-dapur');
    const warehouse_id = filterElement ? filterElement.value : 1;
    
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read&warehouse_id=${warehouse_id}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary">Belum ada data bahan baku. Lakukan pengajuan ke gudang.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const currentStock = parseFloat(item.stock);
                const minStock = parseFloat(item.min_stock);
                
                let stockClass = "text-slate-800"; 
                let warningIcon = "";
                if (currentStock <= minStock) {
                    stockClass = "text-danger font-bold";
                    warningIcon = `<i class="fa-solid fa-triangle-exclamation text-danger ml-2" title="Stok Menipis!"></i>`;
                }

                let btnAksi = '';
                if (canEdit) btnAksi += `<button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center shadow-sm" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>&nbsp;`;
                if (canDelete) btnAksi += `<button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors flex items-center justify-center shadow-sm" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>`;
                if (btnAksi === '') btnAksi = '<span class="text-[10px] font-bold text-slate-400">Akses Dibatasi</span>';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary font-bold text-xs">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-400 uppercase tracking-widest text-xs">${item.code}</td>
                        <td class="p-4 font-black text-slate-700">${item.name}</td>
                        <td class="p-4 text-center"><span class="bg-slate-100 border border-slate-200 text-slate-600 px-3 py-1 rounded-lg text-xs font-bold uppercase">${item.unit}</span></td>
                        <td class="p-4 text-right ${stockClass} text-lg font-black">${formatDesimal(currentStock)} ${warningIcon}</td>
                        <td class="p-4 text-right text-slate-400 font-bold">${formatDesimal(minStock)}</td>
                        <td class="p-4 text-center print:hidden"><div class="flex items-center justify-center gap-1">${btnAksi}</div></td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// 2. LOAD RIWAYAT PENGAJUAN (DENGAN PAGINATION)
async function loadRequests(status, page = 1) {
    currentRequestStatus = status;
    currentRequestPage = page;

    const tbody = document.getElementById('table-requests');
    const filterElement = document.getElementById('filter-dapur');
    const warehouse_id = filterElement ? filterElement.value : 1;
    
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat riwayat...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_requests&warehouse_id=${warehouse_id}&status=${status}&page=${page}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = `<tr><td colspan="5" class="p-8 text-center text-secondary font-bold italic">Tidak ada pengajuan ditemukan.</td></tr>`;
        } else {
            response.data.forEach((item, index) => {
                let badge = '';
                let st = item.status.toLowerCase();
                if(st === 'menunggu') badge = '<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu</span>';
                else if(st === 'ditolak') badge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';
                else badge = '<span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Berhasil</span>';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-slate-400 font-bold text-xs">${(page-1)*10 + index + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${item.created_at}</td>
                        <td class="p-4 font-black text-slate-700">${item.material_name}</td>
                        <td class="p-4 text-center font-black text-blue-600 bg-blue-50/30">${parseFloat(item.qty_requested)} <span class="text-[10px] text-blue-400 uppercase">${item.unit}</span></td>
                        <td class="p-4 text-center">${badge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination-requests');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    for (let i = 1; i <= totalPages; i++) {
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadRequests('${currentRequestStatus}', ${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

function editData(item) {
    document.getElementById('material_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    document.getElementById('unit').value = item.unit; // Terkunci Readonly di HTML
    document.getElementById('stock').value = parseFloat(item.stock);
    document.getElementById('min_stock').value = parseFloat(item.min_stock);
    openModal('modal-bahan');
}

document.getElementById('formBahan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    if (response.status === 'success') {
        closeModal('modal-bahan');
        loadData(); 
        Swal.fire('Tersimpan!', response.message, 'success');
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

function deleteData(id) {
    Swal.fire({
        title: 'Hapus Bahan Dapur?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e11d48', confirmButtonText: 'Ya, Hapus!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData(); formData.append('id', id);
            const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
            if (response.status === 'success') {
                loadData(); Swal.fire('Dihapus!', response.message, 'success');
            } else {
                Swal.fire('Gagal!', response.message, 'error');
            }
        }
    });
}

async function loadPilarStock() {
    const select = document.getElementById('pilar_material_id');
    select.innerHTML = '<option value="">-- Memuat --</option>';
    const response = await fetchAjax('logic.php?action=read_pilar', 'GET');
    
    if (response.status === 'success') {
        let options = '<option value="">-- Pilih Bahan di Gudang Pilar --</option>';
        response.data.forEach(item => {
            options += `<option value="${item.id}">${item.material_name} (Stok Pusat: ${item.stock} ${item.unit})</option>`;
        });
        select.innerHTML = options;
    }
}

function openModalRequest() {
    const filterElement = document.getElementById('filter-dapur');
    const currentWarehouse = filterElement ? filterElement.value : 1;
    document.getElementById('req_warehouse_id').value = currentWarehouse;
    document.getElementById('formRequest').reset();
    
    loadPilarStock();
    openModal('modal-request');
}

document.getElementById('formRequest').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=submit_request', 'POST', formData);
    if (response.status === 'success') {
        Swal.fire('Berhasil Terkirim!', response.message, 'success');
        closeModal('modal-request');
        loadSemuaData(); 
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});