let html5QrcodeScanner = null;

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
        container.scrollIntoView({ behavior: 'smooth' });

    } else {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().then(() => {
                container.classList.add('hidden');
                btnKamera.classList.remove('hidden');
            }).catch(error => {
                console.error("Failed to clear html5QrcodeScanner. ", error);
            });
        }
    }
}

function onScanSuccess(decodedText, decodedResult) {
    toggleKamera(); 
    const inputField = document.getElementById('barcodeInput');
    inputField.value = decodedText; 
    
    const tombolCek = document.querySelector('#formScan button[type="submit"]');
    if (tombolCek) tombolCek.click();
}

function onScanFailure(error) {}

document.getElementById('barcodeInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault(); 
        const tombolCek = document.querySelector('#formScan button[type="submit"]');
        if (tombolCek) tombolCek.click();
    }
});

// 1. PROSES SCAN
document.getElementById('formScan').addEventListener('submit', async function(e) {
    e.preventDefault();
    const inputField = document.getElementById('barcodeInput');
    const barcodeVal = inputField.value;
    const resultBox = document.getElementById('scanResult');
    
    inputField.disabled = true; 
    if (typeof showLoading === "function") showLoading(); 
    
    const formData = new FormData();
    formData.append('barcode', barcodeVal);

    try {
        const response = await fetchAjax('logic.php?action=scan', 'POST', formData);
        
        if (typeof hideLoading === "function") hideLoading();
        resultBox.classList.remove('hidden');
        
        if (response.status === 'need_confirmation') {
            const h = response.header;
            const details = response.details;
            
            document.getElementById('konf-invoice').innerText = h.invoice_no;
            document.getElementById('konf-user').innerText = h.karyawan;
            document.getElementById('konf-gudang').innerText = h.gudang;
            document.getElementById('konf-prod-id').value = h.prod_id;
            
            let htmlList = '';
            details.forEach((item, idx) => {
                htmlList += `
                    <div class="bg-white p-3 rounded-lg border border-slate-100 shadow-sm flex justify-between items-center">
                        <div class="font-bold text-slate-700 text-sm flex gap-2">
                            <span class="text-slate-400">${idx + 1}.</span> 
                            <div>
                                ${item.produk}<br>
                                <span class="text-[10px] text-slate-400 font-bold"><i class="fa-solid fa-shop"></i> ${item.nama_umkm}</span>
                            </div>
                        </div>
                        <div class="font-black text-amber-500 text-lg shrink-0">${item.quantity} <span class="text-[10px] text-slate-400 font-bold uppercase">Pcs</span></div>
                    </div>
                `;
            });
            document.getElementById('konf-list-produk').innerHTML = htmlList;
            
            resultBox.classList.add('hidden'); 
            document.getElementById('modal-konfirmasi').classList.remove('hidden');
        } 
        else if (response.status === 'warning') {
            resultBox.innerHTML = `
                <div class="bg-amber-50 border-2 border-amber-500 p-4 sm:p-6 rounded-2xl flex items-center gap-4 text-left shadow-sm">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-500 text-white rounded-full flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="flex-1"><h3 class="text-base sm:text-lg font-bold text-amber-600 mb-1">PERHATIAN!</h3><p class="text-amber-800 text-xs sm:text-sm font-medium">${response.message}</p></div>
                </div>`;
        } else {
            resultBox.innerHTML = `
                <div class="bg-danger/10 border-2 border-danger p-4 sm:p-6 rounded-2xl flex items-center gap-4 text-left shadow-sm">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-danger text-white rounded-full flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-xmark"></i></div>
                    <div class="flex-1"><h3 class="text-base sm:text-lg font-bold text-danger mb-1">GAGAL!</h3><p class="text-slate-700 text-xs sm:text-sm font-medium">${response.message}</p></div>
                </div>`;
        }
    } catch (error) {
        if (typeof hideLoading === "function") hideLoading();
        console.error(error);
    }

    inputField.disabled = false; 
    inputField.value = ''; 
    if(window.innerWidth > 768) inputField.focus(); 
});

// 2. EKSEKUSI TOMBOL MODAL
async function prosesValidasi(statusBaru) {
    const prod_id = document.getElementById('konf-prod-id').value;
    const resultBox = document.getElementById('scanResult');
    
    document.getElementById('modal-konfirmasi').classList.add('hidden');
    if (typeof showLoading === "function") showLoading();

    const formData = new FormData();
    formData.append('prod_id', prod_id);
    formData.append('status', statusBaru);

    try {
        const response = await fetchAjax('logic.php?action=execute_validasi', 'POST', formData);
        
        if (typeof hideLoading === "function") hideLoading();
        resultBox.classList.remove('hidden');
        
        if (response.status === 'success') {
            if (response.status_type === 'received') {
                resultBox.innerHTML = `
                    <div class="bg-success/10 border-2 border-success p-4 rounded-2xl flex items-center gap-4 text-left shadow-sm">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-success text-white rounded-full flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-check-double"></i></div>
                        <div class="flex-1"><h3 class="text-base sm:text-lg font-bold text-success mb-1">Berhasil Diterima!</h3><p class="text-slate-700 text-xs sm:text-sm font-medium">Barang fisik sesuai dan sudah valid masuk ke gudang.</p></div>
                    </div>`;
            } else {
                resultBox.innerHTML = `
                    <div class="bg-danger/10 border-2 border-danger p-4 rounded-2xl flex items-center gap-4 text-left shadow-sm">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-danger text-white rounded-full flex items-center justify-center text-lg sm:text-xl shrink-0"><i class="fa-solid fa-rotate-left"></i></div>
                        <div class="flex-1"><h3 class="text-base sm:text-lg font-bold text-danger mb-1">Dikembalikan!</h3><p class="text-slate-700 text-xs sm:text-sm font-medium">Barang tidak sesuai. Status dikembalikan menjadi 'Ditolak'.</p></div>
                    </div>`;
            }
        }
    } catch (error) {
        if (typeof hideLoading === "function") hideLoading();
        alert("Terjadi kesalahan jaringan.");
    }
}