let currentPage = 1;
let alertShown = false; 

function getTodayLocal() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

document.addEventListener("DOMContentLoaded", async () => {
    await loadFilterGudang();
    
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1);
});

async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectStore = document.getElementById('warehouse_id');
            let optStore = '<option value="">Semua Store</option>';
            response.warehouses.forEach(w => {
                optStore += `<option value="${w.id}">Store: ${w.name}</option>`;
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
    loadData(1);
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    document.getElementById('warehouse_id').value = '';
    document.getElementById('kitchen_id').value = '';
    
    loadData(1);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data antrean...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        document.getElementById('badge-count').innerText = `${response.total_data} Item Tertunda`;

        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="8" class="p-12 text-center text-secondary"><div class="flex flex-col items-center justify-center"><i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i><span class="font-bold text-slate-500">Hebat! Tidak ada antrean.</span><span class="text-xs">Semua barang produksi pada filter ini sudah divalidasi.</span></div></td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 15 + index + 1;
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                html += `
                    <tr class="hover:bg-amber-50/30 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 text-xs">${no}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[10px] text-slate-400 font-bold text-amber-600">${waktu} WIB</div>
                        </td>
                        <td class="p-4 font-semibold text-slate-600 text-xs">
                            <i class="fa-solid fa-store text-slate-400 mr-1"></i>${item.gudang ?? '-'}
                        </td>
                        <td class="p-4 font-mono text-xs font-bold text-slate-500">${item.invoice_no}</td>
                        <td class="p-4 text-xs font-bold uppercase tracking-widest text-slate-500">${item.asal_dapur || '-'}</td>
                        <td class="p-4 font-medium text-sm">${item.karyawan}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">${item.produk}</td>
                        <td class="p-4 text-center font-black text-amber-600 text-base">${item.quantity}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);

        // ==============================================================
        // LOGIKA ALARM SORE (PENGINGAT VALIDASI BARANG HARI INI)
        // ==============================================================
        if (!alertShown && response.data.length > 0) {
            const now = new Date();
            const jamSekarang = now.getHours();
            
            if (jamSekarang >= 15) { // Jam 3 Sore ke atas
                const todayStr = getTodayLocal();
                const pendingTodayCount = response.data.filter(item => item.created_at.startsWith(todayStr)).length;
                
                if (pendingTodayCount > 0) {
                    Swal.fire({
                        title: '⚠️ Peringatan Sore!',
                        html: `<p style="color:#475569; font-weight:500;">Terdapat <b>${pendingTodayCount} item</b> produksi HARI INI yang nganggur di Dapur dan belum Anda validasi masuk ke Store.<br><br>Harap segera menuju Dapur dan lakukan Scan Barcode sebelum jam kerja berakhir!</p>`,
                        icon: 'warning',
                        confirmButtonText: 'Siap, Menuju Dapur!',
                        confirmButtonColor: '#F59E0B',
                        customClass: { popup: 'rounded-3xl shadow-2xl border border-amber-200' }
                    });
                    alertShown = true; 
                }
            }
        }
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    html += `<button type="button" ${current > 1 ? `onclick="loadData(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i></button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-amber-500 border border-amber-500 text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadData(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadData(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-right"></i></button>`;

    container.innerHTML = html;
}

// ===========================================================================
// CETAK PDF (ANTI LIMIT & BACA FILTER)
// ===========================================================================
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Dokumen...', text: 'Mengambil daftar jemputan...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    const kitchenId = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&kitchen_id=${kitchenId}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const wrapper = document.getElementById('print-table-wrapper');
        const now = new Date();
        
        const warehouseSelect = document.getElementById('warehouse_id');
        const warehouseName = warehouseId ? warehouseSelect.options[warehouseSelect.selectedIndex].text : 'Semua Store';

        document.getElementById('print-periode').innerText = `Lokasi Penjemputan: ${warehouseName.toUpperCase()} | Dicetak pada: ${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID')} WIB`;

        let htmlPrint = `<table><thead><tr><th>No</th><th>Waktu Produksi</th><th>Store Tujuan</th><th>No. Invoice</th><th>Asal Dapur</th><th>Karyawan</th><th>Nama Produk</th><th>Qty</th></tr></thead><tbody>`;
        
        if(response.data.length === 0){
             htmlPrint += `<tr><td colspan="8" style="text-align:center;">Tidak ada antrean barang pada filter tersebut.</td></tr>`;
        } else {
            response.data.forEach((item, index) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                htmlPrint += `<tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>${tgl}</td>
                    <td>${item.gudang ?? '-'}</td>
                    <td>${item.invoice_no}</td>
                    <td>${item.asal_dapur || '-'}</td>
                    <td>${item.karyawan}</td>
                    <td style="font-weight:bold;">${item.produk}</td>
                    <td style="text-align:center; font-weight:bold; font-size:14px;">${item.quantity}</td>
                </tr>`;
            });
        }
        
        htmlPrint += `</tbody></table>`;
        wrapper.innerHTML = htmlPrint;
        Swal.close();

        setTimeout(() => { window.print(); }, 500);
    } else {
        Swal.fire('Error', 'Gagal memuat data cetak', 'error');
    }
}