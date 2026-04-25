let currentRequestPage = 1;
let currentRequestStatus = 'semua';
let masterPilarOptions = '<option value="">-- Pilih Bahan --</option>'; // Cache untuk dropdown

document.addEventListener("DOMContentLoaded", () => {
    loadSemuaData(); 
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function formatDesimal(angka) { const num = parseFloat(angka); return num % 1 !== 0 ? num.toFixed(2) : num; }

function loadSemuaData() {
    loadData();
    loadRequests('semua', 1);
}

// 1. STOK DAPUR (Tetap sama)
async function loadData() {
    const tbody = document.getElementById('table-body');
    const filterElement = document.getElementById('filter-dapur');
    const warehouse_id = filterElement ? filterElement.value : 1;
    
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    const response = await fetchAjax(`logic.php?action=read&warehouse_id=${warehouse_id}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) { html = '<tr><td colspan="7" class="p-8 text-center text-secondary">Belum ada data.</td></tr>'; } 
        else {
            response.data.forEach((item, index) => {
                const currentStock = parseFloat(item.stock);
                const minStock = parseFloat(item.min_stock);
                let stockClass = currentStock <= minStock ? "text-danger font-bold" : "text-slate-800"; 
                
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-xs">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-400 uppercase tracking-widest text-xs">${item.code}</td>
                        <td class="p-4 font-black text-slate-700">${item.name}</td>
                        <td class="p-4 text-center"><span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">${item.unit}</span></td>
                        <td class="p-4 text-right ${stockClass} text-lg font-black">${formatDesimal(currentStock)}</td>
                        <td class="p-4 text-right text-slate-400 font-bold">${formatDesimal(minStock)}</td>
                        <td class="p-4 text-center text-[10px] font-bold text-slate-400">Terkunci</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// 2. RIWAYAT REQUEST (Header Level)
async function loadRequests(status, page = 1) {
    currentRequestStatus = status;
    currentRequestPage = page;
    const tbody = document.getElementById('table-requests');
    const warehouse_id = document.getElementById('filter-dapur') ? document.getElementById('filter-dapur').value : 1;
    
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_requests&warehouse_id=${warehouse_id}&status=${status}&page=${page}`, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) { html = `<tr><td colspan="6" class="p-8 text-center text-slate-400 font-bold italic">Tidak ada pengajuan.</td></tr>`; } 
        else {
            response.data.forEach((item, index) => {
                let badge = '';
                let st = item.status.toLowerCase();
                if(st === 'menunggu') badge = '<span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Menunggu</span>';
                else if(st === 'ditolak') badge = '<span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Ditolak</span>';
                else badge = '<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Berhasil</span>';

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-xs text-slate-400">${(page-1)*10 + index + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${item.created_at}</td>
                        <td class="p-4 font-black text-blue-600">${item.request_no}</td>
                        <td class="p-4 text-center font-black text-slate-800 text-lg">${item.total_item} <span class="text-[10px] text-slate-400 uppercase">Bahan</span></td>
                        <td class="p-4 text-center">${badge}</td>
                        <td class="p-4 text-center">
                            <button onclick="lihatDetailReq(${item.id}, '${item.request_no}')" class="text-blue-500 hover:text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-colors"><i class="fa-solid fa-eye"></i> Detail</button>
                        </td>
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

async function lihatDetailReq(header_id, req_no) {
    document.getElementById('det-req-no').innerText = req_no;
    const tbody = document.getElementById('table-detail-req');
    tbody.innerHTML = '<tr><td colspan="3" class="p-4 text-center text-xs">Memuat detail...</td></tr>';
    
    const response = await fetchAjax(`logic.php?action=read_request_detail&header_id=${header_id}`, 'GET');
    if(response.status === 'success') {
        let html = '';
        response.data.forEach((item, idx) => {
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="p-3 text-xs font-bold text-slate-400">${idx+1}</td>
                    <td class="p-3 font-black text-slate-700 uppercase">${item.material_name}</td>
                    <td class="p-3 text-center font-bold text-blue-600 text-base">${parseFloat(item.qty_requested)} <span class="text-[10px] text-slate-400 uppercase">${item.unit}</span></td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
    openModal('modal-detail');
}

// 3. MULTI-ITEM REQUEST LOGIC
async function openModalRequest() {
    document.getElementById('req_warehouse_id').value = document.getElementById('filter-dapur') ? document.getElementById('filter-dapur').value : 1;
    
    // Tarik master dropdown dari pilar sekali saja untuk di cache
    const response = await fetchAjax('logic.php?action=read_pilar', 'GET');
    if (response.status === 'success') {
        masterPilarOptions = '<option value="">-- Pilih Bahan Gudang Pilar --</option>';
        response.data.forEach(item => { masterPilarOptions += `<option value="${item.id}">${item.material_name} (Tersedia: ${item.stock} ${item.unit})</option>`; });
        
        document.getElementById('req-item-container').innerHTML = ''; // Kosongkan
        addRequestRow(); // Tambah 1 baris awal
        openModal('modal-request');
    }
}

function addRequestRow() {
    const container = document.getElementById('req-item-container');
    const rowHTML = `
        <div class="request-row grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-4 border border-slate-200 rounded-2xl items-center relative shadow-sm">
            <div class="md:col-span-6">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Pilih Bahan</label>
                <select name="pilar_id[]" required class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 text-sm font-bold text-slate-700">
                    ${masterPilarOptions}
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jml Permintaan</label>
                <input type="number" step="any" name="qty[]" required class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-center font-black text-blue-600 text-lg" placeholder="0">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Satuan Konversi</label>
                <select name="req_unit[]" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50 text-xs font-bold text-slate-600">
                    <option value="default">Sesuai Gudang</option>
                    <option value="gram">Gram (gr)</option>
                    <option value="ml">Mililiter (ml)</option>
                    <option value="pcs">Pcs</option>
                </select>
            </div>
            <div class="md:col-span-1 flex justify-end">
                <button type="button" onclick="this.closest('.request-row').remove()" class="w-10 h-10 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl flex items-center justify-center transition-all mt-4" title="Hapus Baris"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHTML);
}

document.getElementById('formRequest').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Mengirim Pengajuan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=submit_request', 'POST', formData);
    
    if (response.status === 'success') {
        Swal.fire('Terkirim!', response.message, 'success');
        closeModal('modal-request');
        loadRequests('semua', 1); 
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});