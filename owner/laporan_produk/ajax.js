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
    document.getElementById('category').value = '';
    loadLaporan();
}

// Render Tabel
async function loadLaporan() {
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const search = document.getElementById('search').value;
    const category = document.getElementById('category').value;
    
    // Update tulisan judul print
    const tglCetak = new Date().toLocaleString('id-ID');
    document.getElementById('print-periode').innerText = `Dicetak pada: ${tglCetak} | Kategori: ${category === '' ? 'Semua Kategori' : category}`;

    const url = `logic.php?action=read&search=${search}&category=${category}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="4" class="p-8 text-center text-secondary font-medium">Data produk tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 text-sm">${index + 1}</td>
                        <td class="p-4 font-mono text-sm text-secondary">${item.code}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">${item.name}</td>
                        <td class="p-4 font-medium text-sm text-slate-600">${item.category}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-danger font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan saat memuat data.</td></tr>`;
    }
}

// Export Excel
function exportExcel() {
    const search = document.getElementById('search').value;
    const category = document.getElementById('category').value;
    
    const url = `logic.php?action=export_excel&search=${search}&category=${category}`;
    window.location.href = url;
}