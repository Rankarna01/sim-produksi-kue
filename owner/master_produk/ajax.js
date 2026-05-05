document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

// Fitur Preview Gambar saat diupload
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('image_preview');
        output.src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

function resetForm() {
    document.getElementById('formProduk').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('old_image').value = '';
    // FIXED PATH GAMBAR
    document.getElementById('image_preview').src = '../../assets/img/no-image.png'; 
    document.getElementById('modal-title').innerText = 'Tambah Produk Baru';
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="9" class="p-8 text-center"><i class="fa-spin fa-solid fa-circle-notch"></i> Memuat...</td></tr>';

    const response = await fetchAjax('logic.php?action=read', 'GET');
    if (response.status === 'success') {
        let html = '';
        response.data.forEach((item, index) => {
            let rpJual = new Intl.NumberFormat('id-ID').format(item.price || 0);
            let rpOnline = new Intl.NumberFormat('id-ID').format(item.online_price || 0);
            let rpModal = new Intl.NumberFormat('id-ID').format(item.modal_price || 0);
            
            // FIXED PATH GAMBAR
            let imgSrc = (item.image && item.image !== 'no-image.png') 
                ? `../../assets/img/${item.image}` 
                : `../../assets/img/no-image.png`;

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-4 text-center text-secondary">${index + 1}</td>
                    <td class="p-4 text-center">
                        <img src="${imgSrc}" class="w-10 h-10 object-cover rounded-lg border shadow-sm mx-auto" alt="Produk">
                    </td>
                    <td class="p-4 font-semibold">${item.code}</td>
                    <td class="p-4 font-bold">${item.name}</td>
                    <td class="p-4">
                        <span class="bg-slate-100 px-2 py-1 rounded text-[10px] font-bold uppercase">${item.category || '-'}</span>
                    </td>
                    <td class="p-4 text-right text-rose-600 font-semibold">${rpModal}</td>
                    <td class="p-4 text-right">
                        <div class="text-emerald-600 font-black">Off: ${rpJual}</div>
                        <div class="text-blue-600 text-[10px] font-bold">On: ${rpOnline}</div>
                    </td>
                    <td class="p-4 text-center">${item.stock > 0 ? item.stock : '<span class="text-danger font-bold">Kosong</span>'}</td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-1">
                            <button onclick='editData(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="p-2 bg-accent/10 text-accent rounded-lg hover:bg-accent hover:text-white transition-all"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="deleteData(${item.id})" class="p-2 bg-danger/10 text-danger rounded-lg hover:bg-danger hover:text-white transition-all"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
        });
        tbody.innerHTML = html || '<tr><td colspan="9" class="p-8 text-center text-secondary">Belum ada data produk.</td></tr>';
    } else {
        tbody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-danger">Gagal memuat data: ${response.message}</td></tr>`;
    }
}

document.getElementById('formProduk').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);

    if (response.status === 'success') {
        closeModal('modal-produk');
        loadData(); 
        // Menggunakan alert native jika SweetAlert tidak terdeteksi di form, atau bisa diubah ke custom alert
        alert(response.message);
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('product_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    document.getElementById('category').value = item.category || 'Roti Manis';
    document.getElementById('modal_price').value = item.modal_price || 0;
    document.getElementById('price').value = item.price || 0;
    document.getElementById('online_price').value = item.online_price || 0;
    
    // Handle form image update - FIXED PATH GAMBAR
    document.getElementById('old_image').value = item.image || '';
    document.getElementById('image_preview').src = (item.image && item.image !== 'no-image.png') 
        ? `../../assets/img/${item.image}` 
        : `../../assets/img/no-image.png`;
    document.getElementById('image').value = ''; // Clear file input
    
    document.getElementById('modal-title').innerText = 'Edit Produk';
    openModal('modal-produk');
}

async function deleteData(id) {
    customConfirm('Yakin ingin menghapus data produk ini?', async () => {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        
        if (response.status === 'success') {
            loadData();
            alert('Berhasil menghapus Data Produk!'); 
        } else {
            alert('Gagal menghapus: ' + response.message); 
        }
    });
}

document.getElementById('formImport').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btnSubmit = document.getElementById('btn-import-submit');
    const originalText = btnSubmit.innerHTML;

    btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Mengupload...';
    btnSubmit.disabled = true;

    const formData = new FormData(this);

    try {
        const response = await fetchAjax('logic.php?action=import', 'POST', formData);

        if (response.status === 'success') {
            alert(response.message);
            closeModal('modal-import');
            document.getElementById('formImport').reset();
            loadData(); 
        } else {
            alert('Gagal: ' + response.message);
        }
    } catch (error) {
        alert("Terjadi kesalahan sistem saat upload file.");
    } finally {
        btnSubmit.innerHTML = originalText;
        btnSubmit.disabled = false;
    }
});