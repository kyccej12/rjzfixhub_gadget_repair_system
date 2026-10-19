<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

include("handlers/initDB.php");
$con = new myDB;

$term = isset($_GET['term'])
    ? trim(strip_tags($_GET['term']))
    : '';

$my_arr = array();

if ($term !== '') {

    $termSQL = addslashes($term);

    $sql = "
        SELECT
            id,
            item_code,
            short_description,
            unit_price
        FROM services_master
        WHERE (
            LOCATE('$termSQL', short_description) > 0
            OR LOCATE('$termSQL', item_code) > 0
        )
        AND file_status = 'Active'
        LIMIT 30
    ";

   $r = $con->dbquery("
        SELECT
            id,
            item_code,
            short_description,
            unit_price
        FROM services_master
        WHERE (
            LOCATE('$termSQL', short_description) > 0
            OR LOCATE('$termSQL', item_code) > 0
        )
        AND file_status = 'Active'
        LIMIT 30
    ");

    if ($r) {
        while ($row = $r->fetch_array()) {
            $code = $row['item_code'];
            $description = $row['short_description'];
            $amount = isset($row['unit_price'])
                ? (float)$row['unit_price']
                : 0;

            $my_arr[] = array(
                'label'       => $code . ' - ' . $description
                                . ' [' . number_format($amount, 2) . ']',
                'value'       => $description,
                'id'          => $row['id'],
                'code'        => $code,
                'description' => $description,
                'amount'      => $amount
            );
        }
    }
}

echo json_encode($my_arr);
exit;
?>