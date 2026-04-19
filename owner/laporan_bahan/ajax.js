let currentPage = 1;

document.addEventListener("DOMContentLoaded", async () => {
    await loadFilterDapur(); 
    loadLaporan(1); 
});

// FITUR BARU: Ambil Daftar Dapur dari Server
async function loadFilterDapur() {
    try {
        const response = await fetchAjax('logic.php?action=init_filter', 'GET');
        if (response.status === 'success') {
            const selectKitchen = document.getElementById('kitchen_id');
            let options = '<option value="">Semua Dapur</option>';
            response.kitchens.forEach(k => {
                options += `<option value="${k.id}">${k.name}</option>`;
            });
            if (selectKitchen) selectKitchen.innerHTML = options;
        }
    } catch (e) {
        console.error("Gagal memuat filter dapur");
    }
}

// Filter Submit
document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault(); 
    loadLaporan(1);
});

// Reset Filter
function resetFilter() {
    document.getElementById('formFilter').reset();
    document.getElementById('search').value = '';
    document.getElementById('status_stok').value = '';
    document.getElementById('kitchen_id').value = '';
    loadLaporan(1);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Render Tabel
async function loadLaporan(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const search = document.getElementById('search').value;
    const status_stok = document.getElementById('status_stok').value;
    const kitchen_id = document.getElementById('kitchen_id').value;
    
    const tglCetak = new Date().toLocaleString('id-ID');
    const kitchenSelect = document.getElementById('kitchen_id');
    const kitchenName = kitchen_id ? kitchenSelect.options[kitchenSelect.selectedIndex].text : 'Semua Dapur';
    
    document.getElementById('print-periode').innerText = `Dicetak pada: ${tglCetak} | Filter Kondisi: ${status_stok === '' ? 'Semua Kondisi' : status_stok.toUpperCase()} | Lokasi: ${kitchenName.toUpperCase()}`;

    const url = `logic.php?action=read&search=${search}&status_stok=${status_stok}&kitchen_id=${kitchen_id}&page=${currentPage}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Data bahan baku tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const no = (currentPage - 1) * 10 + index + 1; // Karena pagination 10 per halaman
                
                // Cek Status Kondisi Stok
                let statusBadge = '';
                const stockVal = parseFloat(item.stock);
                
                if (stockVal <= 0) {
                    statusBadge = `<span class="bg-danger/10 text-danger px-3 py-1 rounded-lg text-xs font-bold uppercase print:text-black print:bg-transparent print:p-0">Habis</span>`;
                } else if (stockVal <= 10) {
                    statusBadge = `<span class="bg-accent/10 text-accent px-3 py-1 rounded-lg text-xs font-bold uppercase print:text-black print:bg-transparent print:p-0">Menipis</span>`;
                } else {
                    statusBadge = `<span class="bg-success/10 text-success px-3 py-1 rounded-lg text-xs font-bold uppercase print:text-black print:bg-transparent print:p-0">Aman</span>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 text-sm">${no}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">${item.name}</td>
                        <td class="p-4 font-bold text-slate-500 text-xs uppercase tracking-widest"><i class="fa-solid fa-store mr-1 text-slate-400"></i> ${item.kitchen_name || 'Belum Diatur'}</td>
                        <td class="p-4 text-right font-black text-primary text-base print:text-black">${formatNumber(stockVal)}</td>
                        <td class="p-4 font-medium text-sm text-secondary">${item.unit}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
        renderPagination(response.total_pages, response.current_page);
    } else {
        tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-danger font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan saat memuat data.</td></tr>`;
    }
}

function renderPagination(totalPages, current) {
    const container = document.getElementById('pagination');
    let html = '';
    
    if (totalPages === 0) totalPages = 1;

    if (current > 1) {
        html += `<button onclick="loadLaporan(${current - 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    } else {
        html += `<button disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        if (i === current) {
            html += `<button class="px-4 py-2 rounded-lg bg-primary border border-primary text-white text-sm font-bold shadow-sm">${i}</button>`;
        } else {
            if (i === 1 || i === totalPages || (i >= current - 1 && i <= current + 1)) {
                html += `<button onclick="loadLaporan(${i})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">${i}</button>`;
            } else if (i === current - 2 || i === current + 2) {
                html += `<span class="px-2 text-slate-400">...</span>`;
            }
        }
    }

    if (current < totalPages) {
        html += `<button onclick="loadLaporan(${current + 1})" class="px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-sm font-semibold transition-colors shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    } else {
        html += `<button disabled class="px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-300 text-sm font-semibold cursor-not-allowed shadow-sm">Next <i class="fa-solid fa-chevron-right"></i></button>`;
    }

    container.innerHTML = html;
}

// CETAK PDF TANPA BATAS (Ambil semua data sesuai filter via AJAX)
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Dokumen...', text: 'Mengambil seluruh data...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const search = document.getElementById('search').value;
    const status_stok = document.getElementById('status_stok').value;
    const kitchen_id = document.getElementById('kitchen_id').value;
    
    // is_print=true akan mematikan limit pagination di backend
    const url = `logic.php?action=read&search=${search}&status_stok=${status_stok}&kitchen_id=${kitchen_id}&is_print=true`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const tbody = document.getElementById('table-laporan');
        let htmlPrint = '';

        if (response.data.length === 0) {
            htmlPrint = '<tr><td colspan="6" class="p-8 text-center text-secondary font-medium">Tidak ada data.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                let statusBadge = '';
                const stockVal = parseFloat(item.stock);
                if (stockVal <= 0) statusBadge = 'Habis';
                else if (stockVal <= 10) statusBadge = 'Menipis';
                else statusBadge = 'Aman';

                htmlPrint += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="font-bold">${item.name}</td>
                        <td>${item.kitchen_name || '-'}</td>
                        <td class="text-right font-bold">${formatNumber(stockVal)}</td>
                        <td>${item.unit}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            });
        }
        
        // Render semua data ke tabel, lalu eksekusi print
        tbody.innerHTML = htmlPrint;
        Swal.close();
        
        setTimeout(() => { 
            window.print(); 
            // Setelah print dialog ditutup, kembalikan tampilan tabel ke mode pagination normal (Halaman 1)
            loadLaporan(1); 
        }, 500);

    } else {
        Swal.fire('Error', 'Gagal memuat data cetak', 'error');
    }
}

// Export Excel
function exportExcel() {
    const search = document.getElementById('search').value;
    const status_stok = document.getElementById('status_stok').value;
    const kitchen_id = document.getElementById('kitchen_id').value;
    
    const url = `logic.php?action=export_excel&search=${search}&status_stok=${status_stok}&kitchen_id=${kitchen_id}`;
    window.location.href = url;
}