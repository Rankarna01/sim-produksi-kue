let currentPage = 1;

document.addEventListener("DOMContentLoaded", async () => {
    await loadFilterGudang();
    applyQuickFilter(); 
});

async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectStore = document.getElementById('warehouse_id');
            let optStore = '<option value="">Semua Store</option>';
            response.warehouses.forEach(w => {
                optStore += `<option value="${w.id}">${w.name}</option>`;
            });
            if(selectStore) selectStore.innerHTML = optStore;

            const selectKitchen = document.getElementById('kitchen_id');
            let optKitchen = '<option value="">Semua Dapur</option>';
            response.kitchens.forEach(k => {
                optKitchen += `<option value="${k.id}">${k.name}</option>`;
            });
            if(selectKitchen) selectKitchen.innerHTML = optKitchen;
        }
    } catch (e) {
        console.error("Gagal memuat filter dropdown");
    }
}

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('quick_filter').value = 'custom'; 
    loadLaporan(1);
});

function applyQuickFilter() {
    const filterType = document.getElementById('quick_filter').value;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const today = new Date();
    let start = '';
    let end = '';

    if (filterType === 'today') {
        start = end = today.toISOString().split('T')[0];
    } else if (filterType === 'this_week') {
        const first = today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1);
        const last = first + 6;
        const startDay = new Date(today.setDate(first));
        const endDay = new Date(today.setDate(last));
        start = startDay.toISOString().split('T')[0];
        end = endDay.toISOString().split('T')[0];
    } else if (filterType === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = firstDay.toISOString().split('T')[0];
        end = lastDay.toISOString().split('T')[0];
    } else if (filterType === 'this_year') {
        start = `${today.getFullYear()}-01-01`;
        end = `${today.getFullYear()}-12-31`;
    }

    if (filterType !== 'custom') {
        startDateInput.value = start;
        endDateInput.value = end;
        loadLaporan(1); 
    }
}

async function loadLaporan(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="10" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const reason = document.getElementById('reason').value;
    const warehouseId = document.getElementById('warehouse_id').value; 
    const kitchenId = document.getElementById('kitchen_id').value; 
    
    document.getElementById('print-periode').innerText = `Periode: ${start || 'Awal'} s/d ${end || 'Akhir'} | Alasan: ${reason === '' ? 'Semua' : reason.toUpperCase()}`;

    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&reason=${reason}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="10" class="p-8 text-center text-secondary font-medium">Tidak ada data produk keluar pada filter ini. Bagus!</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 15 + index + 1; // Limit di logic kita set 15
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                const namaStore = item.gudang ? item.gudang : '<span class="text-slate-400">Store Utama</span>';
                const namaDapur = item.asal_dapur ? item.asal_dapur : '<span class="text-slate-400">-</span>';

                let reasonBadge = '';
                if (item.reason === 'Expired') reasonBadge = `<span class="bg-danger/10 text-danger border border-danger/20 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">${item.reason}</span>`;
                else if (item.reason === 'Rusak') reasonBadge = `<span class="bg-orange-500/10 text-orange-600 border border-orange-500/20 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">${item.reason}</span>`;
                else reasonBadge = `<span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded text-[10px] font-bold uppercase print:border-none print:text-black print:p-0">${item.reason}</span>`;

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-3 text-center text-slate-400 text-xs">${no}</td>
                        <td class="p-3">
                            <div class="font-semibold">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-bold text-slate-700 text-xs">${namaDapur}</td>
                        <td class="p-3 font-bold text-slate-700 text-xs">${namaStore}</td>
                        <td class="p-3 font-mono text-xs font-bold text-primary">${item.origin_invoice}</td>
                        <td class="p-3 font-medium text-sm">${item.karyawan}</td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.produk}</td>
                        <td class="p-3 text-center font-black text-danger text-base print:text-black">-${item.quantity}</td>
                        <td class="p-3 text-center">${reasonBadge}</td>
                        <td class="p-3 text-xs text-slate-500 italic">${item.notes || '-'}</td>
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

    html += `<button type="button" ${current > 1 ? `onclick="loadLaporan(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadLaporan(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadLaporan(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

// CETAK PDF TANPA BATAS (Ambil semua data sesuai filter via AJAX)
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Dokumen...', text: 'Mengambil seluruh data...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const reason = document.getElementById('reason').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    // is_print=true akan mematikan limit pagination di backend
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&reason=${reason}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const tbody = document.getElementById('table-laporan');
        let htmlPrint = '';

        if (response.data.length === 0) {
            htmlPrint = '<tr><td colspan="10" class="p-8 text-center text-secondary font-medium">Tidak ada data.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                htmlPrint += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${tgl} ${waktu}</td>
                        <td>${item.asal_dapur || '-'}</td>
                        <td>${item.gudang || '-'}</td>
                        <td>${item.origin_invoice}</td>
                        <td>${item.karyawan}</td>
                        <td>${item.produk}</td>
                        <td class="text-center font-bold">-${item.quantity}</td>
                        <td class="text-center">${item.reason}</td>
                        <td>${item.notes || '-'}</td>
                    </tr>
                `;
            });
        }
        
        // Render semua data ke tabel, lalu eksekusi print
        tbody.innerHTML = htmlPrint;
        Swal.close();
        
        setTimeout(() => { 
            window.print(); 
            // Setelah print dialog ditutup, kembalikan tampilan tabel ke mode pagination normal
            loadLaporan(currentPage); 
        }, 500);

    } else {
        Swal.fire('Error', 'Gagal memuat data cetak', 'error');
    }
}

function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const reason = document.getElementById('reason').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=export_excel&start_date=${start}&end_date=${end}&reason=${reason}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}`;
    window.location.href = url; 
}