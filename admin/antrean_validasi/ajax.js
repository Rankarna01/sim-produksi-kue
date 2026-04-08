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
    // 1. Muat Dropdown Gudang
    await loadFilterGudang();
    
    // 2. Set default tanggal hari ini
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    // 3. Load data
    loadData(1);
});

// FITUR BARU: Ambil Daftar Gudang dari Server
async function loadFilterGudang() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectGudang = document.getElementById('warehouse_id');
            let options = '<option value="">Semua Gudang</option>';
            response.warehouses.forEach(w => {
                options += `<option value="${w.id}">${w.name}</option>`;
            });
            if(selectGudang) selectGudang.innerHTML = options;
        }
    } catch (e) {
        console.error("Gagal memuat filter gudang");
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
    loadData(1);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data antrean...</td></tr>';
    
    // Ambil Filter
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const warehouseId = document.getElementById('warehouse_id').value;
    
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        document.getElementById('badge-count').innerText = `${response.total_data} Item Tertunda`;

        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-12 text-center text-secondary"><div class="flex flex-col items-center justify-center"><i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i><span class="font-bold text-slate-500">Hebat! Tidak ada antrean.</span><span class="text-xs">Semua barang produksi pada filter ini sudah divalidasi.</span></div></td></tr>';
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
                            <i class="fa-solid fa-warehouse text-slate-400 mr-1"></i>${item.gudang ?? 'Gudang Utama'}
                        </td>
                        <td class="p-4 font-mono text-xs font-bold text-slate-500">${item.invoice_no}</td>
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
            
            // Jika sudah jam 15:00 (Jam 3 Sore) ke atas
            if (jamSekarang >= 15) {
                const todayStr = getTodayLocal();
                // Cek apakah ada barang pending yang diproduksi HARI INI
                const pendingTodayCount = response.data.filter(item => item.created_at.startsWith(todayStr)).length;
                
                if (pendingTodayCount > 0) {
                    Swal.fire({
                        title: '⚠️ Peringatan Sore!',
                        html: `<p style="color:#475569; font-weight:500;">Terdapat <b>${pendingTodayCount} item</b> produksi HARI INI yang nganggur di Dapur dan belum Anda validasi.<br><br>Harap segera ke Dapur dan lakukan Scan Barcode sebelum jam kerja berakhir!</p>`,
                        icon: 'warning',
                        confirmButtonText: 'Siap, Menuju Dapur!',
                        confirmButtonColor: '#F59E0B',
                        customClass: { popup: 'rounded-3xl shadow-2xl border border-amber-200' }
                    });
                    alertShown = true; // Kunci agar tidak muncul terus saat pindah halaman
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
    
    // Tarik data tanpa limit dengan filter yang aktif
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&warehouse_id=${warehouseId}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const wrapper = document.getElementById('print-table-wrapper');
        const now = new Date();
        
        // Ambil nama gudang untuk judul PDF
        const warehouseSelect = document.getElementById('warehouse_id');
        const warehouseName = warehouseId ? warehouseSelect.options[warehouseSelect.selectedIndex].text : 'Semua Gudang';

        document.getElementById('print-periode').innerText = `Lokasi Penjemputan: ${warehouseName.toUpperCase()} | Dicetak pada: ${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID')} WIB`;

        let htmlPrint = `<table><thead><tr><th>No</th><th>Waktu Produksi</th><th>Gudang Tujuan</th><th>No. Invoice</th><th>Karyawan (Dapur)</th><th>Nama Produk</th><th>Qty</th></tr></thead><tbody>`;
        
        if(response.data.length === 0){
             htmlPrint += `<tr><td colspan="7" style="text-align:center;">Tidak ada antrean barang pada filter tersebut.</td></tr>`;
        } else {
            response.data.forEach((item, index) => {
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                htmlPrint += `<tr>
                    <td style="text-align:center;">${index + 1}</td>
                    <td>${tgl}</td>
                    <td>${item.gudang ?? 'Gudang Utama'}</td>
                    <td>${item.invoice_no}</td>
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