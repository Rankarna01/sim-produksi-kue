document.addEventListener("DOMContentLoaded", () => {
    initFilter();
    
    // Set default tanggal hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadRiwayat();
});

async function initFilter() {
    const res = await fetchAjax('logic.php?action=init_filter', 'GET');
    if (res.status === 'success') {
        let optWarehouse = '<option value="">Semua Store</option>';
        res.warehouses.forEach(w => optWarehouse += `<option value="${w.id}">${w.name}</option>`);
        document.getElementById('filter_store').innerHTML = optWarehouse;
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadRiwayat();
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadRiwayat();
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
    const month = monthNames[date.getMonth()];
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day} ${month} ${year}<br><span class="text-[10px] text-slate-400 font-bold">${hours}.${minutes} WIB</span>`;
}

async function loadRiwayat() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const warehouse_id = document.getElementById('filter_store').value;
    const status = document.getElementById('filter_status').value;

    const url = `logic.php?action=read&start_date=${start_date}&end_date=${end_date}&warehouse_id=${warehouse_id}&status=${status}`;
    
    try {
        const res = await fetchAjax(url, 'GET');
        
        if (res.status === 'success') {
            let html = '';
            if (res.data.length === 0) {
                html = '<tr><td colspan="8" class="p-10 text-center text-slate-400 italic">Tidak ada data riwayat titipan ditemukan.</td></tr>';
            } else {
                res.data.forEach((item, index) => {
                    // Status Badge Logic
                    let badgeStatus = '';
                    if(item.status === 'pending') {
                        badgeStatus = '<span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-clock mr-1"></i> Pending</span>';
                    } else if(item.status === 'received') {
                        badgeStatus = '<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check mr-1"></i> Masuk Gudang</span>';
                    } else {
                        badgeStatus = '<span class="bg-slate-100 text-slate-500 border border-slate-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-ban mr-1"></i> Dibatalkan</span>';
                    }

                    // Aksi Button Logic
                    let btnCancel = '';
                    if(item.status === 'pending') {
                        btnCancel = `<button onclick="batalkanData(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all shadow-sm" title="Batalkan & Kembalikan Stok">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                     </button>`;
                    }

                    const dapurInfo = item.dapur ? item.dapur : 'Pusat/Tanpa Dapur';

                    html += `
                        <tr class="hover:bg-slate-50 transition-colors ${item.status === 'cancelled' ? 'opacity-60' : ''}">
                            <td class="p-4 sm:p-5 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                            <td class="p-4 sm:p-5">${formatDate(item.created_at)}</td>
                            <td class="p-4 sm:p-5 font-black text-slate-800">${item.invoice_no}</td>
                            <td class="p-4 sm:p-5">
                                <span class="font-bold text-blue-700">${item.karyawan}</span><br>
                                <span class="text-[10px] font-bold text-slate-400"><i class="fa-solid fa-shop mr-1"></i> ${dapurInfo}</span>
                            </td>
                            <td class="p-4 sm:p-5 text-xs text-slate-600 leading-relaxed max-w-xs truncate" title="${item.product_list}">${item.product_list}</td>
                            <td class="p-4 sm:p-5 text-center font-black text-blue-600 text-lg">${item.total_pcs}</td>
                            <td class="p-4 sm:p-5 text-center">${badgeStatus}</td>
                            <td class="p-4 sm:p-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="cetakStruk(${item.id})" class="w-8 h-8 rounded-lg bg-slate-800 text-white hover:bg-slate-700 flex items-center justify-center transition-all shadow-sm" title="Cetak Struk">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </button>
                                    ${btnCancel}
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-10 text-center text-rose-500">Gagal memuat data!</td></tr>';
    }
}

function cetakStruk(id) {
    // Arahkan ke file print.php milik input_titipan
    window.open(`../input_titipan/print.php?id=${id}`, 'CetakStrukTitipan', 'width=400,height=600');
}

function batalkanData(id) {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        text: "Stok barang titipan akan dikembalikan ke sistem Owner.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Batalkan!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
            
            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('id', id);

            try {
                const res = await fetchAjax('logic.php', 'POST', formData);
                if (res.status === 'success') {
                    Swal.fire('Dibatalkan!', res.message, 'success');
                    loadRiwayat();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        }
    });
}