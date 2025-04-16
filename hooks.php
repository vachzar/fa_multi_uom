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
    }

    function install_access()
    {
        // definisi security section dan area
        $security_sections[SS_MULTI_UOM] = _("Multi UOM Support");
        $security_areas['SA_MULTI_UOM'] = array(SS_MULTI_UOM | 1, _("Manage Multi UOMs"));

        return array($security_areas, $security_sections);
    }
}
