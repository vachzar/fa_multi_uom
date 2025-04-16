<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_security = 'SA_MULTI_UOM';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/modules/multi_uom/multi_uom_db.php");

$selected_id = get_post('selected_id');

if (isset($_POST['submit'])) {
    if (!is_numeric($_POST['conversion_rate'])) {
        display_error("Conversion rate harus berupa angka.");
    } elseif (
        !empty($_POST['stock_id']) && !empty($_POST['base_uom']) &&
        !empty($_POST['alt_uom']) && !empty($_POST['conversion_rate'])
    ) {
        if (!empty($_POST['selected_id'])) {
            update_uom_conversion($_POST['selected_id'], $_POST['stock_id'], $_POST['base_uom'], $_POST['alt_uom'], $_POST['conversion_rate']);
            display_notification("Data berhasil diperbarui.");
        } else {
            add_uom_conversion($_POST['stock_id'], $_POST['base_uom'], $_POST['alt_uom'], $_POST['conversion_rate']);
            display_notification("Data berhasil disimpan.");
        }
        $_POST['selected_id'] = '';
        $selected_id = '';
        unset($_POST['stock_id'], $_POST['base_uom'], $_POST['alt_uom'], $_POST['conversion_rate']);
    } else {
        display_error("Semua kolom harus diisi.");
    }
}

if (isset($_GET['edit'])) {
    $selected_id = $_GET['edit'];
    $conversion = get_uom_conversion_by_id($selected_id);
    if ($conversion) {
        $_POST['stock_id'] = $conversion['stock_id'];
        $_POST['base_uom'] = $conversion['base_uom'];
        $_POST['alt_uom'] = $conversion['alt_uom'];
        $_POST['conversion_rate'] = $conversion['conversion_rate'];
        $_POST['selected_id'] = $selected_id;
    }
}

if (isset($_POST['confirm_delete'])) {
    delete_uom_conversion($_POST['delete_id']);
    display_notification("Data berhasil dihapus.");
}

page(_($help_context = "Multi UOMs"));

start_form();

$sql_items = "SELECT stock_id, description FROM stock_master ORDER BY stock_id";
$res_items = db_query($sql_items, "Gagal ambil daftar item");

$items = array();
while ($row = db_fetch($res_items)) {
    $items[$row['stock_id']] = $row['stock_id'] . " - " . $row['description'];
}

$sql_uoms = "SELECT abbr, name FROM item_units ORDER BY abbr";
$res_uoms = db_query($sql_uoms, "Gagal ambil daftar satuan");

$uoms = array();
while ($row = db_fetch($res_uoms)) {
    $uoms[$row['abbr']] = $row['name'] . " (" . $row['abbr'] . ")";
}

start_table(TABLESTYLE2);

array_selector_row(_("Item:"), 'stock_id', @$_POST['stock_id'], $items);
array_selector_row(_("Base Unit:"), 'base_uom', @$_POST['base_uom'], $uoms);
array_selector_row(_("Alternative Unit:"), 'alt_uom', @$_POST['alt_uom'], $uoms);
text_row(_("Conversion Rate (Alt → Base):"), 'conversion_rate', @$_POST['conversion_rate'], 10, 10);

hidden('selected_id', $selected_id);

end_table(1);

submit_center('submit', $selected_id ? _("Update") : _("Save"));
end_form();

// Konfirmasi penghapusan
if (isset($_GET['delete']) && !isset($_POST['confirm_delete'])) {
    display_warning("Apakah Anda yakin ingin menghapus data ini?");
    start_form(true);
    hidden('delete_id', $_GET['delete']);
    submit_center('confirm_delete', _('Konfirmasi Hapus'), true);
    end_form();
}

echo '<br><br>';
display_heading(_("Daftar Konversi Satuan"));

$result = get_uom_conversions();

start_table(TABLESTYLE);
$th = array(_("Item"), _("Base UOM"), _("Alt UOM"), _("Rate"), '', '');
table_header($th);

while ($row = db_fetch_assoc($result)) {
    start_row();
    label_cell($row['stock_id']);
    label_cell($row['base_uom']);
    label_cell($row['alt_uom']);
    label_cell($row['conversion_rate']);
    label_cell("<a href='?edit={$row['id']}'>" . _('Edit') . "</a>");
    label_cell("<a href='?delete={$row['id']}'>" . _('Delete') . "</a>");
    end_row();
}
end_table();

end_page();
