// Auto-refresh setiap 30 detik agar dapur selalu up-to-date
let autoRefresh;

document.addEventListener("DOMContentLoaded", () => {
    loadOrders();
    autoRefresh = setInterval(loadOrders, 30000); 
});

async function loadOrders() {
    const container = document.getElementById('order-container');
    const loader = document.getElementById('loader');
    
    if(loader) loader.style.display = 'block';

    try {
        const response = await fetch(`logic.php?action=read&nocache=${new Date().getTime()}`);
        const result = await response.json();

        if (result.status === 'success') {
            renderOrders(result.data);
        } else {
            console.error("Gagal menarik data pesanan:", result.message);
        }
    } catch (error) {
        console.error("Kesalahan jaringan:", error);
    }
}

function renderOrders(orders) {
    const container = document.getElementById('order-container');
    container.innerHTML = ''; // Bersihkan container

    if (orders.length === 0) {
        container.innerHTML = `
            <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-3xl border border-slate-200 border-dashed">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-5xl text-slate-300">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                <h3 class="text-xl font-black text-slate-700">Dapur Sedang Kosong</h3>
                <p class="text-sm font-bold text-slate-400 mt-1">Belum ada pesanan custom masuk dari Kasir.</p>
            </div>
        `;
        return;
    }

    orders.forEach(order => {
        // Tentukan style berdasarkan status
        let statusBadge = '';
        let actionBtn = '';
        let cardStyle = '';

        if (order.production_status === 'pending') {
            statusBadge = '<span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-lg text-xs font-black"><i class="fa-solid fa-clock mr-1"></i> Baru Masuk</span>';
            actionBtn = `<button onclick="updateStatus(${order.id}, 'diproses')" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-black py-3 rounded-xl mt-4 transition-all shadow-md shadow-orange-500/30">Mulai Masak</button>`;
            cardStyle = 'border-rose-200 shadow-rose-100/50';
        } else if (order.production_status === 'diproses') {
            statusBadge = '<span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-lg text-xs font-black animate-pulse"><i class="fa-solid fa-fire mr-1"></i> Sedang Dimasak</span>';
            actionBtn = `<button onclick="updateStatus(${order.id}, 'selesai')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-3 rounded-xl mt-4 transition-all shadow-md shadow-emerald-500/30"><i class="fa-solid fa-check mr-1"></i> Tandai Selesai</button>`;
            cardStyle = 'border-orange-300 border-2 bg-orange-50/10';
        }

        // Render daftar item custom
        let itemsHtml = '';
        order.custom_items.forEach(item => {
            itemsHtml += `
                <div class="flex justify-between items-start border-b border-slate-100 pb-2 mb-2 last:border-0 last:mb-0 last:pb-0">
                    <div class="font-bold text-slate-800 text-sm"><span class="text-orange-500 mr-1">🛠️</span> ${item.custom_name}</div>
                    <div class="font-black text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-sm">${item.qty}x</div>
                </div>
            `;
        });

        // Waktu pesan
        const timeObj = new Date(order.created_at);
        const timeStr = timeObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

        // Badge Tipe Order (Toko / Grab)
        const typeBadge = order.order_type === 'online' 
            ? `<span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[10px] font-black uppercase">${order.channel}</span>` 
            : `<span class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded text-[10px] font-black uppercase">TOKO</span>`;

        const card = `
            <div class="bg-white rounded-3xl p-5 shadow-sm transition-all hover:shadow-md flex flex-col h-full border ${cardStyle}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-black text-lg text-slate-800 leading-none mb-1">#${order.invoice_no.split('-')[1]}</h4>
                        <div class="text-[10px] font-bold text-slate-400 mt-1 flex items-center gap-2">
                            <span><i class="fa-regular fa-clock"></i> ${timeStr}</span>
                            ${typeBadge}
                        </div>
                    </div>
                    ${statusBadge}
                </div>
                
                <div class="text-sm font-bold text-slate-600 mb-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    <i class="fa-solid fa-user text-slate-400 mr-1"></i> ${order.customer_name || 'Pelanggan Umum'}
                </div>

                <div class="flex-1 bg-white border border-slate-200 p-3 rounded-xl custom-scrollbar overflow-y-auto mb-3 max-h-[150px]">
                    <div class="text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest border-b border-slate-100 pb-1">Daftar Masakan</div>
                    ${itemsHtml}
                </div>

                ${order.notes ? `
                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-xl mb-1">
                    <div class="text-[10px] font-black text-yellow-600 mb-1 uppercase tracking-widest"><i class="fa-solid fa-note-sticky"></i> Catatan</div>
                    <p class="text-xs font-bold text-yellow-800 leading-tight">${order.notes}</p>
                </div>` : ''}

                <div class="mt-auto">
                    ${actionBtn}
                </div>
            </div>
        `;
        
        container.innerHTML += card;
    });
}

async function updateStatus(id, newStatus) {
    let confirmTxt = newStatus === 'diproses' ? "Mulai kerjakan pesanan ini?" : "Tandai pesanan ini selesai dan siap diambil?";
    
    if (confirm(confirmTxt)) {
        try {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', newStatus);

            const response = await fetch('logic.php?action=update_status', {
                method: 'POST',
                body: fd
            });
            const result = await response.json();

            if (result.status === 'success') {
                loadOrders(); // Refresh layar dapur
            } else {
                alert("Gagal update: " + result.message);
            }
        } catch (error) {
            alert("Kesalahan koneksi!");
        }
    }
}