document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

async function loadData() {
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-secondary"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memuat data jabatan...</td></tr>';
    
    const response = await fetchAjax('logic.php?action=read', 'GET');
    
    if (response.status === 'success') {
        let html = '';
        if (response.data.length === 0) {
            html = '<tr><td colspan="5" class="p-8 text-center text-secondary">Belum ada data jabatan.</td></tr>';
        } else {
            // Daftar jabatan inti yang tidak boleh dihapus/ubah slugnya
            const coreRoles = ['owner', 'admin', 'produksi', 'auditor', 'gudang_pilar'];

            response.data.forEach((item, index) => {
                const isCore = coreRoles.includes(item.role_slug);
                const badgeCore = isCore ? `<span class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded text-[10px] ml-2 uppercase font-bold">Bawaan Sistem</span>` : '';
                
                let btnHapus = '';
                if (!isCore) {
                    btnHapus = `<button onclick="deleteData('${item.role_slug}', '${item.role_name}')" class="bg-slate-100 hover:bg-danger hover:text-white text-slate-500 w-8 h-8 rounded-lg flex items-center justify-center transition-colors shadow-sm" title="Hapus"><i class="fa-solid fa-trash text-xs"></i></button>`;
                }

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center text-secondary text-xs">${index + 1}</td>
                        <td class="p-4 font-bold text-slate-800">${item.role_name} ${badgeCore}</td>
                        <td class="p-4 font-mono text-xs text-primary font-bold">@${item.role_slug}</td>
                        <td class="p-4 text-center">
                            <span class="bg-slate-100 border border-slate-200 text-slate-600 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                ${item.total_akses} Menu
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openModalEdit('${item.role_slug}')" class="bg-amber-500 hover:bg-amber-600 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-colors shadow-sm" title="Edit Akses">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                ${btnHapus}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

function openModalAdd() {
    document.getElementById('form_mode').value = 'add';
    document.getElementById('old_slug').value = '';
    document.getElementById('role_name').value = '';
    document.getElementById('role_slug').value = '';
    document.getElementById('role_slug').readOnly = false;
    document.getElementById('role_slug').classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-400');
    
    // PERBAIKAN 1: Uncheck SEMUA jenis checkbox (Induk, Edit, Hapus)
    document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(cb => cb.checked = false);
    
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-shield-halved text-primary mr-2"></i> Tambah Jabatan Baru';
    openModal('modal-role');
}

async function openModalEdit(slug) {
    Swal.fire({ title: 'Memuat Hak Akses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    const response = await fetchAjax(`logic.php?action=get_permissions&slug=${slug}`, 'GET');
    Swal.close();

    if (response.status === 'success') {
        document.getElementById('form_mode').value = 'edit';
        document.getElementById('old_slug').value = response.role.role_slug;
        document.getElementById('role_name').value = response.role.role_name;
        
        const slugInput = document.getElementById('role_slug');
        slugInput.value = response.role.role_slug;
        
        // Jabatan inti tidak boleh ganti slug biar sistem tidak error
        const coreRoles = ['owner', 'admin', 'produksi', 'auditor'];
        if (coreRoles.includes(response.role.role_slug)) {
            slugInput.readOnly = true;
            slugInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-400');
        } else {
            slugInput.readOnly = false;
            slugInput.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-400');
        }

        // PERBAIKAN 2: Reset semua checkbox lalu centang sesuai data DB (termasuk Edit/Hapus)
        document.querySelectorAll('input[type="checkbox"][name="permissions[]"]').forEach(cb => cb.checked = false);
        response.permissions.forEach(perm => {
            const cb = document.querySelector(`input[type="checkbox"][value="${perm}"]`);
            if (cb) cb.checked = true;
        });

        document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-pen-to-square text-amber-500 mr-2"></i> Edit Hak Akses Jabatan';
        openModal('modal-role');
    }
}

// Auto isi Slug saat mengetik Nama Jabatan (Khusus saat Add)
document.getElementById('role_name').addEventListener('input', function() {
    if (document.getElementById('form_mode').value === 'add') {
        let slug = this.value.toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, '_');
        document.getElementById('role_slug').value = slug;
    }
});

async function saveData() {
    const mode = document.getElementById('form_mode').value;
    const old_slug = document.getElementById('old_slug').value;
    const role_name = document.getElementById('role_name').value;
    const role_slug = document.getElementById('role_slug').value;

    if (!role_name || !role_slug) {
        Swal.fire('Peringatan', 'Nama dan Kode Slug wajib diisi!', 'warning');
        return;
    }

    // PERBAIKAN 3: Ambil SEMUA data checkbox yang tercentang (Induk, Edit, dan Hapus)
    const permissions = [];
    document.querySelectorAll('input[type="checkbox"][name="permissions[]"]:checked').forEach(cb => {
        permissions.push(cb.value);
    });

    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

    const formData = new FormData();
    formData.append('mode', mode);
    formData.append('old_slug', old_slug);
    formData.append('role_name', role_name);
    formData.append('role_slug', role_slug);
    
    // Append array permissions
    permissions.forEach(perm => {
        formData.append('permissions[]', perm);
    });

    const response = await fetchAjax('logic.php?action=save', 'POST', formData);
    
    if (response.status === 'success') {
        Swal.fire('Berhasil!', response.message, 'success');
        closeModal('modal-role');
        loadData();
    } else {
        Swal.fire('Gagal!', response.message, 'error');
    }
}

async function deleteData(slug, name) {
    const result = await Swal.fire({
        title: 'Hapus Jabatan?',
        html: `Anda yakin ingin menghapus jabatan <b>${name}</b>?<br>Hak akses menu mereka juga akan terhapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        
        const formData = new FormData();
        formData.append('slug', slug);
        
        const response = await fetchAjax('logic.php?action=delete', 'POST', formData);
        
        if (response.status === 'success') {
            Swal.fire('Terhapus!', response.message, 'success');
            loadData();
        } else {
            Swal.fire('Gagal!', response.message, 'error');
        }
    }
}