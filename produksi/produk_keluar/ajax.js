let html5QrcodeScanner = null;

document.addEventListener("DOMContentLoaded", () => {
    loadHistory();
    loadEmployees(); 
});

async function loadEmployees() {
    const response = await fetchAjax('logic.php?action=get_employees', 'GET');
    if (response.status === 'success') {
        let opt = '<option value="">-- Pilih Nama Anda --</option>';
        response.data.forEach(e => {
            opt += `<option value="${e.id}">${e.name}</option>`;
        });
        document.getElementById('employee_id').innerHTML = opt;
    }
}

function toggleKamera() {
    const container = document.getElementById('kameraContainer');
    const btnKamera = document.getElementById('btnKamera');

    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        btnKamera.classList.add('hidden');
        
        html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
            fps: 10, 
            qrbox: {width: 250, height: 100},
            aspectRatio: 1.0,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
        }, false);
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    } else {
        matikanKamera();
    }
}

function matikanKamera() {
    const container = document.getElementById('kameraContainer');
    const btnKamera = document.getElementById('btnKamera');
    
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().then(() => {
            container.classList.add('hidden');
            btnKamera.classList.remove('hidden');
        }).catch(error => {
            console.error("Gagal menutup kamera", error);
        });
    }
}

function onScanSuccess(decodedText) {
    matikanKamera();
    document.getElementById('search_invoice').value = decodedText;
    cariInvoice(); 
}

function onScanFailure() { /* Abaikan */ }

function closeModalKeluar() {
    closeModal('modal-keluar');
    resetForm();
    matikanKamera();
}

function resetForm() {
    document.getElementById('search_invoice').value = '';
    document.getElementById('formKeluar').reset();
    document.getElementById('form-details').classList.add('hidden');
    document.getElementById('product-list-container').innerHTML = '';
    document.getElementById('info_tgl_produksi').innerText = '-';
}

async function cariInvoice() {
    const inv = document.getElementById('search_invoice').value.trim();
    if (!inv) {
        alert("Silakan ketik Nomor Invoice atau Scan Barcode terlebih dahulu.");
        return;
    }

    showLoading();
    const response = await fetchAjax(`logic.php?action=search_invoice&inv=${inv}`, 'GET');
    hideLoading();

    if (response.status === 'success') {
        const invoice_no = response.invoice_no;
        const details = response.details;
        const tgl_prod = new Date(response.tgl_produksi);
        
        // Format Tgl Produksi
        const fmtTglProd = tgl_prod.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + tgl_prod.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        
        document.getElementById('origin_invoice').value = invoice_no;
        document.getElementById('info_invoice').innerText = invoice_no;
        document.getElementById('info_tgl_produksi').innerText = fmtTglProd;
        
        // RENDER BARIS PRODUK
        let htmlList = '';
        details.forEach((d) => {
            htmlList += `
                <div class="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex-1">
                        <p class="font-bold text-slate-800 text-sm">${d.product_name}</p>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Sisa di Etalase: <span class="text-danger">${d.sisa} Pcs</span></p>
                        <input type="hidden" name="product_id[]" value="${d.product_id}">
                    </div>
                    <div class="w-full sm:w-28 shrink-0 relative">
                        <input type="number" name="quantity[]" min="0" max="${d.sisa}" class="w-full pl-3 pr-8 py-2 border-2 border-slate-200 rounded-lg focus:border-danger outline-none font-black text-danger text-center transition-colors hover:bg-slate-50" placeholder="0">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-[10px] font-bold text-slate-400">Pcs</span>
                        </div>
                    </div>
                </div>
            `;
        });
        document.getElementById('product-list-container').innerHTML = htmlList;

        document.getElementById('form-details').classList.remove('hidden');
    } else {
        alert(response.message);
        document.getElementById('form-details').classList.add('hidden');
    }
}

async function loadHistory() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="9" class="p-8 text-center text-secondary">Belum ada riwayat pencatatan produk ditarik/keluar.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let reasonBadge = '';
                if (item.reason === 'Expired') reasonBadge = `<span class="bg-danger/10 text-danger border border-danger/20 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;
                else if (item.reason === 'Rusak') reasonBadge = `<span class="bg-orange-500/10 text-orange-600 border border-orange-500/20 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;
                else reasonBadge = `<span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;

                const printBtn = `<button onclick="cetakBuktiTarik('${item.out_id}')" title="Cetak Bukti Penarikan" class="bg-slate-800 hover:bg-slate-900 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-colors shadow-sm"><i class="fa-solid fa-print text-xs"></i></button>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[11px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-4 font-mono text-[10px] font-bold text-slate-400">${item.out_id}</td>
                        <td class="p-4 font-mono text-xs font-bold text-primary">${item.origin_invoice}</td>
                        <td class="p-4 font-bold text-slate-800">${item.product_name}</td>
                        <td class="p-4 text-center font-black text-danger text-lg">-${item.quantity}</td>
                        <td class="p-4 text-center">${reasonBadge}</td>
                        <td class="p-4 text-center text-xs font-semibold text-slate-600">${item.karyawan}</td>
                        <td class="p-4 text-center flex justify-center">${printBtn}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formKeluar').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (confirm("Simpan penarikan produk ini? Stok di sistem akan langsung dikurangi sesuai jumlah yang Anda isi.")) {
        showLoading();
        const formData = new FormData(this);
        const response = await fetchAjax('logic.php?action=save', 'POST', formData);
        hideLoading();

        if (response.status === 'success') {
            closeModalKeluar();
            loadHistory();
            alert("Berhasil! Penarikan tercatat.");
        } else {
            alert('Gagal: ' + response.message);
        }
    }
});

function cetakBuktiTarik(out_id) {
    window.open(`print.php?id=${out_id}`, 'CetakBukti', 'width=400,height=600');
}