document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

function bukaModalTambah() {
    document.getElementById('form-role').reset();
    document.getElementById('role_id').value = '';
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-shield-halved text-blue-600"></i> Tambah Jabatan Baru';
    document.getElementById('role_slug').readOnly = false;
    openModal('modal-role');
}

// Auto-generate Slug (Hanya untuk tambah baru)
document.getElementById('role_name').addEventListener('input', function() {
    if (!document.getElementById('role_id').value) {
        let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
        document.getElementById('role_slug').value = slug;
    }
});

async function loadData() {
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read', 'GET');
    
    if (res.status === 'success') {
        let html = '';
        
        // DAFTAR SLUG JABATAN YANG DILARANG DIHAPUS
        const protectedRoles = ['owner_gudang', 'owner_produksi', 'admin_gudang', 'admin_produksi'];

        res.data.forEach((item, idx) => {
            // Cek apakah slug-nya ada di daftar perlindungan
            let btnDelete = '';
            if (protectedRoles.includes(item.role_slug)) {
                btnDelete = `<button disabled class="w-8 h-8 rounded-lg bg-slate-50 text-slate-300 flex items-center justify-center cursor-not-allowed" title="Jabatan Master Tidak Bisa Dihapus"><i class="fa-solid fa-lock text-[10px]"></i></button>`;
            } else {
                btnDelete = `<button onclick="hapusData(${item.id})" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Hapus Jabatan"><i class="fa-solid fa-trash-can text-xs"></i></button>`;
            }

            html += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                    <td class="p-5 text-sm font-black text-slate-800">${item.role_name}</td>
                    <td class="p-5"><span class="bg-blue-50 text-blue-600 font-mono px-2 py-1 rounded text-[10px] font-bold tracking-widest">${item.role_slug}</span></td>
                    <td class="p-5 text-xs font-bold text-slate-500"><span class="text-emerald-600 font-black">${item.total_perms}</span> Menu Terbuka</td>
                    <td class="p-5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editData(${item.id})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Edit Hak Akses">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                            </button>
                            ${btnDelete}
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }
}

async function editData(id) {
    document.getElementById('form-role').reset();
    document.getElementById('modal-title').innerHTML = '<i class="fa-solid fa-shield-halved text-blue-600"></i> Edit Akses Jabatan';
    
    const res = await fetchAjax(`logic.php?action=get_detail&id=${id}`, 'GET');
    
    if (res.status === 'success') {
        document.getElementById('role_id').value = res.data.id;
        document.getElementById('role_name').value = res.data.role_name;
        document.getElementById('role_slug').value = res.data.role_slug;
        
        // Kunci Slug jika yang diedit adalah jabatan Inti agar tidak bisa diubah slug-nya sembarangan
        const protectedRoles = ['owner_gudang', 'owner_produksi', 'admin_gudang', 'admin_produksi'];
        document.getElementById('role_slug').readOnly = protectedRoles.includes(res.data.role_slug);

        // Centang permissions yang dimiliki
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        checkboxes.forEach(cb => {
            cb.checked = res.data.permissions.includes(cb.value);
        });

        openModal('modal-role');
    }
}

document.getElementById('form-role').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });

    const formData = new FormData(this);
    formData.append('action', 'save'); 

    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if (res.status === 'success') {
        closeModal('modal-role');
        loadData();
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

async function hapusData(id) {
    const result = await Swal.fire({
        title: 'Hapus Jabatan?',
        text: "Semua hak akses yang terikat akan hilang. Pastikan tidak ada user aktif di jabatan ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Ya, Hapus!'
    });

    if (result.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', icon: 'info', showConfirmButton: false });
        const formData = new FormData(); 
        formData.append('action', 'delete'); 
        formData.append('id', id);
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        if (res.status === 'success') {
            loadData();
            Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}