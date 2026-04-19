let cartPO = []; 
let materialsData = []; 
let currentTab = 'semua';
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDataPO(); 
});

function formatTglTime(datetime) {
    if(!datetime) return '-';
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + ' pukul ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatTglOnly(dateStr) {
    if(!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function cariDataPO() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadDataPO(); }, 500);
}

// ==================================================
// RENDER LIST PO (CARD VIEW SESUAI GAMBAR)
// ==================================================
async function loadDataPO() {
    const activeBtn = document.querySelector('button.border-slate-800');
    if (activeBtn) {
        if (activeBtn.innerText.toLowerCase().includes('semua')) currentTab = 'semua';
        else if (activeBtn.innerText.toLowerCase().includes('belum')) currentTab = 'belum_terima';
        else if (activeBtn.innerText.toLowerCase().includes('sudah')) currentTab = 'sudah_terima';
        else if (activeBtn.innerText.toLowerCase().includes('batal')) currentTab = 'dibatalkan';
    }

    const container = document.getElementById('container-list-po');
    container.innerHTML = '<div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></div>';
    
    const search = document.getElementById('search-po') ? document.getElementById('search-po').value : '';
    const res = await fetchAjax(`logic.php?action=read_po&tab=${currentTab}&search=${search}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<div class="p-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 font-bold italic">Belum ada dokumen PO di filter ini.</div>';
        } else {
            res.data.forEach(item => {
                let statusBadge = '';
                if (item.status === 'waiting_approval') statusBadge = '<span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-regular fa-clock mr-1"></i> Menunggu Persetujuan</span>';
                else if (item.status === 'approved') statusBadge = '<span class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-check-double mr-1"></i> Disetujui (Open)</span>';
                else if (item.status === 'received') statusBadge = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-box-open mr-1"></i> Diterima</span>';
                else statusBadge = '<span class="bg-rose-50 text-rose-500 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase"><i class="fa-solid fa-ban mr-1"></i> Ditolak</span>';

                // Hitung Keuangan
                let total = parseFloat(item.total_amount) || 0;
                let paid = parseFloat(item.paid_amount) || 0;
                let sisa = total - paid;

                // Status Bayar Badge
                let payBadge = '';
                if(item.status === 'received') {
                    if(item.payment_status === 'paid') payBadge = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase ml-2">Lunas</span>';
                    else payBadge = '<span class="bg-rose-50 text-rose-500 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase ml-2">Belum Bayar</span>';
                }

                let extraItemText = item.total_items > 1 ? ` (+${item.total_items - 1} lainnya)` : '';

                // HTML Keterangan Tanggal
                let tglHtml = `<p class="text-[10px] text-slate-400 font-bold mb-1">Dibuat: ${formatTglTime(item.created_at)} oleh ${item.admin_name}</p>`;
                tglHtml += `<p class="text-[10px] text-blue-500 font-bold mb-1">Dikirim: ${formatTglOnly(item.shipping_date)}</p>`;
                if (item.status === 'received' && item.updated_at) {
                    tglHtml += `<p class="text-[10px] text-emerald-500 font-bold mb-1">Diterima: ${formatTglTime(item.updated_at)}</p>`;
                }

                // TOMBOL AKSI MENYESUAIKAN GAMBAR
                let actionButtons = '';
                if (item.status === 'received') {
                    actionButtons = `
                        <a href="print.php?id=${item.id}" target="_blank" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 border border-slate-200 shadow-sm">
                            <i class="fa-solid fa-print"></i> Print PO
                        </a>
                        <div class="flex gap-2 w-full mt-2">
                            <a href="#" onclick="dummyFitur('Print Terima')" class="flex-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 px-2 py-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center gap-1 shadow-sm">
                                <i class="fa-solid fa-print"></i> Print Terima
                            </a>
                            <button onclick="dummyFitur('Edit Data PO')" class="bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 px-3 py-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center gap-1 shadow-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <button onclick="dummyFitur('Hapus Data PO')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center gap-1 shadow-sm">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>
                    `;
                } else if (item.status === 'approved') {
                    actionButtons = `
                        <a href="print.php?id=${item.id}" target="_blank" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 border border-slate-200 shadow-sm mb-2">
                            <i class="fa-solid fa-print"></i> Print PO
                        </a>
                        <button onclick="openModalTerima(${item.id}, '${item.po_no}')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-solid fa-check-to-slot"></i> Terima Barang
                        </button>
                    `;
                } else {
                    actionButtons = `
                        <a href="print.php?id=${item.id}" target="_blank" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 border border-slate-200 shadow-sm">
                            <i class="fa-solid fa-print"></i> Print PO
                        </a>
                    `;
                }

                html += `
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between gap-4 transition-all hover:border-blue-300 mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-black text-blue-700 text-lg">${item.po_no}</h4>
                            ${statusBadge} ${payBadge}
                        </div>
                        <h3 class="font-black text-slate-800 text-base mb-2">${item.supplier_name}</h3>
                        
                        ${tglHtml}

                        <p class="text-xs text-slate-600 font-bold mt-4"><span class="text-slate-400 font-medium">Item:</span> ${item.sample_item} ${extraItemText}</p>
                        
                        <div class="flex items-center gap-6 mt-4 border-t border-slate-100 pt-4 w-max">
                            <div><p class="text-[9px] font-black text-slate-400 uppercase">Total</p><p class="text-sm font-black text-slate-800">${formatRupiah(total)}</p></div>
                            <div><p class="text-[9px] font-black text-emerald-400 uppercase">Dibayar</p><p class="text-sm font-black text-emerald-600">${formatRupiah(paid)}</p></div>
                            <div><p class="text-[9px] font-black text-rose-400 uppercase">Sisa</p><p class="text-sm font-black text-rose-600">${formatRupiah(sisa)}</p></div>
                        </div>
                    </div>
                    <div class="flex flex-col w-full md:w-64 mt-4 md:mt-0 justify-center">
                        ${actionButtons}
                    </div>
                </div>
                `;
            });
        }
        container.innerHTML = html;
    }
}

// Dummy Fungsi untuk Tombol yang belum diaktifkan
function dummyFitur(namaFitur) {
    Swal.fire('Fitur Mendatang', `${namaFitur} akan segera diaktifkan pada pengembangan berikutnya!`, 'info');
}

// ==================================================
// FUNGSI BUAT PO DRAFT
// ==================================================
async function loadDraftPO() {
    cartPO = []; 
    document.getElementById('form-po').reset();
    renderCart();

    const res = await fetchAjax('logic.php?action=init_form', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;

        let optSupp = '<option value="">-- Pilih Supplier --</option>';
        res.suppliers.forEach(s => { optSupp += `<option value="${s.id}">${s.name}</option>`; });
        document.getElementById('supplier_id').innerHTML = optSupp;

        let optMat = '<option value="">-- Pilih Barang --</option>';
        res.materials.forEach(m => { optMat += `<option value="${m.id}">[${m.sku_code}] ${m.material_name}</option>`; });
        document.getElementById('item_material').innerHTML = optMat;

        const tbodyPR = document.getElementById('list-pr-pending');
        let htmlPR = '';
        if (res.pr_pending.length === 0) {
            htmlPR = '<tr><td colspan="6" class="py-6 text-center italic opacity-70">Tidak ada permintaan barang yang tertunda.</td></tr>';
        } else {
            res.pr_pending.forEach(pr => {
                const itemData = encodeURIComponent(JSON.stringify({
                    pr_id: pr.id,
                    material_id: pr.material_id,
                    material_name: pr.material_name,
                    qty: parseFloat(pr.qty),
                    unit: pr.unit
                }));

                const d = new Date(pr.created_at);
                const tgl = d.toLocaleDateString('id-ID', {day:'numeric', month:'numeric', year:'numeric'});

                htmlPR += `
                    <tr class="hover:bg-amber-50/50 transition-colors border-b border-amber-200/40 last:border-0">
                        <td class="py-4 pr-2 text-xs font-medium text-amber-800">${tgl}</td>
                        <td class="py-4 pr-2 font-black text-amber-900">${pr.material_name}</td>
                        <td class="py-4 pr-2 font-black text-amber-900">${parseFloat(pr.qty)} <span class="text-[10px] font-bold uppercase text-amber-700/70">${pr.unit}</span></td>
                        <td class="py-4 pr-2 text-xs font-bold text-amber-800">${pr.requested_by_name}</td>
                        <td class="py-4 pr-2 text-xs italic text-amber-700/70 truncate max-w-[150px]">${pr.notes || '-'}</td>
                        <td class="py-4 text-right">
                            <button type="button" onclick="masukkanKePO(this, '${itemData}')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-blue-200">
                                Masukkan ke PO
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbodyPR.innerHTML = htmlPR;
    }
}

function masukkanKePO(btn, encodedData) {
    const item = JSON.parse(decodeURIComponent(encodedData));
    const exist = cartPO.findIndex(c => c.material_id === item.material_id);
    
    if (exist !== -1) cartPO[exist].qty += item.qty;
    else cartPO.push(item); 

    renderCart();
    btn.className = "bg-amber-100 text-amber-500 px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest cursor-not-allowed";
    btn.innerHTML = "<i class='fa-solid fa-check'></i> Ditambahkan";
    btn.disabled = true;
}

function tambahItemManual() {
    const material_id = document.getElementById('item_material').value;
    const qty = parseFloat(document.getElementById('item_qty').value);

    if (!material_id || isNaN(qty) || qty <= 0) {
        Swal.fire('Ups!', 'Pilih barang dan masukkan jumlah yang benar.', 'warning'); return;
    }

    const mat = materialsData.find(m => m.id == material_id);
    const exist = cartPO.findIndex(c => c.material_id == material_id);
    
    if (exist !== -1) cartPO[exist].qty += qty;
    else cartPO.push({ pr_id: null, material_id: mat.id, material_name: mat.material_name, qty: qty, unit: mat.unit });

    renderCart();
    document.getElementById('item_material').value = '';
    document.getElementById('item_qty').value = 1;
}

function hapusCart(index) {
    cartPO.splice(index, 1);
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cart-po');
    let html = '';

    if (cartPO.length === 0) {
        html = '<tr><td colspan="3" class="p-8 text-center text-slate-400 italic text-xs">Belum ada item yang ditambahkan.</td></tr>';
    } else {
        cartPO.forEach((item, index) => {
            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-5 font-black text-slate-700 uppercase text-xs">${item.material_name}</td>
                    <td class="p-5 text-center">
                        <span class="font-black text-blue-600 text-sm">${item.qty}</span>
                        <span class="text-[10px] font-black uppercase text-slate-400 ml-1">${item.unit}</span>
                    </td>
                    <td class="p-5 text-center">
                        <button type="button" onclick="hapusCart(${index})" class="w-6 h-6 rounded bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto shadow-sm">
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    tbody.innerHTML = html;
}

async function simpanPO() {
    const supplier_id = document.getElementById('supplier_id').value;
    const shipping_date = document.getElementById('shipping_date').value;

    if (!supplier_id || !shipping_date) { Swal.fire('Data Belum Lengkap', 'Mohon lengkapi Supplier dan Tanggal Pengiriman!', 'warning'); return; }
    if (cartPO.length === 0) { Swal.fire('Keranjang Kosong', 'Tambahkan minimal 1 item barang untuk dibeli!', 'warning'); return; }

    Swal.fire({ title: 'Menerbitkan PO...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const formData = new FormData();
    formData.append('action', 'save_po');
    formData.append('supplier_id', supplier_id);
    formData.append('shipping_date', shipping_date);
    formData.append('cart', JSON.stringify(cartPO));

    const res = await fetchAjax('logic.php', 'POST', formData);

    if (res.status === 'success') {
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => {
            document.querySelector('button[x-show="tab === \'buat_po\'"]').click(); // Kembali ke list
        });
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
}

// ==================================================
// FUNGSI TERIMA BARANG (MODAL & OTORISASI MANAGER)
// ==================================================
let receiveItems = [];
let activeReceivePoId = null;

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

async function openModalTerima(po_id, po_no) {
    activeReceivePoId = po_id;
    document.getElementById('terima-po-title').innerText = 'Penerimaan Barang PO: ' + po_no;
    openModal('modal-terima-barang');

    const res = await fetchAjax(`logic.php?action=get_po_receive&po_id=${po_id}`, 'GET');
    if (res.status === 'success') {
        receiveItems = res.items.map(item => ({
            material_id: item.material_id,
            material_name: item.material_name,
            qty_po: parseFloat(item.qty),
            qty_terima: parseFloat(item.qty),
            unit: item.unit,
            price: '',
            exp_date: ''
        }));

        let optMat = '<option value="">-- Pilih Barang Lain --</option>';
        res.materials.forEach(m => { 
            optMat += `<option value="${m.id}" data-name="${m.material_name}" data-unit="${m.unit}">${m.material_name}</option>`; 
        });
        document.getElementById('terima_extra_item').innerHTML = optMat;

        renderTerimaItems();
    }
}

function renderTerimaItems() {
    const tbody = document.getElementById('terima-po-items');
    let html = '';
    receiveItems.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-slate-700 text-xs">${item.material_name}</td>
                <td class="p-4 text-center font-bold text-slate-500">${item.qty_po || '-'}</td>
                <td class="p-4">
                    <input type="number" step="any" class="w-full px-2 py-1.5 border border-slate-300 rounded font-black text-blue-600 text-center outline-none" value="${item.qty_terima}" onchange="receiveItems[${idx}].qty_terima = this.value">
                </td>
                <td class="p-4 text-center text-xs font-bold text-slate-500">${item.unit}</td>
                <td class="p-4">
                    <input type="number" step="any" placeholder="Harga Satuan..." class="w-full px-2 py-1.5 border border-slate-300 rounded font-bold text-emerald-600 outline-none" value="${item.price}" onchange="receiveItems[${idx}].price = this.value">
                </td>
                <td class="p-4">
                    <input type="date" class="w-full px-2 py-1.5 border border-slate-300 rounded text-xs font-bold text-slate-700 outline-none" value="${item.exp_date}" onchange="receiveItems[${idx}].exp_date = this.value">
                </td>
                <td class="p-4 text-center">
                    <button type="button" onclick="removeTerimaItem(${idx})" class="w-6 h-6 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// POPUP KODE OTORISASI UNTUK TAMBAH BARANG LUAR PO
async function addExtraTerimaItem() {
    const select = document.getElementById('terima_extra_item');
    if (!select.value) return;

    const { value: code } = await Swal.fire({
        title: 'Otorisasi Dibutuhkan',
        text: 'Masukkan kode Manager/Admin untuk menambah barang di luar kesepakatan PO awal.',
        input: 'text',
        inputPlaceholder: 'Kode Otorisasi',
        showCancelButton: true,
        confirmButtonColor: '#2563EB',
    });

    if (code) {
        const selectedOpt = select.options[select.selectedIndex];
        receiveItems.push({
            material_id: select.value,
            material_name: selectedOpt.dataset.name,
            qty_po: 0,
            qty_terima: 1,
            unit: selectedOpt.dataset.unit,
            price: '',
            exp_date: ''
        });
        
        renderTerimaItems();
        select.value = '';
        Swal.fire({ title: 'Otorisasi Berhasil', text: 'Barang tambahan dimasukkan ke daftar.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    }
}

function removeTerimaItem(idx) {
    receiveItems.splice(idx, 1);
    renderTerimaItems();
}

async function submitTerimaBarang() {
    if(receiveItems.length === 0) { Swal.fire('Ups!', 'Daftar penerimaan kosong.', 'warning'); return; }

    const hasEmptyPrice = receiveItems.some(item => item.price === '' || item.price === null);
    if(hasEmptyPrice) {
        Swal.fire('Harga Kosong', 'Mohon isi Harga Satuan untuk setiap barang yang diterima sesuai dengan nota/surat jalan dari Supplier.', 'error');
        return;
    }

    const confirm = await Swal.fire({ title: 'Simpan Penerimaan?', text: 'Stok barang akan otomatis ditambahkan ke Inventory Gudang, dan total tagihan akan tercatat.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Terima Barang', confirmButtonColor: '#10B981' });
    
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('action', 'save_receive_po');
        formData.append('po_id', activeReceivePoId);
        formData.append('items', JSON.stringify(receiveItems));

        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            closeModal('modal-terima-barang');
            Swal.fire('Berhasil!', res.message, 'success');
            loadDataPO(); 
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}