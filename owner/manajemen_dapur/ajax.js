document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function resetForm() {
    document.getElementById('formDapur').reset();
    document.getElementById('dapur_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Dapur Baru';
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="4" class="p-8 text-center text-secondary">Belum ada data dapur.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                // Tombol Aksi bergaya sama dengan Data Gudang / Bahan Baku
                let btnAksi = `
                    <button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center shadow-sm" title="Hapus">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                `;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-700">${item.name}</td>
                        <td class="p-4 font-semibold text-slate-500">${item.location || '-'}</td>
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

document.getElementById('formDapur').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-dapur');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('dapur_id').value = item.id;
    document.getElementById('name').value = item.name;
    document.getElementById('location').value = item.location;
    document.getElementById('modal-title').innerText = 'Edit Data Dapur';
    openModal('modal-dapur');
}

function deleteData(id) {
    customConfirm('Yakin ingin menghapus dapur ini? Pastikan tidak ada stok bahan yang masih terikat di dapur ini.', async () => {
        const formData = new FormData();
        formData.append('id', id);
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        
        if (response.status === 'success') {
            loadData();
            alert('Berhasil menghapus dapur!'); 
        } else {
            alert('Gagal menghapus: ' + response.message); 
        }
    });
}