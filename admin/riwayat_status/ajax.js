let currentStatus = 'pending'; // Default tab yang aktif pertama kali
let currentPage = 1;

function getTodayLocal() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}

document.addEventListener("DOMContentLoaded", () => {
    // Set default tanggal hari ini
    const today = getTodayLocal();
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    loadData(1); 
});

// FUNGSI PINDAH TAB
function switchTab(status) {
    currentStatus = status;
    currentPage = 1; // Reset halaman ke 1 setiap pindah tab
    
    // Reset styling semua tombol tab
    const tabs = ['pending', 'masuk_gudang', 'ditolak', 'expired'];
    tabs.forEach(t => {
        const btn = document.getElementById(`tab-btn-${t}`);
        btn.classList.remove('border-accent', 'text-accent', 'border-success', 'text-success', 'border-danger', 'text-danger', 'border-slate-500', 'text-slate-600');
        btn.classList.add('border-transparent', 'text-secondary');
    });

    // Beri warna aktif sesuai tab yang dipilih
    const activeBtn = document.getElementById(`tab-btn-${status}`);
    activeBtn.classList.remove('border-transparent', 'text-secondary');
    
    if(status === 'pending') activeBtn.classList.add('border-accent', 'text-accent');
    else if(status === 'masuk_gudang') activeBtn.classList.add('border-success', 'text-success');
    else if(status === 'ditolak') activeBtn.classList.add('border-danger', 'text-danger');
    else if(status === 'expired') activeBtn.classList.add('border-slate-500', 'text-slate-600');

    loadData(1);
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
    loadData(1);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

async function loadData(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    const url = `logic.php?action=read&status=${currentStatus}&start_date=${start}&end_date=${end}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = `<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Tidak ada data untuk status ini.</td></tr>`;
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 15 + index + 1;
                const d = new Date(item.created_at);
                const tgl = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                html += `
                    <tr class="hover:bg-slate-50 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 font-bold text-sm">${no}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary">${jam} WIB</div>
                        </td>
                        <td class="p-4 font-mono font-bold text-primary">${item.invoice_no}</td>
                        <td class="p-4 font-medium">${item.karyawan}</td>
                        <td class="p-4 font-bold text-slate-800">${item.produk}</td>
                        <td class="p-4 text-center font-black text-lg text-slate-800">${formatNumber(item.quantity)}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    } else {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-danger font-medium">Terjadi kesalahan sistem.</td></tr>`;
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    if (totalPages === 0) totalPages = 1;

    html += `<button type="button" ${current > 1 ? `onclick="loadData(${current - 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current > 1 ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold shadow-sm"><i class="fa-solid fa-chevron-left mr-1"></i> Prev</button>`;

    let startPage = Math.max(1, current - 1);
    let endPage = Math.min(totalPages, current + 1);

    if (current === 1) endPage = Math.min(3, totalPages);
    if (current === totalPages) startPage = Math.max(1, totalPages - 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<button type="button" class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            html += `<button type="button" onclick="loadData(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold shadow-sm">${i}</button>`;
        }
    }

    html += `<button type="button" ${current < totalPages ? `onclick="loadData(${current + 1})"` : 'disabled'} class="px-4 py-2 rounded-lg ${current < totalPages ? 'bg-white hover:bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-300 cursor-not-allowed'} border border-slate-200 text-sm font-semibold shadow-sm">Next <i class="fa-solid fa-chevron-right ml-1"></i></button>`;
    container.innerHTML = html;
}

// =========================================================
// FITUR CETAK PDF (FULL DATA SESUAI TAB AKTIF)
// =========================================================
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Data...', text: 'Mengekstrak seluruh halaman...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    // Panggil is_print=true agar tidak dibatasi limit 15 baris
    const url = `logic.php?action=read&status=${currentStatus}&start_date=${start}&end_date=${end}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let labelStatus = currentStatus === 'masuk_gudang' ? 'Selesai (Masuk Gudang)' : currentStatus.toUpperCase();
        document.getElementById('print-subtitle').innerText = `Status Data: ${labelStatus}`;
        document.getElementById('print-periode').innerText = `Filter Tanggal: ${start || 'Awal'} s/d ${end || 'Akhir'}`;

        let htmlPrint = `<table>
                            <thead>
                                <tr>
                                    <th style="width:40px; text-align:center;">No</th>
                                    <th>Waktu</th>
                                    <th>No. Invoice</th>
                                    <th>Dapur</th>
                                    <th>Produk</th>
                                    <th style="text-align:center;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>`;
        
        if(response.data.length === 0){
             htmlPrint += `<tr><td colspan="6" style="text-align:center; padding:20px;">Tidak ada data.</td></tr>`;
        } else {
            response.data.forEach((item, i) => {
                htmlPrint += `
                    <tr>
                        <td style="text-align:center;">${i + 1}</td>
                        <td>${item.created_at}</td>
                        <td>${item.invoice_no}</td>
                        <td>${item.karyawan}</td>
                        <td>${item.produk}</td>
                        <td style="text-align:center; font-weight:bold;">${formatNumber(item.quantity)}</td>
                    </tr>
                `;
            });
        }
        htmlPrint += `</tbody></table>`;
        
        document.getElementById('print-table-wrapper').innerHTML = htmlPrint;
        Swal.close();

        // Jeda sedikit agar browser memproses HTML tabelnya, lalu Print
        setTimeout(() => { window.print(); }, 500);
    } else {
        Swal.fire('Error', 'Gagal menyiapkan data cetak', 'error');
    }
}

function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    window.location.href = `logic.php?action=export_excel&status=${currentStatus}&start_date=${start}&end_date=${end}`;
}