document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function resetForm() {
    document.getElementById('formGudang').reset();
    document.getElementById('warehouse_id').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Gudang';
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="4" class="p-8 text-center text-secondary">Belum ada data gudang.</td></tr>';
        } else {
            response.data.forEach((item, index) => {
                
                // SUNTIKAN 3: Merender tombol Edit/Hapus secara dinamis
                let btnAksi = '';
                
                if (canEdit) {
                    btnAksi += `<button onclick='editData(${JSON.stringify(item)})' class="w-8 h-8 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-surface transition-colors flex items-center justify-center" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>`;
                }
                
                if (canDelete) {
                    btnAksi += `<button onclick="deleteData(${item.id})" class="w-8 h-8 rounded-lg bg-danger/10 text-danger hover:bg-danger hover:text-surface transition-colors flex items-center justify-center" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>`;
                }

                // Jika tidak punya akses edit maupun hapus
                if (btnAksi === '') {
                    btnAksi = '<span class="text-[10px] font-bold text-slate-400">Akses Dibatasi</span>';
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary">${index + 1}</td>
                        <td class="p-4 font-semibold text-slate-700">${item.code}</td>
                        <td class="p-4 text-slate-800">${item.name}</td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
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

document.getElementById('formGudang').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        closeModal('modal-gudang');
        loadData(); 
    } else {
        alert('Gagal: ' + response.message);
    }
});

function editData(item) {
    document.getElementById('warehouse_id').value = item.id;
    document.getElementById('code').value = item.code;
    document.getElementById('name').value = item.name;
    
    document.getElementById('modal-title').innerText = 'Edit Gudang';
    openModal('modal-gudang');
}

async function deleteData(id) {
    // Memanggil customConfirm yang sudah kita buat di head.php
    customConfirm('Yakin ingin menghapus data gudang ini?', async () => {
        
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        
        if (response.status === 'success') {
            loadData();
            // Tambahkan alert sukses agar Toast hijau muncul di layar!
            alert('Berhasil menghapus Data Gudang!'); 
        } else {
            // Memunculkan popup error merah
            alert('Gagal menghapus: ' + response.message); 
        }

    });
}