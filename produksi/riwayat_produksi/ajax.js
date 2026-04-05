let currentPage = 1;

// FUNGSI BARU: Mengambil Tanggal Hari Ini Sesuai Zona Waktu Lokal (Bukan UTC)
function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", () => {
    // Gunakan fungsi tanggal lokal
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
    
    // PERBAIKAN: Kembalikan tanggal ke hari ini saat di-reset, bukan dikosongkan
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
                let editButton = '';

                if (item.status === 'pending') {
                    statusBadge = `<span class="bg-accent/10 text-accent px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-clock"></i> Pending</span>`;
                    editButton = ``; 
                } else if (item.status === 'ditolak') {
                    statusBadge = `<span class="bg-danger/10 text-danger px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1 animate-pulse"><i class="fa-solid fa-triangle-exclamation"></i> Ditolak</span>`;
                    editButton = `<button onclick='bukaEdit(${JSON.stringify(item).replace(/'/g, "&apos;")})' title="Perbaiki Data" class="bg-danger hover:bg-red-700 text-white w-9 h-9 rounded-lg flex items-center justify-center transition-colors shadow-md"><i class="fa-solid fa-pen"></i></button>`;
                } else if (item.status === 'expired') {
                    statusBadge = `<span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-ban"></i> Expired</span>`;
                    editButton = ``;
                } else {
                    statusBadge = `<span class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1"><i class="fa-solid fa-check-double"></i> Selesai</span>`;
                    editButton = ``;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${no}</td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary">${waktu} WIB</div>
                        </td>
                        <td class="p-4 font-mono text-sm text-slate-600">${item.invoice_no}</td>
                        <td class="p-4 font-bold text-slate-800">${item.product_name}</td>
                        <td class="p-4 text-center font-bold text-primary text-lg">${item.quantity}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="cetakUlangStruk(${item.prod_id})" title="Print Struk" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-print"></i> Print
                                </button>
                                ${editButton}
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

function bukaEdit(item) {
    document.getElementById('edit_prod_id').value = item.prod_id;
    document.getElementById('edit_detail_id').value = item.detail_id;
    document.getElementById('edit_produk').value = item.product_name;
    document.getElementById('edit_qty').value = item.quantity;
    
    openModal('modal-edit');
}

document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    showLoading();
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=update_revisi', 'POST', formData);
    
    hideLoading();
    if (response.status === 'success') {
        alert(response.message);
        closeModal('modal-edit');
        loadHistory(currentPage);
    } else {
        alert('Gagal: ' + response.message);
    }
});

function cetakUlangStruk(id) {
    window.open(`../input_produksi/print.php?id=${id}`, 'CetakStruk', 'width=400,height=600');
}