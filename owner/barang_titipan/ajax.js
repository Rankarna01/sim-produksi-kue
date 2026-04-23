let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadData();
});

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function cariData() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadData();
    }, 300);
}

async function loadData() {
    const tbody = document.getElementById('table-body');
    const search = document.getElementById('search-data').value;
    
    tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    try {
        // Asumsi fetchAjax sudah ada di sistemmu (kalau belum, ini pakai native fetch)
        const response = await fetch(`logic.php?action=read&search=${search}`);
        const res = await response.json();

        if (res.status === 'success') {
            let html = '';
            if (res.data.length === 0) {
                html = '<tr><td colspan="7" class="p-10 text-center text-slate-400 italic">Belum ada data barang titipan.</td></tr>';
            } else {
                res.data.forEach((item, index) => {
                    let profit = item.harga_jual - item.harga_modal;
                    let stokClass = item.stok <= 5 ? 'text-rose-600 bg-rose-50' : 'text-blue-600 bg-blue-50';

                    html += `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-5 text-center font-mono text-slate-400 text-xs">${index + 1}</td>
                            <td class="p-5">
                                <h4 class="font-black text-slate-800 text-sm uppercase">${item.nama_barang}</h4>
                                <p class="text-[10px] font-bold text-slate-400 tracking-widest mt-1"><i class="fa-solid fa-shop"></i> ${item.nama_umkm}</p>
                            </td>
                            <td class="p-5 text-right font-bold text-slate-600">${formatRupiah(item.harga_modal)}</td>
                            <td class="p-5 text-right font-black text-emerald-600">${formatRupiah(item.harga_jual)}</td>
                            <td class="p-5 text-center font-black text-blue-600">+${formatRupiah(profit)}</td>
                            <td class="p-5 text-center">
                                <span class="px-3 py-1 rounded-lg text-xs font-black ${stokClass}">${item.stok} Pcs</span>
                            </td>
                            <td class="p-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editData(${item.id})" class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-all" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button onclick="hapusData(${item.id})" class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all" title="Hapus">
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
    } catch (e) {
        console.error(e);
        tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center text-rose-500">Gagal memuat data!</td></tr>';
    }
}

function bukaModal() {
    document.getElementById('formData').reset();
    document.getElementById('id_barang').value = '';
    document.getElementById('modal-title').innerText = 'Tambah Barang Titipan';
    document.getElementById('modal-form').classList.remove('hidden');
}

function tutupModal() {
    document.getElementById('modal-form').classList.add('hidden');
}

document.getElementById('formData').addEventListener('submit', async function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Menyimpan...', showConfirmButton: false, didOpen: () => Swal.showLoading() });

    const formData = new FormData(this);
    formData.append('action', 'save');

    try {
        const response = await fetch('logic.php', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'success') {
            tutupModal();
            Swal.fire('Berhasil!', res.message, 'success');
            loadData();
        } else {
            Swal.fire('Ups!', res.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
});

async function editData(id) {
    Swal.fire({ title: 'Memuat...', showConfirmButton: false, didOpen: () => Swal.showLoading() });
    try {
        const response = await fetch(`logic.php?action=get_detail&id=${id}`);
        const res = await response.json();

        if (res.status === 'success') {
            document.getElementById('id_barang').value = res.data.id;
            document.getElementById('nama_barang').value = res.data.nama_barang;
            document.getElementById('nama_umkm').value = res.data.nama_umkm;
            document.getElementById('harga_modal').value = res.data.harga_modal;
            document.getElementById('harga_jual').value = res.data.harga_jual;
            document.getElementById('stok').value = res.data.stok;
            
            document.getElementById('modal-title').innerText = 'Edit Barang Titipan';
            document.getElementById('modal-form').classList.remove('hidden');
            Swal.close();
        }
    } catch (e) {
        Swal.fire('Error', 'Gagal memuat data.', 'error');
    }
}

function hapusData(id) {
    Swal.fire({
        title: 'Hapus Barang?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const response = await fetch('logic.php', { method: 'POST', body: formData });
            const res = await response.json();

            if (res.status === 'success') {
                Swal.fire('Terhapus!', res.message, 'success');
                loadData();
            }
        }
    });
}