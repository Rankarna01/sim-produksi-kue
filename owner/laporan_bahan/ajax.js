document.addEventListener("DOMContentLoaded", () => {
    loadLaporan(); 
});

// Filter Submit
document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault(); 
    loadLaporan();
});

// Reset Filter
function resetFilter() {
    document.getElementById('formFilter').reset();
    document.getElementById('search').value = '';
    document.getElementById('status_stok').value = '';
    loadLaporan();
}

// Render Tabel
async function loadLaporan() {
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const search = document.getElementById('search').value;
    const status_stok = document.getElementById('status_stok').value;
    
    // Update tulisan judul print
    const tglCetak = new Date().toLocaleString('id-ID');
    document.getElementById('print-periode').innerText = `Dicetak pada: ${tglCetak} | Filter: ${status_stok === '' ? 'Semua Kondisi' : status_stok.toUpperCase()}`;

    const url = `logic.php?action=read&search=${search}&status_stok=${status_stok}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Data bahan baku tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                
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
                        <td class="p-4 text-center text-slate-400 text-sm">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">${item.name}</td>
                        <td class="p-4 text-right font-black text-primary text-base print:text-black">${item.stock}</td>
                        <td class="p-4 font-medium text-sm text-secondary">${item.unit}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-danger font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan saat memuat data.</td></tr>`;
    }
}

// Export Excel
function exportExcel() {
    const search = document.getElementById('search').value;
    const status_stok = document.getElementById('status_stok').value;
    
    const url = `logic.php?action=export_excel&search=${search}&status_stok=${status_stok}`;
    window.location.href = url;
}