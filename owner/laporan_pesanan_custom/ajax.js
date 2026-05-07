document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

async function loadData() {
    const tbody = document.getElementById('table-body');
    const search = document.getElementById('filter_search').value;
    const status = document.getElementById('filter_status').value;

    tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin text-primary text-2xl mb-2 block"></i>Memuat data...</td></tr>';

    try {
        const response = await fetch(`logic.php?action=read&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}&nocache=${Date.now()}`);
        const result = await response.json();

        if (result.status === 'success') {
            renderTable(result.data);
        } else {
            tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-rose-500 font-bold">Gagal memuat data: ${result.message}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-rose-500 font-bold">Kesalahan Jaringan.</td></tr>`;
    }
}

function renderTable(data) {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="p-10 text-center text-slate-400 font-bold"><i class="fa-solid fa-folder-open text-4xl mb-3 block opacity-50"></i>Tidak ada data pesanan custom ditemukan.</td></tr>';
        return;
    }

    data.forEach((row, index) => {
        // Format Tanggal
        const dateObj = new Date(row.created_at);
        const dateStr = dateObj.toLocaleDateString('id-ID') + ' ' + dateObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

        // Badge Status
        let badgeClass = '';
        let statusText = '';
        if (row.production_status === 'pending') { badgeClass = 'bg-rose-100 text-rose-600'; statusText = 'Menunggu'; }
        else if (row.production_status === 'diproses') { badgeClass = 'bg-orange-100 text-orange-600'; statusText = 'Dimasak'; }
        else { badgeClass = 'bg-emerald-100 text-emerald-600'; statusText = 'Selesai'; }

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors';
        tr.innerHTML = `
            <td class="p-4 text-center text-slate-500">${index + 1}</td>
            <td class="p-4 text-slate-600 whitespace-nowrap">${dateStr}</td>
            <td class="p-4 font-bold text-slate-700">${row.invoice_no}</td>
            <td class="p-4 font-black text-amber-600"><i class="fa-solid fa-cake-candles mr-1 opacity-50 text-xs"></i> ${row.custom_name}</td>
            <td class="p-4 text-slate-600 font-bold"><i class="fa-solid fa-user text-xs opacity-50 mr-1"></i> ${row.customer_name}</td>
            <td class="p-4 text-center font-black">${row.qty}</td>
            <td class="p-4 text-right font-medium">Rp ${formatRupiah(row.price)}</td>
            <td class="p-4 text-right font-black text-primary">Rp ${formatRupiah(row.subtotal)}</td>
            <td class="p-4 text-center">
                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase ${badgeClass}">${statusText}</span>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function resetFilter() {
    document.getElementById('filter_search').value = '';
    document.getElementById('filter_status').value = '';
    loadData();
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Fitur Export Excel Sederhana (CSV Format)
function exportExcel() {
    const table = document.getElementById("table-laporan-excel");
    let csv = [];
    const rows = table.querySelectorAll("tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) {
            // Bersihkan icon dan spasi berlebih
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ");
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }

    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "Laporan_Pesanan_Custom_" + Date.now() + ".csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}