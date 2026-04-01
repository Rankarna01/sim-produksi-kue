let html5QrcodeScanner = null;

document.addEventListener("DOMContentLoaded", () => {
    loadHistory();
    loadEmployees(); // Tarik data dropdown
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
}

async function cariInvoice() {
    const inv = document.getElementById('search_invoice').value.trim();
    if (!inv) {
        alert("Silakan Scan Barcode atau ketik Nomor Invoice terlebih dahulu.");
        return;
    }

    showLoading();
    const response = await fetchAjax(`logic.php?action=search_invoice&inv=${inv}`, 'GET');
    hideLoading();

    if (response.status === 'success') {
        const d = response.data;
        
        document.getElementById('origin_invoice').value = d.invoice_no;
        document.getElementById('product_id').value = d.product_id;
        document.getElementById('info_product').innerText = d.product_name;
        document.getElementById('info_sisa').innerText = d.sisa + " Pcs";
        
        document.getElementById('quantity').max = d.sisa;
        document.getElementById('quantity').value = ''; 

        document.getElementById('form-details').classList.remove('hidden');
    } else {
        alert(response.message);
        document.getElementById('form-details').classList.add('hidden');
    }
}

async function loadHistory() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary">Belum ada riwayat pencatatan produk ditarik/keluar.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                const dateObj = new Date(item.created_at);
                const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                let reasonBadge = '';
                if (item.reason === 'Expired') reasonBadge = `<span class="bg-danger/10 text-danger border border-danger/20 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;
                else if (item.reason === 'Rusak') reasonBadge = `<span class="bg-orange-500/10 text-orange-600 border border-orange-500/20 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;
                else reasonBadge = `<span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded text-xs font-bold">${item.reason}</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-700">${tgl}</div>
                            <div class="text-[11px] text-slate-500">${waktu} WIB</div>
                        </td>
                        <td class="p-4 font-mono text-[11px] font-bold text-primary">${item.origin_invoice}</td>
                        <td class="p-4 font-bold text-slate-800">${item.product_name}</td>
                        <td class="p-4 text-center font-black text-danger text-lg">-${item.quantity}</td>
                        <td class="p-4 text-center">${reasonBadge}</td>
                        <td class="p-4 text-center text-xs font-semibold text-slate-600">${item.karyawan}</td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formKeluar').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (confirm("Simpan penarikan produk ini? Stok di sistem akan langsung dikurangi.")) {
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