<?php

function add_uom_conversion($stock_id, $base_uom, $alt_uom, $conversion_rate) {
    $sql = "INSERT INTO item_uom_conversion (stock_id, base_uom, alt_uom, conversion_rate)
            VALUES (" . db_escape($stock_id) . ", " . db_escape($base_uom) . ", "
            . db_escape($alt_uom) . ", " . db_escape($conversion_rate) . ")";
    db_query($sql, "Gagal menyimpan konversi UOM");
}

function get_uom_conversions() {
    $sql = "SELECT * FROM item_uom_conversion ORDER BY stock_id, alt_uom";
    return db_query($sql, "Gagal mengambil konversi UOM");
}

function get_uom_conversion_by_id($id)
{
    $sql = "SELECT * FROM item_uom_conversion WHERE id = " . db_escape($id);
    return db_fetch_assoc(db_query($sql, "Gagal mengambil data konversi UOM berdasarkan ID"));
}

function delete_uom_conversion($id)
{
    $sql = "DELETE FROM item_uom_conversion WHERE id = " . db_escape($id);
    db_query($sql, "Gagal menghapus konversi UOM");
}

function update_uom_conversion($id, $stock_id, $base_uom, $alt_uom, $conversion_rate)
{
    $sql = "UPDATE item_uom_conversion SET 
                stock_id = " . db_escape($stock_id) . ",
                base_uom = " . db_escape($base_uom) . ",
                alt_uom = " . db_escape($alt_uom) . ",
                conversion_rate = " . db_escape($conversion_rate) . "
            WHERE id = " . db_escape($id);
    db_query($sql, "Gagal memperbarui konversi UOM");
}
