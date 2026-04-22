let materialsData = [];
let racksData = [];
let printQueue = [];

document.addEventListener('DOMContentLoaded', async () => {
    await loadInitialData();
});

async function loadInitialData() {
    const res = await fetchAjax('logic.php?action=init_data', 'GET');
    if (res.status === 'success') {
        materialsData = res.materials;
        racksData = res.racks;

        let optMat = '<option value="">-- Pilih atau Cari Produk --</option>';
        res.materials.forEach(m => { optMat += `<option value="${m.id}">[${m.sku_code}] ${m.material_name}</option>`; });
        document.getElementById('select_product').innerHTML = optMat;

        let optRack = '<option value="">-- Pilih atau Cari Lokasi Rak --</option>';
        // Render dropdown rak
        res.racks.forEach(r => { optRack += `<option value="${r.id}">Rak: ${r.nama_rak}</option>`; });
        document.getElementById('select_rack').innerHTML = optRack;
    }
}

function addProduk() {
    const matId = document.getElementById('select_product').value;
    const qty = parseInt(document.getElementById('qty_product').value);

    if(!matId || qty < 1) { Swal.fire('Ups!', 'Pilih produk terlebih dahulu.', 'warning'); return; }

    const mat = materialsData.find(m => m.id == matId);
    if(mat) {
        const exist = printQueue.find(q => q.id == mat.id && q.type === 'product');
        if(exist) exist.qty += qty;
        else printQueue.push({ ...mat, qty: qty, type: 'product' });
        
        updatePreview();
    }
}

async function addRak() {
    const rackId = document.getElementById('select_rack').value;
    const qty = parseInt(document.getElementById('qty_rack').value);

    if(!rackId || qty < 1) { Swal.fire('Ups!', 'Pilih rak terlebih dahulu.', 'warning'); return; }

    const rack = racksData.find(r => r.id == rackId);
    if(rack) {
        const exist = printQueue.find(q => q.id == rack.id && q.type === 'rack');
        if(exist) exist.qty += qty;
        else printQueue.push({ id: rack.id, sku_code: `RAK-${rack.nama_rak}`, material_name: `Lokasi: ${rack.nama_rak}`, qty: qty, type: 'rack' });
        
        updatePreview();
    }
}

function clearQueue() {
    printQueue = [];
    updatePreview();
}

function toggleCustomLayout() {
    const isCustom = document.getElementById('set_paper').value === 'custom';
    document.getElementById('custom_layout_panel').style.display = isCustom ? 'block' : 'none';
    updatePreview();
}

function updatePreview() {
    const container = document.getElementById('preview_paper');
    
    if (printQueue.length === 0) {
        container.innerHTML = `<div class="w-full text-center py-20 text-slate-400 font-bold italic opacity-60 flex flex-col items-center gap-3"><i class="fa-solid fa-barcode text-5xl"></i><p>Preview area</p></div>`;
        return;
    }

    const set = {
        h: document.getElementById('set_height').value,
        w: document.getElementById('set_width').value,
        fmt: document.getElementById('set_format').value,
        h_text: document.getElementById('chk_hide_text').checked,
        h_name: document.getElementById('chk_hide_name').checked,
        h_price: document.getElementById('chk_hide_price').checked,
        sku_top: document.getElementById('chk_sku_top').checked,
        name_bot: document.getElementById('chk_name_bottom').checked,
        rack_text: document.getElementById('chk_show_rack_text').checked,
        rack_bc: document.getElementById('chk_show_rack_barcode').checked,
        is_custom: document.getElementById('set_paper').value === 'custom',
        c_w: document.getElementById('ly_w').value,
        c_h: document.getElementById('ly_h').value,
        c_m: document.getElementById('ly_m').value,
        c_p: document.getElementById('ly_p').value,
        paper_w: document.getElementById('ly_paper_w').value
    };

    if (set.is_custom) { container.style.width = set.paper_w + 'px'; } 
    else { container.style.width = '100%'; }

    let html = '';
    
    printQueue.forEach(item => {
        for(let i=0; i<item.qty; i++) {
            
            let nameHTML = (!set.h_name && set.name_bot) ? `<div class="text-[10px] font-bold text-center mt-1 uppercase line-clamp-2 leading-tight">${item.material_name}</div>` : '';
            let skuTopHTML = set.sku_top ? `<div class="text-[10px] font-black text-center mb-1">${item.sku_code}</div>` : '';
            let priceHTML = (!set.h_price && item.type !== 'rack') ? `<div class="text-xs font-black text-center mt-1">Rp -</div>` : '';
            
            // Render Teks Lokasi Rak
            let labelRak = item.rack_name ? `${item.rack_name}` : 'BELUM DI RAK';
            let rackTextHTML = (set.rack_text && item.type !== 'rack') ? `<div class="text-[9px] font-bold text-slate-500 mt-1 text-center bg-slate-100 px-1 rounded border border-slate-200">LOC: ${labelRak}</div>` : '';
            
            // Render Mini Barcode Rak (untuk barang)
            let rackBcHTML = '';
            if (set.rack_bc && item.type !== 'rack' && item.rack_name) {
                rackBcHTML = `<svg class="barcode-rack mt-1" jsbarcode-value="RAK-${item.rack_name}" jsbarcode-height="12" jsbarcode-width="1" jsbarcode-displayvalue="true" jsbarcode-fontSize="8" jsbarcode-margin="0"></svg>`;
            }
            
            let boxStyle = set.is_custom 
                ? `width: ${set.c_w}px; height: ${set.c_h}px; margin: ${set.c_m}px; padding: ${set.c_p}px; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed #ccc; background: #fff;` 
                : `padding: 10px; margin: 5px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; border-radius: 8px; width: max-content;`;

            html += `<div style="${boxStyle}">`;
            html += skuTopHTML;
            html += `<svg class="barcode-item" 
                          jsbarcode-value="${item.sku_code}" 
                          jsbarcode-format="${set.fmt}" 
                          jsbarcode-height="${set.h}" 
                          jsbarcode-width="${set.w}" 
                          jsbarcode-displayvalue="${!set.h_text && !set.sku_top}" 
                          jsbarcode-margin="2"
                          jsbarcode-fontSize="11"
                          jsbarcode-fontOptions="bold"></svg>`;
            
            html += nameHTML;
            html += priceHTML;
            html += rackTextHTML;
            html += rackBcHTML;
            
            html += `</div>`;
        }
    });

    container.innerHTML = html;

    JsBarcode(".barcode-item").init();
    if(set.rack_bc) JsBarcode(".barcode-rack").init();
}

function prosesCetak() {
    if (printQueue.length === 0) { Swal.fire('Ups!', 'Belum ada antrean cetak.', 'warning'); return; }

    const settings = {
        h: document.getElementById('set_height').value,
        w: document.getElementById('set_width').value,
        fmt: document.getElementById('set_format').value,
        h_text: document.getElementById('chk_hide_text').checked,
        h_name: document.getElementById('chk_hide_name').checked,
        h_price: document.getElementById('chk_hide_price').checked,
        sku_top: document.getElementById('chk_sku_top').checked,
        name_bot: document.getElementById('chk_name_bottom').checked,
        rack_text: document.getElementById('chk_show_rack_text').checked,
        rack_bc: document.getElementById('chk_show_rack_barcode').checked,
        is_custom: document.getElementById('set_paper').value === 'custom',
        c_w: document.getElementById('ly_w').value,
        c_h: document.getElementById('ly_h').value,
        c_m: document.getElementById('ly_m').value,
        c_p: document.getElementById('ly_p').value,
        paper_w: document.getElementById('ly_paper_w').value
    };

    localStorage.setItem('barcodeQueue', JSON.stringify(printQueue));
    localStorage.setItem('barcodeSettings', JSON.stringify(settings));

    window.open('print.php', '_blank');
}