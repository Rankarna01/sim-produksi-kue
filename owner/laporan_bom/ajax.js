document.addEventListener("DOMContentLoaded", () => {
    loadLaporan(); 
});

document.getElementById('formFilter').addEventListener('submit', function(e) {
    e.preventDefault(); 
    loadLaporan();
});

function resetFilter() {
    document.getElementById('formFilter').reset();
    document.getElementById('search').value = '';
    loadLaporan();
}

async function loadLaporan() {
    const tbody = document.getElementById('table-laporan');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data laporan...</td></tr>';
    
    const search = document.getElementById('search').value;
    const url = `logic.php?action=read&search=${search}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response && response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="4" class="p-8 text-center text-secondary font-medium">Data resep/BOM tidak ditemukan.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const totalBahan = item.materials.length;
                // Encode Data Bahan menjadi string aman agar bisa dimasukkan ke dalam elemen HTML
                const bahanEncoded = encodeURIComponent(JSON.stringify(item.materials));

                html += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 transition-colors text-slate-700">
                        <td class="p-4 text-center text-slate-400 font-bold text-sm">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800 text-base">${item.product_name}</td>
                        <td class="p-4 text-center">
                            <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-100">
                                ${totalBahan} Bahan Baku
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <button type="button" 
                                data-produk="${item.product_name}" 
                                data-bahan="${bahanEncoded}" 
                                onclick="bukaDetail(this)" 
                                class="bg-primary/10 hover:bg-primary text-primary hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-2 mx-auto">
                                <i class="fa-solid fa-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    } else {
        tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-danger font-medium"><i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan saat memuat data.</td></tr>`;
    }
}

// Fungsi Membuka Modal Detail Resep
function bukaDetail(btnElement) {
    const namaProduk = btnElement.getAttribute('data-produk');
    // Decode data string menjadi Object Javascript
    const materials = JSON.parse(decodeURIComponent(btnElement.getAttribute('data-bahan')));

    document.getElementById('modal-title-produk').innerText = namaProduk;

    let htmlBahan = '';
    materials.forEach((m, i) => {
        htmlBahan += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-3 text-center text-slate-400 text-xs">${i + 1}</td>
                <td class="p-3 font-semibold text-slate-700">${m.material_name}</td>
                <td class="p-3 text-right font-black text-primary">${m.quantity_needed} <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold ml-1">${m.unit_used}</span></td>
            </tr>
        `;
    });

    document.getElementById('modal-list-bahan').innerHTML = htmlBahan;
    document.getElementById('modal-detail').classList.remove('hidden');
}

// Fungsi Cetak PDF Dinamis
async function cetakPDF() {
    Swal.fire({ title: 'Menyiapkan Dokumen...', text: 'Menyusun laporan BOM...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const search = document.getElementById('search').value;
    const url = `logic.php?action=read&search=${search}`;
    const response = await fetchAjax(url, 'GET');
    
    if (response.status === 'success') {
        const wrapper = document.getElementById('print-content-wrapper');
        const now = new Date();
        document.getElementById('print-periode').innerText = `Dicetak pada: ${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID')} WIB`;

        let htmlPrint = '';

        if(response.data.length === 0){
             htmlPrint = `<p style="text-align:center; padding: 20px;">Tidak ada data resep.</p>`;
        } else {
            response.data.forEach((item, index) => {
                // Header Group (Nama Produk)
                htmlPrint += `<div class="print-product-title">${index + 1}. RESEP: ${item.product_name}</div>`;
                
                // Table Detail (Daftar Bahan)
                htmlPrint += `<table>
                                <thead>
                                    <tr>
                                        <th style="width:50px; text-align:center;">No</th>
                                        <th>Nama Bahan Baku</th>
                                        <th style="text-align:right;">Takaran (Per 1 Pcs)</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                item.materials.forEach((m, i) => {
                    htmlPrint += `
                        <tr>
                            <td style="text-align:center;">${i + 1}</td>
                            <td>${m.material_name}</td>
                            <td style="text-align:right; font-weight:bold;">${m.quantity_needed} ${m.unit_used}</td>
                        </tr>
                    `;
                });
                
                htmlPrint += `</tbody></table>`;
            });
        }
        
        wrapper.innerHTML = htmlPrint;
        Swal.close();

        setTimeout(() => { window.print(); }, 500);

    } else {
        Swal.fire('Error', 'Gagal memuat data cetak', 'error');
    }
}

function exportExcel() {
    const search = document.getElementById('search').value;
    const url = `logic.php?action=export_excel&search=${search}`;
    window.location.href = url;
}