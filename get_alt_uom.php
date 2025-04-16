<?php
define('FA_SESSION_PATH', true);
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/modules/multi_uom/multi_uom_db.php");

// Set header untuk JSON
header('Content-Type: application/json');

if (!isset($_GET['stock_id']) || empty($_GET['stock_id'])) {
    echo json_encode([]);
    exit;
}

$stock_id = $_GET['stock_id'];

// Ambil daftar konversi untuk item ini
$result = get_alternative_uoms_for_item($stock_id);

$data = [];

while ($row = db_fetch_assoc($result)) {
    $data[] = [
        'alt_uom' => $row['alt_uom'],
        'conversion_rate' => $row['conversion_rate'],
    ];
}

echo json_encode($data);
exit;
