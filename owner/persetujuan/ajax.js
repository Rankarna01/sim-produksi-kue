document.addEventListener("DOMContentLoaded", () => {
    loadData();
});

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }

function formatTglTime(datetime) {
    const d = new Date(datetime);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

async function loadData() {
    const tbody = document.getElementById('table-data');
    tbody.innerHTML = '<tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>';
    
    const res = await fetchAjax('logic.php?action=read', 'GET');
    
    if (res.status === 'success') {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="p-8 text-center text-slate-400 italic font-bold">Belum ada data pengajuan resep.</td></tr>';
        } else {
            res.data.forEach((item, idx) => {
                
                // Styling Badge Status
                let badgeStatus = '';
                if(item.status === 'pending') badgeStatus = `<span class="bg-amber-100 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-clock mr-1"></i> Menunggu</span>`;
                else if(item.status === 'approved') badgeStatus = `<span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-check-double mr-1"></i> Disetujui</span>`;
                else badgeStatus = `<span class="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>`;

                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-5 text-center text-xs font-bold text-slate-400">${idx + 1}</td>
                        <td class="p-5">
                            <div class="font-black text-blue-600 text-sm">${item.request_no}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">${formatTglTime(item.created_at)}</div>
                        </td>
                        <td class="p-5">
                            <div class="font-black text-slate-800 uppercase">${item.product_name}</div>
                            <div class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-user-pen mr-1"></i> ${item.user_name}</div>
                        </td>
                        <td class="p-5 text-xs text-slate-600 italic max-w-xs truncate">${item.notes || '-'}</td>
                        <td class="p-5 text-center">${badgeStatus}</td>
                        <td class="p-5 text-center">
                            <button onclick="lihatDetail(${item.id})" class="bg-white border border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 px-4 py-2 rounded-xl text-xs font-black transition-all shadow-sm flex items-center justify-center mx-auto gap-2">
                                <i class="fa-solid fa-eye"></i> Cek Resep
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tbody.innerHTML = html;
    }
}

async function lihatDetail(id) {
    Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    const res = await fetchAjax(`logic.php?action=read_detail&id=${id}`, 'GET');
    Swal.close();

    if (res.status === 'success') {
        document.getElementById('det_product').innerText = res.header.product_name;
        document.getElementById('det_notes').innerText = `"${res.header.notes}"`;

        // Render Bahan
        let htmlBahan = '';
        if (res.details.length === 0) {
            htmlBahan = '<tr><td colspan="3" class="p-6 text-center text-slate-400 italic font-bold text-xs">Resep kosong.</td></tr>';
        } else {
            res.details.forEach((item, idx) => {
                htmlBahan += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-slate-400 text-xs">${idx + 1}</td>
                        <td class="p-4 font-black text-slate-700 uppercase text-xs">${item.material_name}</td>
                        <td class="p-4 text-right font-black text-blue-600 text-sm">${parseFloat(item.quantity_needed)} <span class="text-[9px] text-slate-400 uppercase tracking-widest">${item.unit_used}</span></td>
                    </tr>
                `;
            });
        }
        document.getElementById('table-detail-bahan').innerHTML = htmlBahan;

        // Render Tombol Aksi di Footer Modal berdasarkan Status
        const footer = document.getElementById('modal-footer-aksi');
        if (res.header.status === 'pending') {
            footer.innerHTML = `
                <button type="button" onclick="closeModal('modal-detail')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all text-xs uppercase tracking-widest">Tutup</button>
                <button type="button" onclick="prosesTolak(${id})" class="bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-xmark"></i> Tolak
                </button>
                <button type="button" onclick="prosesSetuju(${id})" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-emerald-200 flex items-center gap-2">
                    <i class="fa-solid fa-check-double"></i> Setujui & Terapkan
                </button>
            `;
        } else {
            // Kalau sudah approved/rejected, tombolnya cuma tutup
            footer.innerHTML = `
                <button type="button" onclick="closeModal('modal-detail')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all text-xs uppercase tracking-widest">Tutup Laporan</button>
            `;
        }

        openModal('modal-detail');
    }
}

async function prosesSetuju(id) {
    const confirm = await Swal.fire({
        title: 'Setujui Resep Baru?',
        text: "Resep lama akan dihapus dan digantikan dengan komposisi baru ini secara permanen.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Ya, Setujui & Terapkan'
    });

    if(confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'approve'); formData.append('id', id);
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        if(res.status === 'success') {
            closeModal('modal-detail');
            loadData();
            Swal.fire('Disetujui!', res.message, 'success');
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}

async function prosesTolak(id) {
    const confirm = await Swal.fire({
        title: 'Tolak Pengajuan?',
        text: "Pengajuan ini akan ditolak dan resep lama tetap digunakan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        confirmButtonText: 'Ya, Tolak'
    });

    if(confirm.isConfirmed) {
        Swal.fire({ title: 'Memproses...', icon: 'info', allowOutsideClick: false, showConfirmButton: false });
        const formData = new FormData(); formData.append('action', 'reject'); formData.append('id', id);
        
        const res = await fetchAjax('logic.php', 'POST', formData);
        if(res.status === 'success') {
            closeModal('modal-detail');
            loadData();
            Swal.fire('Ditolak!', res.message, 'success');
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    }
}