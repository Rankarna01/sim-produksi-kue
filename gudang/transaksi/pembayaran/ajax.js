let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    // Set default filter date (Bulan ini) untuk optimasi
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = today.toISOString().split('T')[0];
    
    // Set format waktu input pembayaran = sekarang
    setDatetimeNow('pay_date');

    loadBills();
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

function formatTglTime(datetime) {
    if(!datetime) return '-';
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' (' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ')';
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parseFloat(angka) || 0);
}

function setDatetimeNow(elementId) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById(elementId).value = now.toISOString().slice(0, 16);
}

function cariBills() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { loadBills(); }, 500);
}

// ==========================================
// RENDER KARTU TAGIHAN (SESUAI GAMBAR 1)
// ==========================================
async function loadBills() {
    const container = document.getElementById('container-bills');
    container.innerHTML = '<div class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i> Memuat tagihan...</div>';

    const status = document.getElementById('filter_status').value;
    const search = document.getElementById('search-bill').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;

    const res = await fetchAjax(`logic.php?action=read_bills&status=${status}&search=${search}&start_date=${start_date}&end_date=${end_date}`, 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<div class="p-10 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 font-bold italic">Tidak ada tagihan ditemukan pada filter ini.</div>';
        } else {
            res.data.forEach(item => {
                // LOGIKA BADGE STATUS (Merah/Orange/Hijau)
                let statusBadge = '';
                if(item.payment_status === 'unpaid') statusBadge = '<span class="bg-rose-50 text-rose-500 px-3 py-1 rounded-full text-[10px] font-black border border-rose-100 uppercase tracking-widest">Belum Bayar</span>';
                else if(item.payment_status === 'partial') statusBadge = '<span class="bg-orange-50 text-orange-500 px-3 py-1 rounded-full text-[10px] font-black border border-orange-100 uppercase tracking-widest">Sebagian</span>';
                else statusBadge = '<span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black border border-emerald-100 uppercase tracking-widest">Lunas</span>';

                // KEUANGAN
                let total = parseFloat(item.total_amount) || 0;
                let paid = parseFloat(item.paid_amount) || 0;
                let sisa = total - paid;

                // RENDER DETAIL ITEM DALAM KOTAK ABU-ABU
                let itemsHtml = '';
                if(item.items && item.items.length > 0){
                    itemsHtml = '<div class="bg-slate-50 border border-slate-100 rounded-xl p-3 w-full md:w-80 mt-3">';
                    itemsHtml += '<p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Detail Barang:</p>';
                    item.items.forEach(i => {
                        itemsHtml += `
                            <div class="flex justify-between items-center mb-1 text-xs">
                                <span class="font-bold text-slate-700 truncate w-32">${i.material_name}</span>
                                <span class="text-slate-500 w-20 text-right">@ ${formatRupiah(i.price)}</span>
                                <span class="font-black text-slate-800 w-16 text-right">${parseFloat(i.qty)} <span class="text-[9px]">${i.unit}</span></span>
                            </div>
                        `;
                    });
                    itemsHtml += '</div>';
                }

                // KARTU UI (Berdasarkan Image 1)
                html += `
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between gap-4 transition-all hover:shadow-md">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h4 class="font-black text-blue-700 text-lg">${item.po_no}</h4>
                            ${statusBadge}
                        </div>
                        <h3 class="font-black text-slate-800 text-base mb-1">${item.supplier_name}</h3>
                        <p class="text-[10px] text-slate-400 font-bold">Dibuat: ${formatTglTime(item.created_at)} • ${item.admin_name}</p>
                        <p class="text-[10px] text-slate-400 font-bold mb-2">Diterima: ${formatTglTime(item.updated_at)}</p>
                        
                        ${itemsHtml}

                        <div class="flex items-center gap-6 mt-4 pt-2">
                            <div><p class="text-[9px] font-black text-slate-400 uppercase">Total</p><p class="text-sm font-black text-slate-800">${formatRupiah(total)}</p></div>
                            <div><p class="text-[9px] font-black text-emerald-400 uppercase">Dibayar</p><p class="text-sm font-black text-emerald-600">${formatRupiah(paid)}</p></div>
                            <div><p class="text-[9px] font-black text-rose-400 uppercase">Sisa</p><p class="text-sm font-black text-rose-600">${formatRupiah(sisa)}</p></div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center items-end w-full md:w-auto mt-4 md:mt-0 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                        ${item.payment_status !== 'paid' ? `
                            <button onclick="openModalBayar(${item.id}, '${item.po_no}')" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
                                <i class="fa-solid fa-wallet"></i> Bayar Tagihan
                            </button>
                        ` : `
                            <div class="text-emerald-500 font-black flex items-center gap-2 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100">
                                <i class="fa-solid fa-circle-check"></i> Selesai (Lunas)
                            </div>
                        `}
                    </div>
                </div>
                `;
            });
        }
        container.innerHTML = html;
    }
}

// ==========================================
// MODAL PEMBAYARAN (GAMBAR 2 & 3)
// ==========================================
async function openModalBayar(po_id, po_no) {
    document.getElementById('modal-po-no').innerText = po_no;
    document.getElementById('pay_po_id').value = po_id;
    document.getElementById('form-pembayaran').reset();
    setDatetimeNow('pay_date'); // Reset jam ke saat ini

    openModal('modal-bayar');

    const res = await fetchAjax(`logic.php?action=get_payment_data&po_id=${po_id}`, 'GET');
    
    if (res.status === 'success') {
        // 1. SET SUMMARY KEUANGAN
        let total = parseFloat(res.po_info.total_amount);
        let paid = parseFloat(res.po_info.paid_amount);
        let sisa = total - paid;

        document.getElementById('modal-total').innerText = formatRupiah(total);
        document.getElementById('modal-dibayar').innerText = formatRupiah(paid);
        document.getElementById('modal-sisa').innerText = formatRupiah(sisa);
        
        // Batasi max bayar
        document.getElementById('pay_max_amount').value = sisa;
        document.getElementById('pay_amount').max = sisa;

        // Sembunyikan form jika sudah lunas (safety)
        document.getElementById('form-pembayaran').style.display = (sisa > 0) ? 'block' : 'none';

        // 2. SET DROPDOWN METODE
        let optMethod = '<option value="">-- Pilih Metode --</option>';
        res.methods.forEach(m => { optMethod += `<option value="${m.id}">${m.name}</option>`; });
        document.getElementById('pay_method').innerHTML = optMethod;

        // 3. RENDER RIWAYAT PEMBAYARAN
        const tbody = document.getElementById('table-riwayat');
        if (res.history.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="p-6 text-center italic text-slate-400">Belum ada data pembayaran untuk PO ini.</td></tr>';
        } else {
            let htmlHist = '';
            res.history.forEach((h, idx) => {
                htmlHist += `
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 text-center text-slate-400 font-bold text-xs">${idx + 1}</td>
                        <td class="p-3 font-bold text-slate-700 text-xs">${formatTglTime(h.payment_date)}</td>
                        <td class="p-3 font-black text-slate-700 text-xs uppercase">${h.method_name}</td>
                        <td class="p-3 text-[10px] text-slate-500 italic max-w-[150px] truncate">${h.notes || '-'}</td>
                        <td class="p-3 text-right font-black text-emerald-600">${formatRupiah(h.amount)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = htmlHist;
        }
    }
}

// ==========================================
// SUBMIT PEMBAYARAN BARU
// ==========================================
document.getElementById('form-pembayaran').addEventListener('submit', async function(e) {
    e.preventDefault();

    const po_id = document.getElementById('pay_po_id').value;
    const amount = parseFloat(document.getElementById('pay_amount').value);
    const max_amount = parseFloat(document.getElementById('pay_max_amount').value);
    const method_id = document.getElementById('pay_method').value;
    const pay_date = document.getElementById('pay_date').value;
    const notes = document.getElementById('pay_notes').value;

    if (amount <= 0) { Swal.fire('Ups!', 'Jumlah bayar harus lebih dari 0!', 'warning'); return; }
    if (amount > max_amount) { Swal.fire('Ups!', `Jumlah bayar melebih sisa tagihan! (Maks: ${formatRupiah(max_amount)})`, 'warning'); return; }

    const confirm = await Swal.fire({ title: 'Simpan Pembayaran?', text: 'Data pembayaran tidak dapat dihapus setelah disimpan.', icon: 'question', showCancelButton: true, confirmButtonColor: '#2563EB', confirmButtonText: 'Ya, Simpan' });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

        const formData = new FormData();
        formData.append('action', 'save_payment');
        formData.append('po_id', po_id);
        formData.append('method_id', method_id);
        formData.append('amount', amount);
        formData.append('pay_date', pay_date);
        formData.append('notes', notes);

        const res = await fetchAjax('logic.php', 'POST', formData);

        if (res.status === 'success') {
            closeModal('modal-bayar');
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
            loadBills(); // Refresh tabel utama
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
});