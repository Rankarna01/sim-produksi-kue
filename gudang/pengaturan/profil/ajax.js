document.addEventListener("DOMContentLoaded", () => {
    loadProfil();
    bindLivePreview();
});

function bindLivePreview() {
    const storeNameInput = document.getElementById('store_name');
    const addressInput = document.getElementById('address');
    const phoneInput = document.getElementById('phone');
    const emailInput = document.getElementById('email');

    if (storeNameInput) {
        storeNameInput.addEventListener('input', function() {
            const previewName = document.getElementById('kopPreviewName');
            if (previewName) previewName.textContent = this.value.trim() || 'NAMA PERUSAHAAN';
        });
    }

    if (addressInput) {
        addressInput.addEventListener('input', function() {
            const previewAddress = document.getElementById('kopPreviewAddress');
            if (previewAddress) previewAddress.textContent = this.value.trim() || 'Alamat toko akan tampil di sini';
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const previewPhone = document.getElementById('kopPreviewPhone');
            if (previewPhone) previewPhone.innerHTML = `<i class="fa-solid fa-phone text-[10px] mr-1 text-slate-400"></i> ${this.value.trim() || '-'}`;
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const previewEmail = document.getElementById('kopPreviewEmail');
            if (previewEmail) previewEmail.innerHTML = `<i class="fa-solid fa-envelope text-[10px] mr-1 text-slate-400"></i> ${this.value.trim() || '-'}`;
        });
    }
}

function previewLogo(event) {
    const reader = new FileReader();
    const file = event.target.files[0];
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Ukuran Terlalu Besar', 'Ukuran gambar maksimal 2MB!', 'warning');
            event.target.value = '';
            if (fileNameDisplay) fileNameDisplay.textContent = 'Belum ada file baru dipilih';
            return;
        }

        if (fileNameDisplay) {
            fileNameDisplay.textContent = file.name;
        }

        reader.onload = function(){
            const img = document.getElementById('logoPreview');
            const placeholder = document.getElementById('logoPlaceholder');
            
            if (img && placeholder) {
                img.src = reader.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }

            // Sync with Kop Preview
            const kopImg = document.getElementById('kopPreviewImg');
            const kopIcon = document.getElementById('kopPreviewIcon');
            if (kopImg && kopIcon) {
                kopImg.src = reader.result;
                kopImg.classList.remove('hidden');
                kopIcon.classList.add('hidden');
            }
        }
        reader.readAsDataURL(file);
    }
}

async function loadProfil() {
    Swal.fire({ title: 'Memuat Data...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading() } });
    
    const res = await fetchAjax('logic.php?action=read', 'GET');
    Swal.close();

    if (res.status === 'success' && res.data) {
        const storeName = res.data.store_name || '';
        const phone = res.data.phone || '';
        const email = res.data.email || '';
        const address = res.data.address || '';

        document.getElementById('store_name').value = storeName;
        document.getElementById('phone').value = phone;
        document.getElementById('email').value = email;
        document.getElementById('address').value = address;

        // Set Checkbox Saklar Persetujuan dari Database
        document.getElementById('req_approval_in').checked = (res.data.req_approval_in == 1);
        document.getElementById('req_approval_out').checked = (res.data.req_approval_out == 1);
        document.getElementById('req_approval_po').checked = (res.data.req_approval_po == 1);
        document.getElementById('req_approval_pr').checked = (res.data.req_approval_pr == 1);
        document.getElementById('req_approval_print').checked = (res.data.req_approval_print == 1);

        // Update Kop Surat Live Preview
        const previewName = document.getElementById('kopPreviewName');
        const previewAddress = document.getElementById('kopPreviewAddress');
        const previewPhone = document.getElementById('kopPreviewPhone');
        const previewEmail = document.getElementById('kopPreviewEmail');

        if (previewName) previewName.textContent = storeName || 'NAMA PERUSAHAAN';
        if (previewAddress) previewAddress.textContent = address || 'Alamat toko akan tampil di sini';
        if (previewPhone) previewPhone.innerHTML = `<i class="fa-solid fa-phone text-[10px] mr-1 text-slate-400"></i> ${phone || '-'}`;
        if (previewEmail) previewEmail.innerHTML = `<i class="fa-solid fa-envelope text-[10px] mr-1 text-slate-400"></i> ${email || '-'}`;

        const fileNameDisplay = document.getElementById('fileNameDisplay');
        if (fileNameDisplay) fileNameDisplay.textContent = 'Belum ada file baru dipilih';

        const img = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        const kopImg = document.getElementById('kopPreviewImg');
        const kopIcon = document.getElementById('kopPreviewIcon');

        if (res.data.logo_path) {
            const fullLogoUrl = '../../../' + res.data.logo_path;
            
            if (img && placeholder) {
                img.src = fullLogoUrl;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }

            if (kopImg && kopIcon) {
                kopImg.src = fullLogoUrl;
                kopImg.classList.remove('hidden');
                kopIcon.classList.add('hidden');
            }
        } else {
            if (img && placeholder) {
                img.src = '';
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
            if (kopImg && kopIcon) {
                kopImg.src = '';
                kopImg.classList.add('hidden');
                kopIcon.classList.remove('hidden');
            }
        }
    }
}

// Submit Data
document.getElementById('formProfil').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    Swal.fire({ 
        title: 'Menyimpan Profil...', 
        text: 'Mengunggah konfigurasi ke server.', 
        icon: 'info', 
        allowOutsideClick: false, 
        showConfirmButton: false, 
        didOpen: () => { Swal.showLoading() } 
    });

    const formData = new FormData(this);
    
    // Pastikan status saklar checkbox 1 atau 0
    formData.set('req_approval_in', document.getElementById('req_approval_in').checked ? 1 : 0);
    formData.set('req_approval_out', document.getElementById('req_approval_out').checked ? 1 : 0);
    formData.set('req_approval_po', document.getElementById('req_approval_po').checked ? 1 : 0);
    formData.set('req_approval_pr', document.getElementById('req_approval_pr').checked ? 1 : 0);
    formData.set('req_approval_print', document.getElementById('req_approval_print').checked ? 1 : 0);

    formData.append('action', 'save');

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        Swal.fire({ 
            title: 'Berhasil Disimpan!', 
            text: res.message, 
            icon: 'success', 
            timer: 1800, 
            showConfirmButton: false 
        });
        loadProfil();
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

async function fetchAjax(url, method, data = null) {
    const options = { method: method };
    if (data) options.body = data;
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        return { status: 'error', message: error.message };
    }
}