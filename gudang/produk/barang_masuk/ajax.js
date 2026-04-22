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
        res.materials.forEach(m => { optMat += `<option value="${m.id}">${m.sku_code} - ${m.material_name}</option>`; });
        document.getElementById('material_id').innerHTML = optMat;

        let optSupp = '<option value="">Tanpa Supplier (Lain-lain)</option>';
        res.suppliers.forEach(s => { optSupp += `<option value="${s.id}">${s.name}</option>`; });
        document.getElementById('supplier_id').innerHTML = optSupp;
    }
}

function updateSatuan() {
    const matId = document.getElementById('material_id').value;
    const label = document.getElementById('satuan_label');
    
    if(!matId) { label.value = '-'; return; }
    const mat = materialsData.find(m => m.id == matId);
    if(mat) label.value = mat.unit;
}

function openModalMasuk() {
    document.getElementById('formMasuk').reset();
    document.getElementById('satuan_label').value = '-';
    openModal('modal-masuk');
}
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function switchTab(tab) {
    currentTab = tab;
    loadData(1);
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
            html = `<tr><td colspan="8" class="p-10 text-center text-slate-400 italic font-bold">Belum ada transaksi masuk di filter ini.</td></tr>`;
        } else {
            res.data.forEach((item) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                let sourceBadge = item.source === 'PO' ? 
                    '<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">PO System</span>' : 
                    '<span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest">Manual</span>';

                // Lencana Status Transaksi
                let statusVal = item.status || 'approved'; // default if missing
                let statusBadge = '';
                if(statusVal === 'pending') statusBadge = '<span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-hourglass-half mr-1"></i> Pending</span>';
                else if(statusVal === 'rejected') statusBadge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';
                else statusBadge = '<span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Approved</span>';

                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                let safeSupplier = (item.supplier_name || 'Lain-lain').replace(/'/g, "&apos;");

                html += `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-xs">${tgl}</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mt-1">${item.transaction_no}</div>
                        </td>
                        <td class="p-5 font-black text-slate-800 uppercase text-xs">${item.material_name}</td>
                        <td class="p-5 text-center">${sourceBadge}</td>
                        <td class="p-5 text-xs font-bold text-slate-600">${safeSupplier}</td>
                        <td class="p-5 text-center">
                            <div class="inline-flex items-center gap-1 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                <span class="font-black text-emerald-600 text-sm">+${parseFloat(item.qty)}</span>
                                <span class="text-[10px] font-bold text-emerald-400 uppercase">${item.unit}</span>
                            </div>
                        </td>
                        <td class="p-5 text-center">${statusBadge}</td>
                        <td class="p-5">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-xs text-slate-500 italic max-w-[130px] truncate">${safeNotes}</span>
                                <button onclick="lihatDetail('${item.source}', '${safeSupplier}', '${safeNotes}')" class="text-[10px] bg-slate-100 hover:bg-blue-100 text-blue-600 px-2 py-1 rounded font-black transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-expand"></i> Detail
                                </button>
                            </div>
                        </td>
                        <td class="p-5 text-xs font-bold text-slate-600">${item.admin_name}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function lihatDetail(sumber, supplier, notes) {
    let iconPO = sumber === 'PO' ? '<i class="fa-solid fa-file-contract text-blue-500 mr-2"></i>' : '<i class="fa-solid fa-pen-to-square text-slate-400 mr-2"></i>';
    
    Swal.fire({
        title: 'Detail Informasi Masuk',
        html: `
            <div class="text-left space-y-4 text-sm mt-4">
                <div class="flex gap-4">
                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Sumber Masuk</span>
                        <span class="font-bold text-slate-800">${sumber}</span>
                    </div>
                    <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Supplier</span>
                        <span class="font-bold text-slate-800">${supplier}</span>
                    </div>
                </div>
                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                    <span class="block text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2">Keterangan / Nomor Dokumen PO</span>
                    <span class="font-bold text-slate-700 leading-relaxed">${iconPO} ${notes}</span>
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#3b82f6',
        customClass: { popup: 'rounded-3xl' }
    });
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

document.getElementById('formMasuk').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    const res = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (res.status === 'success') {
        closeModal('modal-masuk');
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