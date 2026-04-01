document.getElementById('formSearch').addEventListener('submit', async function(e) {
    e.preventDefault();
    const inv = document.getElementById('search_invoice').value.trim();
    
    showLoading();
    const response = await fetchAjax(`logic.php?action=track_invoice&inv=${inv}`, 'GET');
    hideLoading();

    const container = document.getElementById('timeline-container');
    const eventsDiv = document.getElementById('timeline-events');

    if (response.status === 'success') {
        // Tampilkan Container
        container.classList.remove('hidden');
        
        // Isi Header
        document.getElementById('info-inv').innerText = response.invoice_no;
        
        // Format Status Akhir
        let st = response.current_status;
        let stHtml = '';
        if (st === 'pending') stHtml = '<span class="text-yellow-300"><i class="fa-solid fa-clock"></i> Pending (Antrean)</span>';
        else if (st === 'masuk_gudang') stHtml = '<span class="text-green-400"><i class="fa-solid fa-check-double"></i> Masuk Gudang</span>';
        else if (st === 'ditolak') stHtml = '<span class="text-red-400"><i class="fa-solid fa-triangle-exclamation"></i> Ditolak (Revisi)</span>';
        else if (st === 'expired') stHtml = '<span class="text-slate-400"><i class="fa-solid fa-ban"></i> Expired (Habis)</span>';
        document.getElementById('info-status').innerHTML = stHtml;

        // Render Events
        eventsDiv.innerHTML = '';
        
        response.events.forEach((evt, index) => {
            const dateObj = new Date(evt.time);
            const tgl = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const waktu = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            // Tentukan warna dan icon berdasarkan tipe event
            let dotColor = 'bg-slate-300';
            let icon = '<i class="fa-solid fa-circle text-xs"></i>';
            let bgLight = 'bg-slate-50';

            if (evt.type === 'start') {
                dotColor = 'bg-indigo-500';
                icon = '<i class="fa-solid fa-fire-burner text-white text-[10px]"></i>';
                bgLight = 'bg-indigo-50 border-indigo-100';
            } else if (evt.type === 'expired' || evt.type === 'rusak' || evt.type === 'rejected') {
                dotColor = 'bg-danger';
                icon = '<i class="fa-solid fa-xmark text-white text-[10px]"></i>';
                bgLight = 'bg-red-50 border-red-100';
            } else {
                // Tipe lainnya (Lainnya, Konsumsi)
                dotColor = 'bg-orange-500';
                icon = '<i class="fa-solid fa-arrow-right-from-bracket text-white text-[10px]"></i>';
                bgLight = 'bg-orange-50 border-orange-100';
            }

            // Animasi masuk (Fade In)
            const delay = index * 100;

            const html = `
                <div class="relative pl-6 sm:pl-8 animate-fade-in" style="animation-fill-mode: both; animation-delay: ${delay}ms;">
                    <div class="absolute w-6 h-6 rounded-full ${dotColor} -left-[13px] top-1.5 border-4 border-white flex items-center justify-center shadow-sm">
                        ${icon}
                    </div>
                    
                    <div class="p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm ${bgLight}">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2 gap-1">
                            <h4 class="text-base font-bold text-slate-800">${evt.title}</h4>
                            <span class="text-xs font-bold text-slate-500 bg-white px-2 py-1 rounded shadow-sm flex items-center gap-1 w-max">
                                <i class="fa-regular fa-clock"></i> ${tgl} - ${waktu} WIB
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            ${evt.description}
                        </p>
                    </div>
                </div>
            `;
            eventsDiv.insertAdjacentHTML('beforeend', html);
        });

    } else {
        alert(response.message);
        container.classList.add('hidden');
    }
});