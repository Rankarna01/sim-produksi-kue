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
    loadLaporan();
}

// Render Tabel
async function loadLaporan() {
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const search = document.getElementById('search').value;
    
    // Update tulisan judul print
    const tglCetak = new Date().toLocaleString('id-ID');
    document.getElementById('print-periode').innerText = `Dicetak pada: ${tglCetak} ${search ? '| Pencarian: ' + search : ''}`;

    const url = `logic.php?action=read&search=${search}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Data resep/BOM tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 text-sm">${index + 1}</td>
                        <td class="p-4 font-bold text-primary text-base">${item.product_name}</td>
                        <td class="p-4 font-semibold text-slate-700 text-sm"><i class="fa-solid fa-cube text-slate-300 mr-2 no-print"></i>${item.material_name}</td>
                        <td class="p-4 text-right font-black text-slate-800 text-base">${item.quantity_needed}</td>
                        <td class="p-4 font-medium text-sm text-secondary">${item.unit_used}</td>
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
    const url = `logic.php?action=export_excel&search=${search}`;
    window.location.href = url;
}