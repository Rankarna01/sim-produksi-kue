let currentTab = 'semua';
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadDataPO(); 
});

function switchTabPO(tabName) {
    currentTab = tabName;
    loadDataPO();
}

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

async function loadDataPO() {
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

                let total = parseFloat(item.total_amount) || 0;
                let paid = parseFloat(item.paid_amount) || 0;
                let sisa = total - paid;

                let payBadge = '';
                if(item.status === 'received') {
                    if(item.payment_status === 'paid') payBadge = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase ml-2">Lunas</span>';
                    else payBadge = '<span class="bg-rose-50 text-rose-500 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase ml-2">Belum Bayar</span>';
                }

                let extraItemText = item.total_items > 1 ? ` (+${item.total_items - 1} lainnya)` : '';

                let tglHtml = `<p class="text-[10px] text-slate-400 font-bold mb-1">Dibuat: ${formatTglTime(item.created_at)} oleh ${item.admin_name}</p>`;
                tglHtml += `<p class="text-[10px] text-blue-500 font-bold mb-1">Dikirim: ${formatTglOnly(item.shipping_date)}</p>`;
                if (item.status === 'received' && item.updated_at) {
                    tglHtml += `<p class="text-[10px] text-emerald-500 font-bold mb-1">Diterima: ${formatTglTime(item.updated_at)}</p>`;
                }

                // Hitungan Keterangan Cetak (Kalau nilainya null dari database, otomatis jadi 0)
                let po_count = item.print_po_count || 0;
                let terima_count = item.print_terima_count || 0;

                // ==================================================
                // LOGIKA TOMBOL PRINT PO (Kunci & Counter)
                // ==================================================
                let printPOBtn = '';
                if (item.print_po_status === 'locked') {
                    printPOBtn = `<button onclick="ajukanIzinCetak(${item.id}, 'po')" class="w-full bg-slate-100 text-slate-400 px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 border border-slate-200 shadow-sm cursor-pointer hover:bg-slate-200 hover:text-slate-600 transition-colors" title="Klik untuk mengajukan izin cetak PO"><i class="fa-solid fa-lock"></i> PO Terkunci (${po_count}x Cetak)</button>`;
                } else if (item.print_po_status === 'pending_approval') {
                    printPOBtn = `<button disabled class="w-full bg-amber-50 text-amber-500 px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 border border-amber-200 shadow-sm cursor-not-allowed"><i class="fa-solid fa-hourglass-half"></i> Menunggu Izin (${po_count}x Cetak)</button>`;
                } else {
                    printPOBtn = `<button onclick="cetakDokumen(${item.id}, 'po', 'print.php')" class="w-full bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 border border-blue-200 shadow-sm"><i class="fa-solid fa-print"></i> Print PO (${po_count}x)</button>`;
                }

                // ==================================================
                // LOGIKA TOMBOL PRINT TERIMA (Kunci & Counter)
                // ==================================================
                let printTerimaBtn = '';
                if (item.print_terima_status === 'locked') {
                    printTerimaBtn = `<button onclick="ajukanIzinCetak(${item.id}, 'terima')" class="flex-1 bg-slate-100 text-slate-400 border border-slate-200 px-2 py-2 rounded-xl text-[10px] font-black flex items-center justify-center gap-1 shadow-sm hover:bg-slate-200 transition-colors" title="Klik untuk mengajukan izin cetak Terima"><i class="fa-solid fa-lock"></i> Terima Terkunci (${terima_count}x)</button>`;
                } else if (item.print_terima_status === 'pending_approval') {
                    printTerimaBtn = `<button disabled class="flex-1 bg-amber-50 text-amber-500 border border-amber-200 px-2 py-2 rounded-xl text-[10px] font-black flex items-center justify-center gap-1 shadow-sm cursor-not-allowed"><i class="fa-solid fa-hourglass-half"></i> Izin Proses (${terima_count}x)</button>`;
                } else {
                    printTerimaBtn = `<button onclick="cetakDokumen(${item.id}, 'terima', 'print_po.php')" class="flex-1 bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-600 border border-emerald-200 px-2 py-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center gap-1 shadow-sm"><i class="fa-solid fa-print"></i> Print Terima (${terima_count}x)</button>`;
                }

                // ==================================================
                // MENYUSUN TOMBOL AKSI KANAN (TERMASUK TOMBOL RETUR)
                // ==================================================
                let actionButtons = '';
                if (item.status === 'received') {
                    
                    // SUNTIKAN FITUR: TOMBOL RETUR MUNCUL JIKA BELUM LUNAS
                    let btnRetur = '';
                    if (item.payment_status !== 'paid') {
                        btnRetur = `
                            <button onclick="openModalRetur(${item.id}, '${item.po_no}')" class="bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 border border-rose-200 px-3 py-2 rounded-xl text-[10px] font-black transition-all flex items-center justify-center gap-1 shadow-sm mt-2 w-full uppercase tracking-widest">
                                <i class="fa-solid fa-rotate-left"></i> Ajukan Retur
                            </button>
                        `;
                    }

                    actionButtons = `
                        ${printPOBtn}
                        <div class="flex gap-2 w-full mt-2">
                            ${printTerimaBtn}
                        </div>
                        ${btnRetur}
                    `;

                } else if (item.status === 'approved') {
                    actionButtons = `
                        ${printPOBtn}
                        <button onclick="openModalTerima(${item.id}, '${item.po_no}')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 shadow-sm mt-2">
                            <i class="fa-solid fa-check-to-slot"></i> Terima Barang
                        </button>
                    `;
                } else {
                    actionButtons = `
                        <button disabled class="w-full bg-slate-50 text-slate-300 px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 border border-slate-100 shadow-sm cursor-not-allowed">
                            <i class="fa-solid fa-print"></i> Belum Bisa Print
                        </button>
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
                        
                        <div class="flex items-center justify-between mt-4">
                            <p class="text-xs text-slate-600 font-bold"><span class="text-slate-400 font-medium">Item:</span> ${item.sample_item} ${extraItemText}</p>
                            <button onclick="lihatDetailPO(${item.id}, '${item.po_no}')" class="text-[10px] bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg font-black transition-colors flex items-center gap-1 shadow-sm">
                                <i class="fa-solid fa-list-ul"></i> Lihat Detail Item
                            </button>
                        </div>
                        
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

// ==================================================
// FITUR LIHAT DETAIL ITEM PO VIA POPUP
// ==================================================
async function lihatDetailPO(po_id, po_no) {
    Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    const res = await fetchAjax(`logic.php?action=get_po_receive&po_id=${po_id}`, 'GET');
    
    if (res.status === 'success') {
        let htmlTabel = `
            <div class="text-left mt-4 bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden custom-scrollbar max-h-60 overflow-y-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-100 border-b border-slate-200 sticky top-0">
                        <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <th class="p-3">Nama Barang</th>
                            <th class="p-3 text-center">Qty</th>
                            <th class="p-3 text-right">Harga Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
        `;
        
        res.items.forEach(i => {
            htmlTabel += `
                <tr class="hover:bg-white transition-colors">
                    <td class="p-3 font-bold text-slate-700 text-xs">${i.material_name}</td>
                    <td class="p-3 text-center font-black text-blue-600 text-xs">${parseFloat(i.qty)} <span class="text-[9px] font-bold text-slate-400 uppercase">${i.unit}</span></td>
                    <td class="p-3 text-right font-bold text-emerald-600 text-xs">${formatRupiah(i.price)}</td>
                </tr>
            `;
        });
        
        htmlTabel += `</tbody></table></div>`;
        
        Swal.fire({
            title: `Daftar Item PO`,
            html: `<p class="text-sm font-black text-blue-600 uppercase tracking-widest">${po_no}</p>` + htmlTabel,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#3b82f6',
            customClass: { popup: 'rounded-3xl' }
        });
    } else {
        Swal.fire('Gagal!', 'Tidak dapat memuat detail item.', 'error');
    }
}


function dummyFitur(namaFitur) {
    Swal.fire('Fitur Mendatang', `${namaFitur} akan diaktifkan segera!`, 'info');
}

// ==================================================
// FUNGSI PRINT & LOCK DENGAN DUA TIPE (PO & TERIMA)
// ==================================================
async function cetakDokumen(po_id, tipe, filePrint) {
    const textDesc = tipe === 'po' ? 'Print PO' : 'Print Tanda Terima';
    
    const confirm = await Swal.fire({
        title: 'Cetak Dokumen?',
        text: `Setelah ${textDesc} dicetak, tombol ini akan TERKUNCI dan menambah hitungan cetak.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563EB',
        confirmButtonText: 'Ya, Cetak & Kunci'
    });

    if (confirm.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'mark_printed');
        formData.append('id', po_id);
        formData.append('tipe', tipe); 
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if(res.status === 'success') {
            window.open(`${filePrint}?id=${po_id}`, '_blank');
            loadDataPO(); 
            Swal.fire({title: 'Berhasil', text: `${textDesc} dicetak dan dihitung.`, icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
        } else {
            Swal.fire('Ups!', res.message, 'error');
        }
    }
}

async function ajukanIzinCetak(po_id, tipe) {
    const textDesc = tipe === 'po' ? 'PO' : 'Tanda Terima';
    
    const confirm = await Swal.fire({
        title: `Ajukan Izin Cetak ${textDesc}?`,
        text: 'Kirim permintaan kepada Manager untuk membuka kunci cetak dokumen ini.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ajukan',
        confirmButtonColor: '#2563EB'
    });

    if (confirm.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'request_print');
        formData.append('id', po_id);
        formData.append('tipe', tipe);
        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if(res.status === 'success') {
            Swal.fire('Berhasil Diajukan', `Izin cetak ${textDesc} dikirim ke Manager.`, 'success');
            loadDataPO();
        }
    }
}

// ==================================================
// FITUR BARU: MODAL RETUR PO & PENGAJUAN RETUR
// ==================================================
let returItems = [];
let activeReturPoId = null;

async function openModalRetur(po_id, po_no) {
    activeReturPoId = po_id;
    document.getElementById('retur-po-title').innerText = 'Pengajuan Retur PO: ' + po_no;
    document.getElementById('retur_reason').value = '';
    openModal('modal-retur-po'); // Pastikan fungsi openModal() ada di ajax_form.js atau file global

    const res = await fetchAjax(`logic.php?action=get_po_retur&po_id=${po_id}`, 'GET');
    
    if (res.status === 'success') {
        returItems = res.items.map(item => ({
            material_id: item.material_id, 
            material_name: item.material_name, 
            unit: item.unit,
            price: item.price,
            qty_terima: parseFloat(item.qty), 
            qty_return: 0 
        }));
        renderReturItems();
    }
}

function renderReturItems() {
    const tbody = document.getElementById('retur-po-items');
    let html = '';
    returItems.forEach((item, idx) => {
        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-slate-700 text-xs">${item.material_name} <span class="text-[9px] uppercase text-slate-400 font-bold ml-1">${item.unit}</span></td>
                <td class="p-4 text-center text-xs font-bold text-slate-500">${formatRupiah(item.price)}</td>
                <td class="p-4 text-center font-black text-blue-600 bg-blue-50/30">${item.qty_terima}</td>
                <td class="p-4 bg-rose-50/30">
                    <input type="number" step="any" min="0" max="${item.qty_terima}" class="w-full px-2 py-1.5 border border-rose-300 rounded font-black text-rose-600 text-center outline-none focus:border-rose-500" value="${item.qty_return}" onchange="updateReturQty(${idx}, this.value, ${item.qty_terima})">
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function updateReturQty(idx, val, maxVal) {
    let num = parseFloat(val);
    if (isNaN(num) || num < 0) num = 0;
    if (num > maxVal) {
        Swal.fire('Tidak Valid!', 'Jumlah retur tidak boleh melebihi jumlah yang diterima.', 'error');
        num = 0;
    }
    returItems[idx].qty_return = num;
    renderReturItems();
}

async function submitReturPO() {
    const reason = document.getElementById('retur_reason').value;
    if (!reason.trim()) {
        Swal.fire('Ups!', 'Mohon isi Alasan Retur agar Owner mengetahui penyebabnya.', 'warning');
        return;
    }

    const isAnyRetur = returItems.some(i => i.qty_return > 0);
    if (!isAnyRetur) {
        Swal.fire('Ups!', 'Silakan isi angka pada kolom Qty Retur minimal 1 barang.', 'warning');
        return;
    }

    const confirm = await Swal.fire({ 
        title: 'Ajukan Retur?', 
        text: 'Pengajuan ini akan dikirim ke Owner. Jika disetujui, stok dan tagihan akan terpotong otomatis.', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: 'Ya, Ajukan Retur', 
        confirmButtonColor: '#e11d48' 
    });
    
    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('action', 'save_retur_po'); 
        formData.append('po_id', activeReturPoId); 
        formData.append('reason', reason); 
        formData.append('items', JSON.stringify(returItems));
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        
        if (res.status === 'success') {
            closeModal('modal-retur-po'); 
            Swal.fire('Berhasil Diajukan!', res.message, 'success'); 
        } else { 
            Swal.fire('Gagal!', res.message, 'error'); 
        }
    }
}