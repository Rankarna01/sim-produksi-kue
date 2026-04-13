document.addEventListener('DOMContentLoaded', () => {
    loadPermintaan();
});

async function loadPermintaan() {
    const tbody = document.getElementById('list-permintaan');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';

    const res = await fetchAjax('logic.php?action=read_permintaan', 'GET');
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-10 text-center text-slate-400 font-bold italic">Tidak ada antrean permintaan barang.</td></tr>';
        } else {
            res.data.forEach((item, index) => {
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-slate-400 font-mono text-xs">${index + 1}</td>
                        <td class="p-5">
                            <div class="font-black text-slate-800 text-xs tracking-tighter">${item.request_no}</div>
                            <div class="text-[10px] text-slate-400 font-bold">${item.created_at}</div>
                        </td>
                        <td class="p-5 font-bold text-slate-700 uppercase text-xs">${item.nama_dapur}</td>
                        <td class="p-5">
                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-lg text-[11px] font-black uppercase border border-blue-100">${item.material_name}</span>
                        </td>
                        <td class="p-5 text-center font-black text-slate-800 text-lg">
                            ${parseFloat(item.qty_requested)} <span class="text-[10px] text-slate-400">${item.unit}</span>
                        </td>
                        <td class="p-5 text-center">
                            <button onclick='openModalProses(${JSON.stringify(item)})' class="bg-blue-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:shadow-lg hover:shadow-blue-200 transition-all active:scale-95">Proses</button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

function openModalProses(item) {
    document.getElementById('modal-id').value = item.id;
    document.getElementById('modal-bahan').value = item.material_name;
    document.getElementById('modal-qty-minta').value = `${parseFloat(item.qty_requested)} ${item.unit}`;
    document.getElementById('modal-qty-kirim').value = parseFloat(item.qty_requested);
    document.getElementById('modal-proses').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modal-proses').classList.add('hidden');
}

document.getElementById('form-proses').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Asumsi fungsi global loading dari RotiKu ERP
    if(typeof showLoading === "function") showLoading();
    
    const res = await fetchAjax('logic.php', 'POST', formData);
    
    if(typeof hideLoading === "function") hideLoading();

    if (res.status === 'success') {
        Swal.fire('Berhasil!', res.message, 'success');
        closeModal();
        loadPermintaan();
    } else {
        Swal.fire('Gagal!', res.message, 'error');
    }
});

// FITUR BARU: FUNGSI UNTUK TOLAK PERMINTAAN
function rejectPermintaan() {
    const id = document.getElementById('modal-id').value;
    
    Swal.fire({
        title: 'Tolak Permintaan?',
        text: "Permintaan barang ini akan dibatalkan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'Ya, Tolak!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'tolak_kirim');
            formData.append('id', id);

            if(typeof showLoading === "function") showLoading();
            const res = await fetchAjax('logic.php', 'POST', formData);
            if(typeof hideLoading === "function") hideLoading();

            if (res.status === 'success') {
                Swal.fire('Ditolak!', res.message, 'success');
                closeModal();
                loadPermintaan();
            } else {
                Swal.fire('Gagal!', res.message, 'error');
            }
        }
    });
}