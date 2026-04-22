document.addEventListener('DOMContentLoaded', () => {
    loadMetode();
});

// ==========================================
// RENDER DAFTAR METODE PEMBAYARAN
// ==========================================
async function loadMetode() {
    const listContainer = document.getElementById('list-metode');
    listContainer.innerHTML = '<li class="p-4 text-center text-slate-400 text-sm"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat data...</li>';

    const res = await fetchAjax('logic.php?action=read', 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<li class="p-6 text-center text-slate-400 italic text-xs font-bold">Belum ada metode pembayaran.</li>';
        } else {
            res.data.forEach(item => {
                html += `
                    <li class="p-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
                        <span class="font-bold text-sm text-slate-700">${item.name}</span>
                        <button onclick="hapusMetode(${item.id}, '${item.name}')" class="text-rose-400 hover:text-rose-600 transition-colors" title="Hapus">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </li>
                `;
            });
        }
        listContainer.innerHTML = html;
    }
}

// ==========================================
// TAMBAH METODE BARU
// ==========================================
document.getElementById('form-metode').addEventListener('submit', async function(e) {
    e.preventDefault();
    const nameInput = document.getElementById('method_name');
    const nameVal = nameInput.value.trim();

    if(!nameVal) return;

    Swal.fire({ title: 'Menyimpan...', icon: 'info', showConfirmButton: false, allowOutsideClick: false });

    const formData = new FormData();
    formData.append('action', 'save');
    formData.append('name', nameVal);

    const res = await fetchAjax('logic.php', 'POST', formData);

    if (res.status === 'success') {
        Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
        nameInput.value = ''; // Reset input
        loadMetode(); // Refresh daftar
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

// ==========================================
// HAPUS METODE
// ==========================================
async function hapusMetode(id, name) {
    const confirm = await Swal.fire({
        title: 'Hapus Metode?',
        text: `Apakah Anda yakin ingin menghapus metode pembayaran "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });

    if (confirm.isConfirmed) {
        Swal.fire({ title: 'Menghapus...', icon: 'info', showConfirmButton: false });

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const res = await fetchAjax('logic.php', 'POST', formData);

        if (res.status === 'success') {
            Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            loadMetode();
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}