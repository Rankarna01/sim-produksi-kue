document.addEventListener('DOMContentLoaded', () => {
    loadPOApproval(); 
    loadDataBarangDropdown();
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ==========================================
// 1. RENDER TABEL PERMINTAAN PEMBELIAN (PR)
// ==========================================
async function loadPermintaan(page = 1) {
    const tbody = document.getElementById('list-permintaan');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const res = await fetchAjax(`logic.php?action=read_permintaan&page=${page}`, 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 font-bold italic">Tidak ada antrean permintaan pembelian (PR).</td></tr>';
        } else {
            res.data.forEach((item, index) => {
                const no = (page - 1) * 10 + index + 1;
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-slate-400 font-mono text-xs">${no}</td>
                        <td class="p-5">
                            <div class="font-black text-slate-800 text-xs tracking-tighter uppercase">${item.request_no}</div>
                            <div class="text-[10px] text-slate-400 font-bold">${tgl}</div>
                        </td>
                        <td class="p-5">
                            <div class="font-bold text-slate-700 text-sm">${item.material_name} <span class="text-[10px] text-slate-400 font-normal uppercase">(${item.sku_code})</span></div>
                            <div class="text-[10px] text-slate-500 italic mt-0.5">${item.notes || 'Tidak ada catatan'}</div>
                        </td>
                        <td class="p-5 text-center font-black text-blue-600 text-base">
                            ${parseFloat(item.qty)} <span class="text-[10px] text-slate-400 uppercase tracking-widest">${item.unit}</span>
                        </td>
                        <td class="p-5 font-bold text-slate-600 text-xs uppercase">
                            <i class="fa-solid fa-user-pen mr-1 text-slate-400"></i> ${item.requested_by_name || 'Admin'}
                        </td>
                        <td class="p-5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesKePO(${item.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-cart-arrow-down"></i> Proses ke PO
                                </button>
                                <button onclick="tolakPermintaan(${item.id})" class="bg-red-50 hover:bg-red-500 text-red-500 hover:text-white w-8 h-8 rounded-xl transition-all flex items-center justify-center">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(res.total_pages, res.current_page);
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination-permintaan');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    for (let i = 1; i <= totalPages; i++) {
        const active = i === current ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200';
        html += `<button onclick="loadPermintaan(${i})" class="w-8 h-8 rounded-lg font-black text-xs transition-all ${active}">${i}</button>`;
    }
    container.innerHTML = html;
}

// ==========================================
// 2. FUNGSI BUAT REQUEST BARU (MANUAL)
// ==========================================
function bukaModalBuatRequest() {
    document.getElementById('formBuatReq').reset();
    openModal('modal-buat-req');
}

async function loadDataBarangDropdown() {
    const select = document.getElementById('pilar_material_id');
    const res = await fetchAjax('logic.php?action=get_materials', 'GET');
    if (res.status === 'success' && select) {
        let options = '<option value="">-- Pilih Bahan Baku --</option>';
        res.data.forEach(item => { options += `<option value="${item.id}">${item.material_name} (Stok: ${parseFloat(item.stock)} ${item.unit})</option>`; });
        select.innerHTML = options;
    }
}

const formReq = document.getElementById('formBuatReq');
if(formReq) {
    formReq.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'submit_request');

        Swal.fire({ title: 'Mengajukan...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if (res.status === 'success') {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
            closeModal('modal-buat-req');
            loadPermintaan(1);
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    });
}

async function prosesKePO(id) {
    const confirm = await Swal.fire({ title: 'Proses Permintaan?', text: "Pindah ke Draft PO.", icon: 'question', showCancelButton: true, confirmButtonColor: '#2563EB', confirmButtonText: 'Ya, Proses' });
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'proses_ke_po'); formData.append('id', id);
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Siap Jadi PO!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
            loadPermintaan(1);
        } else Swal.fire('Gagal!', res.message, 'error');
    }
}

async function tolakPermintaan(id) {
    const confirm = await Swal.fire({ title: 'Tolak Permintaan?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Tolak' });
    if (confirm.isConfirmed) {
        const formData = new FormData(); formData.append('action', 'tolak_permintaan'); formData.append('id', id);
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            Swal.fire({ title: 'Ditolak!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
            loadPermintaan(1);
        } else Swal.fire('Gagal!', res.message, 'error');
    }
}

// ==================================================
// 3. FUNGSI PERSETUJUAN PO 
// ==================================================
async function loadPOApproval() {
    const container = document.getElementById('list-po-approval');
    if(!container) return;

    container.innerHTML = '<div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></div>';

    const res = await fetchAjax('logic.php?action=read_po_approval', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<div class="p-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 font-bold italic">Tidak ada dokumen PO yang menunggu persetujuan.</div>';
        } else {
            res.data.forEach(item => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'numeric', month:'numeric', year:'numeric'});
                let extraItemText = item.total_items > 1 ? ` (+${item.total_items - 1} item)` : '';

                html += `
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-amber-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-all hover:shadow-md relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-400"></div>
                    <div class="pl-2 flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-black text-blue-600 text-lg">${item.po_no}</h4>
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-bold uppercase animate-pulse">Pending Approval</span>
                        </div>
                        <h3 class="font-black text-slate-800 text-base mb-1">${item.supplier_name}</h3>
                        <p class="text-sm text-slate-600 font-medium mb-1">${item.sample_item} <span class="text-xs text-slate-400">${extraItemText}</span></p>
                        <p class="text-[10px] text-slate-400 font-bold">Dibuat: ${tgl} oleh ${item.admin_name}</p>
                    </div>
                    <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
                        <button onclick="viewDetailPO(${item.id}, '${item.po_no}', '${item.supplier_name}')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm">Detail</button>
                        <button onclick="prosesApprovalPO(${item.id}, 'approved')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1"><i class="fa-solid fa-check"></i> Approve</button>
                        <button onclick="prosesApprovalPO(${item.id}, 'rejected')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1"><i class="fa-solid fa-xmark"></i> Reject</button>
                    </div>
                </div>
                `;
            });
        }
        container.innerHTML = html;
    }
}

async function viewDetailPO(po_id, po_no, supplier_name) {
    document.getElementById('detail-po-no').innerText = 'PO #' + po_no;
    document.getElementById('detail-supplier').innerText = supplier_name;
    document.getElementById('approve_po_id').value = po_id;
    
    const tbody = document.getElementById('detail-po-items');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin"></i></td></tr>';
    openModal('modal-detail-po');

    const res = await fetchAjax(`logic.php?action=get_po_detail&po_id=${po_id}`, 'GET');
    if (res.status === 'success') {
        let html = '';
        res.data.forEach((item, idx) => {
            let harga = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.price);
            
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="p-4 text-center font-bold text-slate-400 align-middle">${idx + 1}</td>
                    <td class="p-4 align-middle">
                        <div class="font-bold text-slate-700">${item.material_name}</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-widest">${item.sku_code}</div>
                        <input type="hidden" name="detail_id[]" value="${item.id}">
                        <input type="hidden" name="price[]" value="${item.price}">
                    </td>
                    <td class="p-4 text-center font-bold text-slate-600 text-xs align-middle">${harga}</td>
                    <td class="p-4 text-right align-middle">
                        <div class="flex items-center justify-end gap-2">
                            <input type="number" name="qty[]" value="${parseFloat(item.qty)}" step="0.01" min="0" max="${parseFloat(item.qty)}" class="w-20 px-2 py-1.5 border border-slate-300 rounded-lg text-center font-black text-blue-600 outline-none focus:border-blue-600 bg-white transition-all shadow-sm">
                            <span class="text-[10px] font-bold text-slate-400 uppercase w-8 text-left">${item.unit}</span>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        const btnReject = document.getElementById('btn-reject-po');
        if(btnReject) btnReject.onclick = () => prosesRejectPO(po_id);
    }
}

async function prosesRejectPO(po_id) {
    const confirm = await Swal.fire({ title: 'Tolak PO ini?', text: 'PO akan dibatalkan secara permanen.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#F43F5E', confirmButtonText: 'Ya, Tolak' });
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'update_po_status'); formData.append('po_id', po_id); formData.append('status', 'rejected');
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            closeModal('modal-detail-po'); Swal.fire({ title: 'Ditolak!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }); loadPOApproval(); 
        } else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}

const formApprovePO = document.getElementById('form-approve-po');
if(formApprovePO) {
    formApprovePO.addEventListener('submit', async function(e) {
        e.preventDefault();
        const confirm = await Swal.fire({ title: 'Setujui PO ini?', text: 'Kuantitas barang akan disahkan sesuai form.', icon: 'question', showCancelButton: true, confirmButtonColor: '#2563EB', confirmButtonText: 'Ya, Setujui' });

        if (confirm.isConfirmed) {
            Swal.fire({ title: 'Menyimpan Approval...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
            const formData = new FormData(this); formData.append('action', 'update_po_status'); formData.append('status', 'approved');
            const res = await fetchAjax('logic.php', 'POST', formData);
            
            if (res.status === 'success') {
                closeModal('modal-detail-po'); Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }); loadPOApproval();
            } else { Swal.fire('Gagal!', res.message, 'error'); }
        }
    });
}

async function prosesApprovalPO(po_id, status) {
    const isApprove = status === 'approved';
    const confirm = await Swal.fire({ title: isApprove ? 'Setujui PO ini?' : 'Tolak PO ini?', icon: isApprove ? 'question' : 'warning', showCancelButton: true, confirmButtonColor: isApprove ? '#2563EB' : '#F43F5E', confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak' });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'update_po_status'); formData.append('po_id', po_id); formData.append('status', status);
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            closeModal('modal-detail-po'); Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }); loadPOApproval(); 
        } else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}

// ==========================================================
// 4. FUNGSI TRANSAKSI MANUAL (BARANG MASUK)
// ==========================================================
async function loadManualApproval() {
    const tbody = document.getElementById('table-manual-approval');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read_manual', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada transaksi masuk manual yang menunggu persetujuan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${tgl}<br><span class="text-[9px] text-slate-400 tracking-widest uppercase">${item.transaction_no}</span></td>
                        <td class="p-4 font-black text-slate-700 uppercase">${item.material_name}</td>
                        <td class="p-4 text-center font-black text-emerald-600 bg-emerald-50/50 border-l border-r border-slate-100">+${parseFloat(item.qty)} <span class="text-[10px] uppercase font-bold text-emerald-400">${item.unit}</span></td>
                        <td class="p-4 text-xs text-slate-500 italic max-w-[150px] truncate" title="${safeNotes}">${safeNotes}</td>
                        <td class="p-4 text-xs font-bold text-slate-600">${item.admin_name}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesManual(${item.id}, 'approve')" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Setujui & Tambah Stok"><i class="fa-solid fa-check"></i></button>
                                <button onclick="prosesManual(${item.id}, 'reject')" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Tolak Transaksi"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

async function prosesManual(id, actionType) {
    const isApprove = actionType === 'approve';
    const confirm = await Swal.fire({ title: isApprove ? 'Setujui Transaksi?' : 'Tolak Transaksi?', text: isApprove ? 'Stok Gudang akan BERTAMBAH.' : 'Transaksi dibatalkan.', icon: isApprove ? 'question' : 'warning', showCancelButton: true, confirmButtonColor: isApprove ? '#10b981' : '#f43f5e', confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak' });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
        const formData = new FormData(); formData.append('action', isApprove ? 'approve_manual' : 'reject_manual'); formData.append('id', id);
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') { Swal.fire('Berhasil!', res.message, 'success'); loadManualApproval(); } 
        else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}

// ==========================================================
// 5. FUNGSI BARANG KELUAR MANUAL (FITUR BARU)
// ==========================================================
async function loadKeluarApproval() {
    const tbody = document.getElementById('table-keluar-approval');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read_keluar', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada transaksi keluar yang menunggu persetujuan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                let safeNotes = (item.notes || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${tgl}<br><span class="text-[9px] text-slate-400 tracking-widest uppercase">${item.transaction_no}</span></td>
                        <td class="p-4 font-black text-slate-700 uppercase">${item.material_name}</td>
                        <td class="p-4 text-center font-black text-rose-600 bg-rose-50/50 border-l border-r border-slate-100">-${parseFloat(item.qty)} <span class="text-[10px] uppercase font-bold text-rose-400">${item.unit}</span></td>
                        <td class="p-4 text-xs font-bold text-slate-700 uppercase">${item.status}<br><span class="text-[10px] text-slate-500 normal-case italic font-normal" title="${safeNotes}">${safeNotes}</span></td>
                        <td class="p-4 text-xs font-bold text-slate-600">${item.admin_name}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesKeluar(${item.id}, 'approve')" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Setujui & Kurangi Stok"><i class="fa-solid fa-check"></i></button>
                                <button onclick="prosesKeluar(${item.id}, 'reject')" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Tolak Transaksi"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

async function prosesKeluar(id, actionType) {
    const isApprove = actionType === 'approve';
    const confirm = await Swal.fire({ title: isApprove ? 'Setujui Barang Keluar?' : 'Tolak Barang Keluar?', text: isApprove ? 'Stok Gudang akan DIPOTONG.' : 'Transaksi dibatalkan.', icon: isApprove ? 'question' : 'warning', showCancelButton: true, confirmButtonColor: isApprove ? '#10b981' : '#f43f5e', confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak' });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
        const formData = new FormData(); formData.append('action', isApprove ? 'approve_keluar' : 'reject_keluar'); formData.append('id', id);
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') { Swal.fire('Berhasil!', res.message, 'success'); loadKeluarApproval(); } 
        else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}

// ==========================================================
// 6. FUNGSI IZIN CETAK
// ==========================================================
async function loadIzinCetak() {
    const tbody = document.getElementById('table-izin-cetak');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read_izin_cetak', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="5" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada permohonan izin cetak dokumen.</td></tr>';
        } else {
            let no = 1;
            res.data.forEach((item) => {
                const d = new Date(item.updated_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'});

                if (item.print_po_status === 'pending_approval') {
                    html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${no++}</td>
                        <td class="p-4 font-black text-slate-700">${item.supplier_name}<br><span class="text-[9px] text-slate-400 uppercase">Req: ${tgl}</span></td>
                        <td class="p-4 font-black text-blue-600 tracking-widest">${item.po_no}</td>
                        <td class="p-4"><span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-bold uppercase"><i class="fa-solid fa-print mr-1"></i> Izin Cetak Ulang PO</span></td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesIzinCetak(${item.id}, 'po', 'approve')" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Izinkan</button>
                                <button onclick="prosesIzinCetak(${item.id}, 'po', 'reject')" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Tolak</button>
                            </div>
                        </td>
                    </tr>`;
                }

                if (item.print_terima_status === 'pending_approval') {
                    html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${no++}</td>
                        <td class="p-4 font-black text-slate-700">${item.supplier_name}<br><span class="text-[9px] text-slate-400 uppercase">Req: ${tgl}</span></td>
                        <td class="p-4 font-black text-emerald-600 tracking-widest">${item.po_no}</td>
                        <td class="p-4"><span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-lg text-[10px] font-bold uppercase"><i class="fa-solid fa-print mr-1"></i> Izin Cetak Tanda Terima</span></td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesIzinCetak(${item.id}, 'terima', 'approve')" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Izinkan</button>
                                <button onclick="prosesIzinCetak(${item.id}, 'terima', 'reject')" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">Tolak</button>
                            </div>
                        </td>
                    </tr>`;
                }
            });
        }
        tbody.innerHTML = html;
    }
}

async function prosesIzinCetak(id, tipe, keputusan) {
    const isApprove = keputusan === 'approve';
    const confirm = await Swal.fire({ title: isApprove ? 'Berikan Izin Cetak?' : 'Tolak Izin Cetak?', text: isApprove ? 'Gembok dokumen ini akan terbuka 1x lagi.' : 'Dokumen tetap terkunci.', icon: 'question', showCancelButton: true, confirmButtonColor: isApprove ? '#10b981' : '#f43f5e', confirmButtonText: 'Ya, Lanjutkan' });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'proses_izin_cetak'); formData.append('id', id); formData.append('tipe', tipe); formData.append('keputusan', keputusan);
        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if (res.status === 'success') { Swal.fire('Berhasil!', res.message, 'success'); loadIzinCetak(); } 
        else { Swal.fire('Gagal!', res.message, 'error'); }
    }
}

// ==========================================================
// 7. FUNGSI HISTORI SEMUA PERSETUJUAN
// ==========================================================
let searchHistoriTimeout = null;

function cariHistori() {
    clearTimeout(searchHistoriTimeout);
    searchHistoriTimeout = setTimeout(() => { loadHistori(); }, 500);
}

async function loadHistori() {
    const tbody = document.getElementById('table-histori');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-slate-600 text-2xl"></i></td></tr>';
    
    const modul = document.getElementById('histori_modul').value;
    const status = document.getElementById('histori_status').value;
    const search = document.getElementById('histori_search').value;

    const res = await fetchAjax(`logic.php?action=read_histori&modul=${modul}&status=${status}&search=${search}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada riwayat persetujuan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const d = new Date(item.tgl_proses);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                
                let badgeStatus = '';
                if(item.status === 'processed' || item.status === 'approved') badgeStatus = '<span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-check mr-1"></i> Selesai</span>';
                else badgeStatus = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>';

                let safeDetail = (item.detail || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${tgl}</td>
                        <td class="p-4 font-black text-slate-700 uppercase">${item.modul}</td>
                        <td class="p-4 font-black text-blue-600 tracking-widest">${item.ref_no}</td>
                        <td class="p-4 text-xs text-slate-500 italic max-w-[200px] truncate" title="${safeDetail}">${safeDetail}</td>
                        <td class="p-4 text-center">${badgeStatus}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// ==========================================================
// FUNGSI PERSETUJUAN RETUR PO (FITUR BARU)
// ==========================================================
async function loadReturPOApproval() {
    const tbody = document.getElementById('table-retur-po-approval');
    if(!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read_retur_po', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 italic font-bold">Tidak ada pengajuan retur PO yang menunggu persetujuan.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) + ' ' + d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                let safeReason = (item.reason || '-').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4 text-xs font-bold text-slate-500">${tgl}<br><span class="text-[9px] text-slate-400 uppercase">Oleh: ${item.admin_name}</span></td>
                        <td class="p-4 font-black text-blue-600 tracking-widest">${item.po_no}<br><span class="text-xs text-slate-600 font-bold uppercase tracking-normal">${item.supplier_name}</span></td>
                        <td class="p-4 font-black text-slate-700 uppercase">${item.material_name}<br><span class="text-rose-500 font-black text-sm">-${parseFloat(item.qty_return)} <span class="text-[10px] text-rose-400 uppercase">${item.unit}</span></span></td>
                        <td class="p-4 text-xs text-slate-500 italic max-w-[150px] truncate" title="${safeReason}">${safeReason}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="prosesReturPO(${item.id}, 'approve')" class="bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Setujui Retur"><i class="fa-solid fa-check"></i></button>
                                <button onclick="prosesReturPO(${item.id}, 'reject')" class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-500 hover:text-white w-8 h-8 rounded-lg flex items-center justify-center transition-all shadow-sm" title="Tolak Retur"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

async function prosesReturPO(id, keputusan) {
    const isApprove = keputusan === 'approve';
    const confirm = await Swal.fire({ 
        title: isApprove ? 'Setujui Retur PO?' : 'Tolak Retur PO?', 
        text: isApprove ? 'Stok Gudang dan Tagihan PO akan langsung dipotong!' : 'Pengajuan retur akan dibatalkan.', 
        icon: isApprove ? 'question' : 'warning', 
        showCancelButton: true, 
        confirmButtonColor: isApprove ? '#10b981' : '#f43f5e', 
        confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak' 
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
        const formData = new FormData(); 
        formData.append('action', 'proses_retur_po'); 
        formData.append('id', id); 
        formData.append('keputusan', keputusan);
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') { 
            Swal.fire('Berhasil!', res.message, 'success'); 
            loadReturPOApproval(); 
        } else { 
            Swal.fire('Gagal!', res.message, 'error'); 
        }
    }
}