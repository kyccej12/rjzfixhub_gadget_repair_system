
<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include("handlers/initDB.php");
$con = new myDB;

$term = isset($_GET['term']) ? trim(strip_tags($_GET['term'])) : '';
$cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';

$my_arr = array();

if ($term !== '') {
    $termSQL = addslashes($term);

    $r = $con->dbquery("
        SELECT
            item_code,
            description,
            brand,
            srp,
            unit
        FROM products_master
        WHERE (
            LOCATE('$termSQL', description) > 0
            OR LOCATE('$termSQL', item_code) > 0
            OR LOCATE('$termSQL', brand) > 0
        )
        AND file_status = 'Active'
        LIMIT 30
    ");

    if ($r) {
        while ($row = $r->fetch_array()) {
            $itemCodeSQL = addslashes($row['item_code']);
            $branchSQL = isset($_SESSION['branchid'])
                ? addslashes($_SESSION['branchid'])
                : '';

            // Physical inventory balance
            $pi = $con->getArray("
                SELECT IFNULL(SUM(b.qty), 0) AS physical_qty
                FROM phy_header a
                LEFT JOIN phy_details b
                    ON a.doc_no = b.doc_no
                    AND a.branch = b.branch
                WHERE a.branch = '$branchSQL'
                    AND b.item_code = '$itemCodeSQL'
                    AND a.status = 'Finalized'
                    AND a.posting_date = '2026-10-11'
            ");

            // Inventory movement balance
            $cur = $con->getArray("
                SELECT IFNULL(
                    SUM(purchases + inbound - outbound - pullouts - sold),
                    0
                ) AS currentbalance
                FROM ibook
                WHERE item_code = '$itemCodeSQL'
                    AND doc_date BETWEEN '2026-10-11'
                    AND '" . date('Y-m-d') . "'
                    AND doc_branch = '$branchSQL'
            ");

            $physicalQty = isset($pi['physical_qty'])
                ? (float)$pi['physical_qty']
                : 0;

            $currentBalance = isset($cur['currentbalance'])
                ? (float)$cur['currentbalance']
                : 0;

            $qoh = round($physicalQty + $currentBalance, 2);

            $brand = isset($row['brand']) ? $row['brand'] : '';
            $description = isset($row['description'])
                ? $row['description']
                : '';

            $my_arr[] = array(
                'label'       => $brand . ' - ' . $description
                                 . ' [Available: ' . $qoh . ']',
                'value'       => $brand . ' - ' . $description,
                'item_code'   => $row['item_code'],
                'description' => $description,
                'brand'       => $brand,
                'model'       => isset($row['model']) ? $row['model'] : '',
                'unit'        => isset($row['unit']) ? $row['unit'] : '',
                'price'       => isset($row['srp']) ? (float)$row['srp'] : 0,
                'remaining'   => $qoh
            );
        }
    }
}

echo json_encode($my_arr);
exit;
?>