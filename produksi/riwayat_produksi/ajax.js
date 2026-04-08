let currentPage = 1;

function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", () => {
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    loadHistory(1);
});

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadHistory(1); 
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    document.getElementById('status').value = '';
    
    loadHistory(1);
}

async function loadHistory(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-history');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary font-medium">Tidak ada data ditemukan pada filter ini.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 10 + index + 1;
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let statusBadge = '';
                let actionButtons = '';

                let btnPrint = `<button onclick="cetakUlangStruk(${item.prod_id})" title="Print Struk" class="bg-slate-800 hover:bg-slate-900 text-white w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-md"><i class="fa-solid fa-print text-xs"></i></button>`;
                
                // btnBatal sekarang hanya mengirim prod_id (1 invoice penuh)
                let btnBatal = `<button onclick="batalkanProduksi(${item.prod_id})" title="Batalkan 1 Invoice" class="bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-danger w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-sm"><i class="fa-solid fa-trash text-xs"></i></button>`;
                
                // btnEdit mengirim prod_id untuk me-load rincian di modal
                let btnEdit = `<button onclick="bukaEdit(${item.prod_id})" title="Revisi Data" class="bg-danger hover:bg-red-700 text-white w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-md"><i class="fa-solid fa-pen text-xs"></i></button>`;

                if (item.status === 'pending') {
                    statusBadge = `<span class="bg-accent/10 text-accent px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-clock"></i> Pending</span>`;
                    actionButtons = btnPrint + btnBatal; 
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="bg-danger/10 text-danger px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1 animate-pulse"><i class="fa-solid fa-triangle-exclamation"></i> Ditolak</span>`;
                    actionButtons = btnPrint + btnEdit + btnBatal;
                } else if (item.status === 'expired') {
                    statusBadge = `<span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-ban"></i> Expired</span>`;
                    actionButtons = btnPrint; 
                } else {
                    statusBadge = `<span class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-check-double"></i> Selesai</span>`;
                    actionButtons = btnPrint; 
                }

                // Ubah pemisah koma menjadi baris baru agar lebih rapi di tabel
                let formattedProductList = item.product_list.replace(/, /g, '<br>');

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${no}</td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary">${waktu} WIB</div>
                        </td>
                        <td class="p-4 font-mono text-sm text-slate-600 font-bold">${item.invoice_no}</td>
                        <td class="p-4 text-sm text-slate-700 leading-relaxed">${formattedProductList}</td>
                        <td class="p-4 text-center font-black text-primary text-xl">${item.total_qty}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                ${actionButtons}
                            </div>
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
    const container = document.getElementById('pagination');
    let html = '';
    
    if (totalPages === 0) totalPages = 1;

    if (current > 1) {
        html += `<button onclick="loadHistory(${current - 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    } else {
        html += `<button disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            if (i === 1 || i === totalPages || (i >= current - 1 && i <= current + 1)) {
                html += `<button onclick="loadHistory(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
            } else if (i === current - 2 || i === current + 2) {
                html += `<span class="px-2 text-slate-400">...</span>`;
            }
        }
    }

    if (current < totalPages) {
        html += `<button onclick="loadHistory(${current + 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    } else {
        html += `<button disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    }

    container.innerHTML = html;
}

// FITUR BARU: MENGAMBIL SEMUA PRODUK DALAM 1 INVOICE UNTUK DIEDIT
async function bukaEdit(prod_id) {
    document.getElementById('edit_prod_id').value = prod_id;
    const container = document.getElementById('edit-produk-list');
    
    // Tampilkan loading di dalam modal
    container.innerHTML = '<div class="text-center py-8 text-secondary"><i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2 block"></i> Memuat detail produk...</div>';
    openModal('modal-edit');
    
    // Tarik rincian produk
    const res = await fetchAjax(`logic.php?action=get_details&prod_id=${prod_id}`, 'GET');
    
    if (res.status === 'success') {
        let htmlList = '';
        res.data.forEach((item, index) => {
            htmlList += `
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4 shadow-sm">
                    <input type="hidden" name="detail_id[]" value="${item.detail_id}">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">Produk ${index + 1}</label>
                        <div class="font-bold text-slate-800 text-sm">${item.product_name}</div>
                    </div>
                    <div class="w-24 shrink-0">
                        <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider text-center">Qty (Pcs)</label>
                        <input type="number" name="quantity[]" value="${item.quantity}" required min="1" class="w-full px-2 py-2 border-2 border-slate-200 rounded-lg focus:border-primary outline-none font-black text-primary text-center text-lg bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>
            `;
        });
        container.innerHTML = htmlList;
    } else {
        container.innerHTML = `<div class="text-center py-4 text-danger font-bold">Gagal memuat data!</div>`;
    }
}

document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan Revisi...', text: 'Menyesuaikan stok bahan baku...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=update_revisi', 'POST', formData);
    
    if (response.status === 'success') {
        Swal.fire('Berhasil!', response.message, 'success');
        closeModal('modal-edit');
        loadHistory(currentPage);
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
});

// FITUR BARU: BATALKAN 1 INVOICE PENUH
async function batalkanProduksi(prod_id) {
    const result = await Swal.fire({
        title: 'Batalkan Invoice Ini?',
        text: "Semua daftar produk dalam Invoice ini akan dibatalkan, dan bahan baku dikembalikan 100% ke Gudang!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Batalkan Semua!',
        cancelButtonText: 'Tutup'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Memproses...', text: 'Mengembalikan seluruh bahan baku ke Gudang...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        
        const formData = new FormData();
        formData.append('prod_id', prod_id);
        
        const response = await fetchAjax('logic.php?action=cancel_produksi', 'POST', formData);
        
        if (response.status === 'success') {
            Swal.fire('Dibatalkan!', response.message, 'success');
            loadHistory(currentPage);
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}

function cetakUlangStruk(id) {
    window.open(`../input_produksi/print.php?id=${id}`, 'CetakStruk', 'width=400,height=600');
}