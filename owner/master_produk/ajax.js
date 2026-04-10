document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function resetForm() {
    document.getElementById('formProduk').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Produk Baru';
}

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

                let rp = new Intl.NumberFormat('id-ID').format(item.price);
                let stockBadge = item.stock > 0
                    ? `<span class="bg-success/10 text-success px-2 py-1 rounded-md text-xs font-bold">${item.stock}</span>`
                    : `<span class="bg-danger/10 text-danger px-2 py-1 rounded-md text-xs font-bold">Kosong</span>`;

                // SUNTIKAN 3: Tombol Aksi Dinamis berdasarkan Permission
                let btnAksi = '';
                
                if (canEdit) {
                    btnAksi += `<button onclick='editData(${JSON.stringify(item).replace(/'/g, "&apos;")})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>&nbsp;`;
                }
                
                if (canDelete) {
                    btnAksi += `<button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>`;
                }

                if (btnAksi === '') {
                    btnAksi = '<span class="text-[10px] font-bold text-slate-400">Akses Dibatasi</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-semibold text-slate-700">${item.code}</td>
                        <td class="p-4 text-slate-800 font-bold">${item.name}</td>
                        <td class="p-4"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">${item.category}</span></td>
                        <td class="p-4 text-right font-black text-slate-700">${rp}</td>
                        <td class="p-4 text-center">${stockBadge}</td>
                        <td class="p-4 text-center print:hidden">
                            <div class="flex items-center justify-center gap-1">
                                ${btnAksi}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

document.getElementById('formProduk').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);

    if (response.status === 'success') {
        closeModal('modal-produk');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('product_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    document.getElementById('category').value = item.category;
    document.getElementById('price').value = item.price;

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