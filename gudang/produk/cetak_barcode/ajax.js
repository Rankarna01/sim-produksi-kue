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
        Swal.fire({title: 'Tersimpan', text: 'Produk ditambahkan ke preview.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
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
        Swal.fire({title: 'Tersimpan', text: 'Rak ditambahkan ke preview.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
    }
}

function clearQueue() {
    printQueue = [];
    updatePreview();
}

// Fitur untuk Mengurangi Barcode dari Preview
window.kurangiAntrean = function(index) {
    if(printQueue[index]) {
        if(printQueue[index].qty > 1) {
            printQueue[index].qty -= 1;
        } else {
            printQueue.splice(index, 1);
        }
        updatePreview();
    }
}

function toggleCustomLayout() {
    const paperType = document.getElementById('set_paper').value;
    document.getElementById('custom_layout_panel').style.display = (paperType === 'custom') ? 'block' : 'none';
    updatePreview();
}

function updatePreview() {
    const container = document.getElementById('preview_paper');
    
    if (printQueue.length === 0) {
        container.innerHTML = `<div class="w-full text-center py-20 text-slate-400 font-bold italic opacity-60 flex flex-col items-center gap-3"><i class="fa-solid fa-barcode text-5xl"></i><p>Area Preview. Klik "Simpan ke Preview" dari produk/rak untuk memunculkan barcode.</p></div>`;
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
        paper: document.getElementById('set_paper').value,
        is_custom: document.getElementById('set_paper').value === 'custom',
        c_w: document.getElementById('ly_w').value,
        c_h: document.getElementById('ly_h').value,
        c_m: document.getElementById('ly_m').value,
        c_p: document.getElementById('ly_p').value,
        paper_w: document.getElementById('ly_paper_w').value
    };

    if (set.is_custom) { 
        container.style.width = set.paper_w + 'px'; 
    } else { 
        container.style.width = '100%'; 
    }

    let html = '';
    
    printQueue.forEach((item, index) => {
        for(let i=0; i<item.qty; i++) {
            
            let nameHTML = (!set.h_name && set.name_bot) ? `<div class="text-[10px] font-bold text-center mt-1 uppercase line-clamp-2 leading-tight">${item.material_name}</div>` : '';
            let skuTopHTML = set.sku_top ? `<div class="text-[10px] font-black text-center mb-1">${item.sku_code}</div>` : '';
            let priceHTML = (!set.h_price && item.type !== 'rack') ? `<div class="text-xs font-black text-center mt-1">Rp -</div>` : '';
            
            let labelRak = item.rack_name ? `${item.rack_name}` : 'BELUM DI RAK';
            let rackTextHTML = (set.rack_text && item.type !== 'rack') ? `<div class="text-[9px] font-bold text-slate-500 mt-1 text-center bg-slate-100 px-1 rounded border border-slate-200">LOC: ${labelRak}</div>` : '';
            
            let rackBcHTML = '';
            if (set.rack_bc && item.type !== 'rack' && item.rack_name) {
                rackBcHTML = `<svg class="barcode-rack mt-1" jsbarcode-value="RAK-${item.rack_name}" jsbarcode-height="12" jsbarcode-width="1" jsbarcode-displayvalue="true" jsbarcode-fontSize="8" jsbarcode-margin="0"></svg>`;
            }
            
            // Logika Styling Kertas Tom & Jerry vs Custom vs Auto Fit
            let boxStyle = '';
            if (set.is_custom) {
                boxStyle = `width: ${set.c_w}px; height: ${set.c_h}px; margin: ${set.c_m}px; padding: ${set.c_p}px; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed #ccc; background: #fff;`;
            } else if (set.paper === 'tj107') {
                boxStyle = `width: 50mm; height: 18mm; margin: 1mm; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed #ccc; background: #fff; padding: 1mm; border-radius: 4px;`;
            } else if (set.paper === 'tj108') {
                boxStyle = `width: 38mm; height: 18mm; margin: 1mm; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px dashed #ccc; background: #fff; padding: 1mm; border-radius: 4px;`;
            } else { // Auto Fit
                boxStyle = `padding: 10px; margin: 5px; border: 1px dashed #ccc; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; border-radius: 8px; width: max-content;`;
            }

            // Wrapper dengan Group Hover dan Tombol Silang (X)
            html += `<div style="${boxStyle} position: relative;" class="group">`;
            html += `<button onclick="kurangiAntrean(${index})" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 transition-opacity shadow-md z-10 cursor-pointer" title="Hapus/Kurangi 1 Item"><i class="fa-solid fa-times"></i></button>`;
            
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

    // Coba render barcode, jika format tidak disupport JsBarcode otomatis akan menggunakan CODE128
    try {
        JsBarcode(".barcode-item").init();
        if(set.rack_bc) JsBarcode(".barcode-rack").init();
    } catch(err) {
        console.warn("JsBarcode Format Error, Fallback to CODE128", err);
        // Fallback Force CODE128
        document.querySelectorAll(".barcode-item").forEach(el => {
            JsBarcode(el, el.getAttribute("jsbarcode-value"), { format: "CODE128", displayValue: el.getAttribute("jsbarcode-displayvalue") === "true", height: set.h, width: set.w });
        });
    }
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
        paper: document.getElementById('set_paper').value,
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