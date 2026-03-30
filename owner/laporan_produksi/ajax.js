document.addEventListener("DOMContentLoaded", () => {
    applyQuickFilter(); // Langsung terapkan filter "Bulan Ini" saat pertama kali buka
});

// Submit Form Filter
document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('quick_filter').value = 'custom'; // Jika submit manual, ubah ke custom
    loadLaporan();
});

// Fungsi Pintar Mengatur Range Tanggal Otomatis
function applyQuickFilter() {
    const filterType = document.getElementById('quick_filter').value;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    const today = new Date();
    let start = '';
    let end = '';

    if (filterType === 'today') {
        start = end = today.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_week') {
        // Ambil hari Senin dan Minggu minggu ini
        const first = today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1);
        const last = first + 6;
        const startDay = new Date(today.setDate(first));
        const endDay = new Date(today.setDate(last));
        start = startDay.toISOString().split('T')[0];
        end = endDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        start = firstDay.toISOString().split('T')[0];
        end = lastDay.toISOString().split('T')[0];
    } 
    else if (filterType === 'this_year') {
        start = `${today.getFullYear()}-01-01`;
        end = `${today.getFullYear()}-12-31`;
    }

    if (filterType !== 'custom') {
        startDateInput.value = start;
        endDateInput.value = end;
        loadLaporan(); // Otomatis load data
    }
}

// Fungsi Memuat Tabel via AJAX
async function loadLaporan() {
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    
    // Update tulisan judul untuk Print PDF
    document.getElementById('print-periode').innerText = `Periode: ${start || 'Awal'} s/d ${end || 'Akhir'} | Status: ${status === '' ? 'Semua' : status.toUpperCase()}`;

    const url = `logic.php?action=read&start_date=${start}&end_date=${end}&status=${status}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="8" class="p-8 text-center text-secondary font-medium">Tidak ada data produksi pada periode ini.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let statusBadge = item.status === 'pending' 
                    ? `<span class="bg-accent/10 text-accent px-2 py-1 rounded text-[10px] font-bold uppercase print:text-black print:bg-transparent print:p-0">Pending</span>`
                    : `<span class="bg-success/10 text-success px-2 py-1 rounded text-[10px] font-bold uppercase print:text-black print:bg-transparent print:p-0">Selesai</span>`;

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-3 text-center text-slate-400 text-xs">${index + 1}</td>
                        <td class="p-3">
                            <div class="font-semibold">${tgl}</div>
                            <div class="text-[10px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-3 font-mono text-xs text-secondary">${item.invoice_no}</td>
                        <td class="p-3 font-medium text-sm">${item.karyawan}</td>
                        <td class="p-3 font-bold text-slate-800 text-sm">${item.produk}</td>
                        <td class="p-3 text-right font-black text-primary text-base print:text-black">${item.quantity}</td>
                        <td class="p-3 text-center">${statusBadge}</td>
                        <td class="p-3 text-xs font-semibold text-slate-600">${item.gudang}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// Fungsi Export Excel (Buka link download)
function exportExcel() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const status = document.getElementById('status').value;
    
    const url = `logic.php?action=export_excel&start_date=${start}&end_date=${end}&status=${status}`;
    window.location.href = url; // Memicu download di browser
}