// Load data saat halaman pertama kali dibuka
document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

// Fungsi Reset Form & Ubah Judul Modal
function resetForm() {
    document.getElementById('formProduk').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Produk Baru';
}

// Fungsi Load Data Table
async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="7" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="7" class="p-8 text-center text-secondary">Belum ada data produk.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                // Format Rupiah
                let rp = new Intl.NumberFormat('id-ID').format(item.price);
                
                // Badge stok (Jika stok 0 merah, jika ada hijau)
                let stockBadge = item.stock > 0 
                    ? `<span class="bg-success/10 text-success px-2 py-1 rounded-md text-xs font-bold">${item.stock}</span>` 
                    : `<span class="bg-danger/10 text-danger px-2 py-1 rounded-md text-xs font-bold">Kosong</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-semibold text-slate-700">${item.code}</td>
                        <td class="p-4 text-slate-800">${item.name}</td>
                        <td class="p-4"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs">${item.category}</span></td>
                        <td class="p-4 text-right font-medium text-slate-700">${rp}</td>
                        <td class="p-4 text-center">${stockBadge}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

// Fungsi Submit Form (Tambah / Edit)
document.getElementById('formProduk').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        alert(response.message); // Nanti bisa diganti SweetAlert
        closeModal('modal-produk');
        loadData(); // Reload tabel otomatis
    } else {
        alert('Gagal: ' + response.message);
    }
});

// Fungsi Edit (Isi form dengan data yang dipilih)
function editData(item) {
    document.getElementById('product_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    document.getElementById('category').value = item.category;
    document.getElementById('price').value = item.price;
    
    document.getElementById('modal-title').innerText = 'Edit Produk';
    openModal('modal-produk');
}

// Fungsi Delete
async function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        if (response.status === 'success') {
            loadData(); // Reload tabel
        } else {
            alert('Gagal menghapus: ' + response.message);
        }
    }
}