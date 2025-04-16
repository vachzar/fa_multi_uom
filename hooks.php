<?php
// Gunakan nilai yang tidak bentrok, contoh SS_ORDERS biasanya 101<<8, kita ambil yang aman
define('SS_MULTI_UOM', 150 << 8);

class hooks_multi_uom extends hooks
{
    public $module_name = 'Multi UOM Support';

    function install_options($app)
    {
        global $path_to_root;

        if ($app->id == 'stock') {
            $app->add_rapp_function(
                2,
                _('Multi UOMs'),
                $path_to_root . '/modules/multi_uom/uom_ui.php',
                'SA_MULTI_UOM',
                MENU_TRANSACTION
            );
        }

        $this->inject_sales_js(); // inject JS saat modul dimuat
    }

    function inject_sales_js()
    {
        echo "
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script>
    $(document).ready(function () {
        // Pasang event change pada select dengan name 'stock_id'
        $('select[name=\"stock_id\"]').on('change', function () {
            var stockId = $(this).val();
            var target = $('#units');
            if (!stockId || target.length === 0) return;
            
            // Gunakan URL yang benar: ../modules/multi_uom/get_alt_uom.php?stock_id=...
            $.getJSON('../modules/multi_uom/get_alt_uom.php?stock_id=' + encodeURIComponent(stockId), function(data) {
                // Buat dropdown baru untuk satuan alternatif
                var select = $('<select class=\"multi-uom-select\" name=\"alt_uom\"><option value=\"\">Pilih</option></select>');
                $.each(data, function(index, item) {
                    select.append('<option value=\"' + item.alt_uom + '\" data-rate=\"' + item.conversion_rate + '\">' + item.alt_uom + ' (' + item.conversion_rate + ')</option>');
                });
                // Ganti isi dari elemen dengan id 'units'
                target.empty().append(select);
                
                // Bind event change pada dropdown yang baru dibuat agar mengupdate quantity jika diperlukan
                select.on('change', function () {
                    var rate = parseFloat($(this).find(':selected').data('rate'));
                    // Misalnya, field quantity memiliki name 'quantity'; sesuaikan jika perlu:
                    var qty_input = $('input[name=\"quantity\"]');
                    var current_qty = parseFloat(qty_input.val()) || 0;
                    if (!isNaN(rate)) {
                        qty_input.val((current_qty * rate).toFixed(2));
                    }
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Gagal ambil satuan alternatif: ' + textStatus + ', ' + errorThrown);
            });
        });
    });
    </script>
    ";
    }


    function install_access()
    {
        // definisi security section dan area
        $security_sections[SS_MULTI_UOM] = _("Multi UOM Support");
        $security_areas['SA_MULTI_UOM'] = array(SS_MULTI_UOM | 1, _("Manage Multi UOMs"));

        return array($security_areas, $security_sections);
    }

    function update_app_menu(&$app)
    {
        $app->add_module(_("Inventory"), "multi_uom", _("Multi UOM"), "/modules/multi_uom/multi_uom_ui.php", 'SA_MULTI_UOM');
    }

    function render_headers($app)
    {
        $this->inject_sales_js();
        $page = $_SERVER['PHP_SELF'];
        if (strpos($page, 'sales_order_entry.php') !== false || strpos($page, 'customer_invoice.php') !== false) {
            echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fungsi ini akan menggantikan elemen <td id="units"> menjadi <select>
    function updateUnitSelect(row, stockId) {
        fetch('modules/multi_uom/get_alt_uom.php?stock_id=' + encodeURIComponent(stockId))
            .then(response => response.json())
            .then(data => {
                const tdUnits = row.querySelector("td[id^='units']");
                if (!tdUnits) return;

                const select = document.createElement('select');
                select.name = 'alt_uom[]';
                select.classList.add('alt-uom-select');

                const currentText = tdUnits.textContent.trim();
                const baseOption = document.createElement('option');
                baseOption.value = currentText;
                baseOption.textContent = currentText;
                select.appendChild(baseOption);

                data.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.alt_uom;
                    option.textContent = opt.alt_uom + " (" + opt.conversion_rate + ")";
                    option.dataset.rate = opt.conversion_rate;
                    select.appendChild(option);
                });

                select.addEventListener('change', function () {
                    const selectedRate = parseFloat(this.selectedOptions[0].dataset.rate || 1);
                    const qtyInput = row.querySelector("input[name^='qty']");
                    if (qtyInput && !isNaN(selectedRate)) {
                        qtyInput.value = (parseFloat(qtyInput.value) * selectedRate).toFixed(2);
                    }
                });

                tdUnits.innerHTML = '';
                tdUnits.appendChild(select);
            })
            .catch(err => {
                console.error("Gagal load UOM alternatif:", err);
            });
    }

    document.querySelectorAll("select[name^='stock_id']").forEach(select => {
        select.addEventListener('change', function () {
            const row = this.closest('tr');
            const stockId = this.value;
            if (!row || !stockId) return;
            updateUnitSelect(row, stockId);
        });

        // Trigger langsung saat load awal
        const row = select.closest('tr');
        if (row) updateUnitSelect(row, select.value);
    });
});
</script>
EOT;
        }
    }
}
