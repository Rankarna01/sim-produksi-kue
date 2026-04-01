document.addEventListener("DOMContentLoaded", () => {
    // Set default tanggal hari ini di kotak input saat pertama kali dibuka
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
    
    // Langsung muat data
    loadHistory();
});

// Event ketika tombol Filter diklik
document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    loadHistory();
});

// Fungsi untuk tombol Reset
function resetFilter() {
    document.getElementById('formFilter').reset();
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    loadHistory();
}

async function loadHistory() {
    const tbody = document.getElementById('table-history');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    // Ambil nilai dari input filter
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    
    // Panggil AJAX dengan parameter filter
    const url = `logic.php?action=read&start_date=${start}&end_date=${end}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary font-medium">Tidak ada riwayat validasi pada rentang tanggal tersebut.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                // Konversi tanggal database ke format lokal yang rapi
                const dateObj = new Date(item.updated_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                html += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-xs text-secondary font-mono">${waktu} WIB</div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">${item.produk}</div>
                            <div class="text-xs text-slate-500 font-mono tracking-wider">${item.invoice_no}</div>
                        </td>
                        <td class="p-4 text-center">
                            <span class="bg-success/10 text-success border border-success/20 px-3 py-1 rounded-full font-bold shadow-sm inline-block transform group-hover:scale-110 transition-transform">
                                + ${item.quantity}
                            </span>
                        </td>
                        <td class="p-4 font-semibold text-slate-600">
                            <i class="fa-solid fa-warehouse text-slate-400 mr-2"></i>${item.gudang}
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        // Tampilkan error jika terjadi kegagalan di database
        tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-danger font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> ${response.message}</td></tr>`;
    }
}