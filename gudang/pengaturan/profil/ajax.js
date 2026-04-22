document.addEventListener("DOMContentLoaded", () => {
    loadProfil();
});

function previewLogo(event) {
    const reader = new FileReader();
    const file = event.target.files[0];
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran gambar maksimal 2MB!', 'warning');
            event.target.value = '';
            return;
        }

        reader.onload = function(){
            const img = document.getElementById('logoPreview');
            const placeholder = document.getElementById('logoPlaceholder');
            
            img.src = reader.result;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
}

async function loadProfil() {
    Swal.fire({ title: 'Memuat Data...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading() } });
    
    const res = await fetchAjax('logic.php?action=read', 'GET');
    Swal.close();

    if (res.status === 'success' && res.data) {
        document.getElementById('store_name').value = res.data.store_name || '';
        document.getElementById('phone').value = res.data.phone || '';
        document.getElementById('email').value = res.data.email || '';
        document.getElementById('address').value = res.data.address || '';

        // Set Checkbox Saklar Persetujuan dari Database
        document.getElementById('req_approval_in').checked = (res.data.req_approval_in == 1);
        document.getElementById('req_approval_out').checked = (res.data.req_approval_out == 1);
        document.getElementById('req_approval_po').checked = (res.data.req_approval_po == 1);
        document.getElementById('req_approval_pr').checked = (res.data.req_approval_pr == 1);
        document.getElementById('req_approval_print').checked = (res.data.req_approval_print == 1);

        if (res.data.logo_path) {
            const img = document.getElementById('logoPreview');
            const placeholder = document.getElementById('logoPlaceholder');
            
            img.src = '../../../' + res.data.logo_path;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
    }
}

// Submit Data
document.getElementById('formProfil').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    Swal.fire({ title: 'Menyimpan Profil...', text: 'Mengunggah data ke server.', icon: 'info', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading() } });

    const formData = new FormData(this);
    
    // 🔥 PERBAIKAN UTAMA: Paksa kirim status Checkbox 1 atau 0 ke backend! 🔥
    formData.set('req_approval_in', document.getElementById('req_approval_in').checked ? 1 : 0);
    formData.set('req_approval_out', document.getElementById('req_approval_out').checked ? 1 : 0);
    formData.set('req_approval_po', document.getElementById('req_approval_po').checked ? 1 : 0);
    formData.set('req_approval_pr', document.getElementById('req_approval_pr').checked ? 1 : 0);
    formData.set('req_approval_print', document.getElementById('req_approval_print').checked ? 1 : 0);

    formData.append('action', 'save');

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        Swal.fire({ title: 'Tersimpan!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
        loadProfil(); // Panggil ulang untuk merefresh tampilan
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