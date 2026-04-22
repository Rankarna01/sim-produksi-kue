<?php
require_once '../../../config/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mencetak Barcode...</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background-color: #fff; font-family: Arial, sans-serif; }
        
        #print_container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-content: flex-start;
        }
        
        .sticker-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            background: #fff;
            page-break-inside: avoid;
        }

        .product-name { font-size: 10px; font-weight: bold; text-transform: uppercase; line-height: 1.1; margin-top: 2px;}
        .sku-top { font-size: 10px; font-weight: 900; margin-bottom: 2px; }
        .price-tag { font-size: 11px; font-weight: 900; margin-top: 2px; }
        .rack-info { font-size: 8px; color: #444; margin-top: 2px; }

        @media print {
            @page { margin: 0; }
            body { margin: 0; }
            /* Hilangkan border dashed saat dicetak sungguhan agar bersih */
            .sticker-box { border: none !important; } 
        }
    </style>
</head>
<body>

    <div id="print_container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const queueStr = localStorage.getItem('barcodeQueue');
            const setStr = localStorage.getItem('barcodeSettings');

            if (!queueStr || !setStr) {
                document.body.innerHTML = "<h2 style='text-align:center; margin-top:50px;'>Tidak ada data untuk dicetak.</h2>"; return;
            }

            const queue = JSON.parse(queueStr);
            const set = JSON.parse(setStr);
            const container = document.getElementById('print_container');
            
            if (set.is_custom) { container.style.width = set.paper_w + 'px'; } 
            else { container.style.width = '100%'; }

            let html = '';

            queue.forEach(item => {
                for(let i=0; i<item.qty; i++) {
                    let nameHTML = (!set.h_name && set.name_bot) ? `<div class="product-name">${item.material_name}</div>` : '';
                    let skuTopHTML = set.sku_top ? `<div class="sku-top">${item.sku_code}</div>` : '';
                    let priceHTML = (!set.h_price && item.type !== 'rack') ? `<div class="price-tag">Rp -</div>` : '';
                    
                    let rackTextHTML = (set.rack_text && item.type !== 'rack') ? `<div class="rack-info">Rak: ${item.rack_name || '-'}</div>` : '';
                    let rackBcHTML = (set.rack_bc && item.type !== 'rack' && item.rack_name) ? `<svg class="barcode-rack" jsbarcode-value="RACK-${item.rack_id}" jsbarcode-height="15" jsbarcode-width="1" jsbarcode-displayvalue="false" jsbarcode-margin="0"></svg>` : '';
                    
                    let boxStyle = set.is_custom 
                        ? `width: ${set.c_w}px; height: ${set.c_h}px; margin: ${set.c_m}px; padding: ${set.c_p}px;` 
                        : `padding: 10px; margin: 5px; border: 1px dashed #ccc; border-radius: 8px; width: max-content;`;

                    html += `<div class="sticker-box" style="${boxStyle}">
                                ${skuTopHTML}
                                <svg class="barcode-render" 
                                    jsbarcode-value="${item.sku_code}" 
                                    jsbarcode-format="${set.fmt}" 
                                    jsbarcode-height="${set.h}" 
                                    jsbarcode-width="${set.w}" 
                                    jsbarcode-displayvalue="${!set.h_text && !set.sku_top}" 
                                    jsbarcode-margin="2"
                                    jsbarcode-fontSize="11"
                                    jsbarcode-fontOptions="bold"></svg>
                                ${nameHTML}
                                ${priceHTML}
                                ${rackTextHTML}
                                ${rackBcHTML ? `<div style="margin-top:2px;">${rackBcHTML}</div>` : ''}
                             </div>`;
                }
            });

            container.innerHTML = html;
            JsBarcode(".barcode-render").init();
            if(set.rack_bc) JsBarcode(".barcode-rack").init();

            setTimeout(() => { window.print(); }, 800);
        });
    </script>
</body>
</html>