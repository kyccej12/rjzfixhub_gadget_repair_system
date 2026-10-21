<?php
/*
 * Designed for mPDF 6.x and the existing _init / getArray() pattern.
 *
 * IMPORTANT:
 * 1. Update the mPDF include path below to match your installation.
 * 2. This file assumes _init has getArray($sql) for one row and getData($sql)
 *    for multiple rows. If your _generics.php uses a different multi-row method,
 *    replace the single getData() call in the "Parts / services" section.
 * 3. Replace the sample business contact details and terms with your actual policy.
 */

session_start();
require_once("../handlers/session.guard.php");

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include("../lib/mpdf6/mpdf.php");
include("../handlers/_generics.php");
$mydb = new _init();
$co = $mydb->getArray("select * from options_branches where company = '$_SESSION[company]';");

if (empty($_SESSION['branchid'])) {
    die("Your session has expired. Please log in again.");
}

$branch = $_SESSION['branchid'];
$joNoInput = isset($_GET['jo_no']) ? trim($_GET['jo_no']) : '';

if ($joNoInput === '' || !ctype_digit($joNoInput)) {
    die("Invalid Job Order number.");
}

/* Fetch the Job Order header. */
$joNoSql = addslashes($joNoInput);
$branchSql = addslashes((string)$branch);

$header = $mydb->getArray("
    SELECT *
    FROM jo_header
    WHERE jo_no = '{$joNoSql}'
      AND branch = '{$branchSql}'
    LIMIT 1
");

list($deviceType) = $mydb->getArray("SELECT a.category FROM options_devicetype a LEFT JOIN jo_header b ON a.device_id = b.device_type WHERE b.jo_no = '{$joNoSql}' AND b.branch = '{$branchSql}';");

$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-JO".$header['jo_no']."-".date('Ymd');


if (empty($header) || empty($header['jo_no'])) {
    die("Job Order not found.");
}

/* Fetch parts/items using the trace number stored in the header. */
$traceNo = isset($header['trace_no']) ? addslashes($header['trace_no']) : '';

/* Initialize an empty array to hold the parts/items. */
$items = array();

if ($traceNo !== '') {
    $safeTraceNo = myDB::escapeString($traceNo);

    $sql = "
        SELECT *
        FROM jo_details
        WHERE trace_no = '{$safeTraceNo}' and branch = '{$branchSql}' and jo_no = '{$joNoSql}'
    ";

    $result = myDB::dbquery($sql);

    if ($result === false) {
        die("Error retrieving JO details: " . mysqli_error(
            myConnector::getInstance()->getConnection()
        ));
    }

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $result->free();
}
/* END: Fetch parts/items using the trace number stored in the header. */

$service = array();

if ($traceNo !== '') {
    $safeTraceNoservice = myDB::escapeString($traceNo);



    $sql = "
        SELECT *
        FROM jo_services
        WHERE trace_no = '{$safeTraceNoservice}' and branch = '{$branchSql}' and jo_no = '{$joNoSql}'
    ";

    $resultService = myDB::dbquery($sql);

    if ($resultService === false) {
        die("Error retrieving JO service: " . mysqli_error(
            myConnector::getInstance()->getConnection()
        ));
    }

    while ($row = $resultService->fetch_assoc()) {
        $service[] = $row;
    }

    $resultService->free();
}

function jo_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function jo_money($value) {
    return number_format((float)$value, 2);
}
function jo_field($row, $key, $default = '') {
    return isset($row[$key]) && $row[$key] !== '' ? $row[$key] : $default;
}

$joNo = jo_field($header, 'jo_no', $joNoInput);
$joDateRaw = jo_field($header, 'jo_date', date('Y-m-d'));
$joDate = jo_field($header, 'jo_date', '');
$deviceCat = jo_field($header, 'device_type', '');
$joDate = strtotime($joDateRaw) ? date('M d, Y', strtotime($joDateRaw)) : $joDateRaw;
$customerName = jo_field($header, 'customer_name', 'Walk-in Customer');
$customerCode = str_pad(jo_field($header, 'customer_code', ''), 6, '0', STR_PAD_LEFT);
$customerContact = jo_field($header, 'customer_contactno', '');
$customerAddress = jo_field($header, 'customer_address', '');
$customerEmail = jo_field($header, 'customer_email', '');
$unitName = jo_field($header, 'unit_name', '');
$unitCategory = jo_field($header, 'unit_category', '');
$unitModel = jo_field($header, 'unit_model', '');
$unitPurchaseDate = jo_field($header, 'unit_pdate', '');
$unitWarranty = jo_field($header, 'unit_warranty', '');
$unitLcd = jo_field($header, 'unit_lcd', '');
$unitBattery = jo_field($header, 'unit_battery', '');
$unitStorage = jo_field($header, 'unit_storage', '');
$unitCharger = jo_field($header, 'unit_charger', '');
$unitProcessor = jo_field($header, 'unit_processor', '');
$unitRam = jo_field($header, 'unit_ram', '');
$unitMotherboard = jo_field($header, 'unit_motherboard', '');
$remarks = jo_field($header, 'remarks', '');
$notes = jo_field($header, 'notes', '');
$action_taken = jo_field($header, 'action_taken', '');
$status = jo_field($header, 'status', 'Active');

$joYear = ($joDate != '') ? date('Y', strtotime($joDate)) : '';
$joNo = str_pad($joNo, 6, '0', STR_PAD_LEFT);

$joReference = 'RJZ-' . $joYear . '-' . $joNo;

$visualFields = array(
    'LCD / LED' => 'visual_lcd',
    'USB Port' => 'visual_usb',
    'BitLocker' => 'visual_bitlocker',
    'Charger' => 'visual_charger',
    'HDD / SSD' => 'visual_storage',
    'Screws' => 'visual_screws',
    'Camera' => 'visual_camera',
    'Touchpad' => 'visual_touchpad',
    'Charging' => 'visual_charging',
    'Speaker' => 'visual_speaker',
    'HDMI / VGA' => 'visual_hdmi',
    'Keyboard' => 'visual_keyboard',
    'BIOS Password' => 'visual_bios',
    'Battery' => 'visual_battery',
    'RAM' => 'visual_ram',
    'Wi-Fi' => 'visual_wifi'
);

/* Totals stored on jo_header, with calculated fallback for item rows. */
$partsTotal = (float)jo_field($header, 'pamount', 0);
$laborTotal = (float)jo_field($header, 'labor', 0);
$grandTotal = (float)jo_field($header, 'amount', 0);

if ($grandTotal <= 0) {
    $grandTotal = $partsTotal + $laborTotal;
}

/* Payment history for this Job Order */
$paymentRows = array();
$paymentTotal = 0;

if ($traceNo !== '') {
    $safePaymentTraceNo = myDB::escapeString($traceNo);

    $paymentSql = "
        SELECT
            DATE_FORMAT(date_created, '%m/%d/%Y') AS payment_date,
            payment_type,
            payment_method,
            amount
        FROM jo_payments
        WHERE jo_no = '{$joNoSql}'
          AND branch = '{$branchSql}'
          AND trace_no = '{$safePaymentTraceNo}'
        ORDER BY date_created ASC, payment_id ASC
    ";

    $paymentResult = myDB::dbquery($paymentSql);

    if ($paymentResult !== false) {
        while ($payment = $paymentResult->fetch_assoc()) {
            $paymentRows[] = $payment;
            $paymentTotal += (float)$payment['amount'];
        }

        $paymentResult->free();
    }
}

$mpdf = new mPDF('utf-8', 'FOLIO-P', 0, 'dejavusans', 6, 6, 6, 6, 6, 6);
$mpdf->SetTitle('Job Order ' . $joNo);
$mpdf->SetAuthor('RJZ Fix Hub');
$mpdf->SetDisplayMode('fullpage');

$html = '
<style>

    body {
        font-family: dejavusans, sans-serif;
        font-size: 6.5pt;
        color: #243247;
        margin: 0;
        padding: 0;
    }

    /* Header */
    .brand {
        font-size: 17pt;
        font-weight: bold;
        color: #12304a;
        letter-spacing: .2px;
    }

    .tagline {
        font-size: 7pt;
        color: #66788a;
    }

    .document-title {
        font-size: 14pt;
        font-weight: bold;
        color: #12304a;
        text-align: right;
    }

    .doc-subtitle {
        font-size: 6pt;
        color: #64748b;
        text-align: right;
        margin-right: -12px;
        margin-bottom: 2px;
    }

    .topline {
        border-bottom: 2px solid #17a2a4;
    }

    /* Section headings */
    .section {
        background: #eaf2f7;
        color: #12304a;
        font-weight: bold;
        font-size: 6.5pt;
        padding: 3px 3px;
        border-left: 2px solid #17a2a4;
    }

    /* Panels and information */
    .panel {
        border: 1px solid #d7e0e8;
        padding: 4px;
    }

    .label {
        color: #64748b;
        font-size: 6.5pt;
    }

    .value {
        font-size: 7pt;
        font-weight: bold;
        color: #243247;
    }

    .muted {
        color: #64748b;
    }

    /* General table sizing */
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    td, th {
        vertical-align: top;
        overflow-wrap: break-word;
    }

    /* Parts / items table */
    .items {
        width: 100%;
        table-layout: fixed;
    }

    .items th {
        background: #12304a;
        color: #fff;
        font-size: 7pt;
        padding: 4px 3px;
        border: 1px solid #12304a;
    }

    .items td {
        border: 1px solid #d7e0e8;
        padding: 4px 3px;
        font-size: 7pt;
    }

    .items tr:nth-child(even) td {
        background: #f7fafc;
    }

    /* Totals */
    .total-label {
        text-align: right;
        padding: 3px;
        color: #526477;
        font-size: 7pt;
    }

    .total-value {
        text-align: right;
        padding: 3px;
        font-size: 7pt;
        font-weight: bold;
    }

    .grand-label {
        background: #eaf2f7;
        font-weight: bold;
        padding: 5px;
        font-size: 8pt;
    }

    .grand-value {
        background: #eaf2f7;
        color: #12304a;
        font-size: 11pt;
        font-weight: bold;
        text-align: right;
        padding: 5px;
    }

    /* Visual inspection */
    .check-grid {
        width: 100%;
        table-layout: fixed;
    }

    .check-grid td {
        border: 1px solid #d7e0e8;
        padding: 3px;
    }

    .check-label {
        color: #526477;
        font-size: 7pt;
    }

    .check-value {
        font-weight: bold;
        text-align: right;
        font-size: 7pt;
    }

    /* Terms and signatures */
    .terms {
        font-size: 6.5pt;
        line-height: 1.2;
        color: #425466;
    }

    .signature {
        border-top: 1px solid #526477;
        text-align: center;
        padding-top: 3px;
        font-size: 7pt;
    }

    .small {
        font-size: 6.5pt;
        color: #64748b;
    }

    img {
        max-width: 100%;
        height: auto;
    }
</style>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td width="58%" style="padding-top:20px; vertical-align:top;">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <tr>
                <td width="65" valign="middle" style="padding:0 0 0 0;">
                    <img
                        src="../images/'.$co['headerlogo'].'"
                        width="60"
                        height="60"
                        style="display:block;"
                    />
                </td>
                <td valign="middle" style="padding:0;">
                    <div class="brand" style="font-size:17pt; line-height:1.1;">
                        RJZ FIX HUB
                    </div>
                    <div class="tagline">
                        Gadget • Laptop • Desktop Repair Services
                    </div>
                    <div class="small" style="padding-top:4px;">
                        Branch: '.$co['branch_name'].' &nbsp; <br />
                        Contact: '.$co['contact_no'].' &nbsp; <br />
                        Address: '.$co['address'].'
                    </div>
                </td>
            </tr>
        </table>

        
    </td>
    <td width="42%" style="padding-bottom:10px;" align="right">
        <div class="document-title">JOB ORDER</div>
        <barcode class="doc-subtitle" size=0.8 code="'.$bcode.'" type="C128A">
        <br />
        <table width="100%" cellpadding="4" cellspacing="0">
            <tr><td width="40%" style="background:#eaf2f7;"><b>JO No.</b></td><td width="55%" style="border:1px solid #d7e0e8;"><b>' . $joReference . '</b></td></tr>
            <tr><td style="background:#eaf2f7;"><b>Date</b></td><td style="border:1px solid #d7e0e8;">' . jo_h($joDate) . '</td></tr>
            <tr><td style="background:#eaf2f7;"><b>Status</b></td><td style="border:1px solid #d7e0e8;">' . jo_h($status) . '</td></tr>
        </table>
    </td>
</tr>
</table>
<div class="topline"></div>
<div style="margin-bottom:3px;"></div>
<div class="section">CUSTOMER DETAILS</div>
<table width="100%" cellpadding="7" cellspacing="0" class="panel">
<tr>
    <td width="45%">
        <div class="label">CUSTOMER NAME</div><div class="value">' . jo_h($customerName) . '</div><br />
        <div class="label">ADDRESS</div><div>' . jo_h($customerAddress ?: '—') . '</div>
    </td>
    <td width="30%">
        <div class="label">CUSTOMER ID</div><div class="value">' . jo_h($customerCode ?: '—') . '</div><br />
        <div class="label">CONTACT NUMBER</div><div class="value">' . jo_h($customerContact ?: '—') . '</div><br />
    </td>
    <td width="25%">
        <div class="label">EMAIL</div><div>' . jo_h($customerEmail ?: '—') . '</div>
    </td>
</tr>
</table>
<div style="margin-bottom:3px;"></div>
<div class="section">UNIT / DEVICE DETAILS</div>
<table width="100%" cellpadding="3" cellspacing="0" class="panel">
<tr>
    <td width="33.3%"><div class="label">DEVICE / UNIT NAME</div><div class="value">' . jo_h($unitName ?: '—') . '</div></td>
    <td width="22.2%"><div class="label">MODEL</div><div class="value">' . jo_h($unitModel ?: '—') . '</div></td>
    <td width="22.2%"><div class="label">CATEGORY</div><div class="value">' . $deviceType . '</div></td>
    <td width="22.2%"><div class="label">WARRANTY STATUS</div>' . jo_h($unitWarranty ?: '—') . '</td>
</tr>
<tr>
    <td><div class="label">PURCHASE DATE</div>' . jo_h($unitPurchaseDate ?: '—') . '</td>
    <td><div class="label">CHARGER / ACCESSORIES</div>' . jo_h($unitCharger ?: '—') . '</td>
    <td><div class="label">MOTHERBOARD</div>' . jo_h($unitMotherboard ?: '—') . '</td>
    <td><div class="label">LCD / LED</div>' . jo_h($unitLcd ?: '—') . '</td>
</tr>
 <tr>
    <td><div class="label">BATTERY</div>' . jo_h($unitBattery ?: '—') . '</td>
    <td><div class="label">CAMERA</div>' . jo_h($unitCamera ?: '—') . '</td>
    <td><div class="label">SPEAKER</div>' . jo_h($unitSpeaker ?: '—') . '</td>
    <td><div class="label">TOUCHPAD</div>' . jo_h($unitTouchpad ?: '—') . '</td>
</tr>';
    if($deviceCat != '1') {
    $html .= '<tr>
                <td><div class="label">RAM</div>' . jo_h($unitRam ?: '—') . '</td>
                <td><div class="label">HDD / SSD</div>' . jo_h($unitStorage ?: '—') . '</td>
                <td><div class="label">PROCESSOR</div>' . jo_h($unitProcessor ?: '—') . '</td>
                <td><div class="label">KEYBOARD</div>' . jo_h($unitKeyboard ?: '—') . '</td>

            </tr>
           
            ';
    }

    $html .= '</table>
<div style="margin-bottom:3px;"></div>

<div class="section">VISUAL PARTS INSPECTION — CONDITION AT INTAKE</div>
<table width="100%" cellpadding="0" cellspacing="0" class="check-grid">
';

    // Visual Inspection fields
        if ((string)$deviceCat != '1') {

            // Laptop / PC: display all visual inspection fields
            $fieldsToDisplay = $visualFields;

        } else {

            // Smartphone: display only applicable fields
            $fieldsToDisplay = array(
                'LCD / LED' => 'visual_lcd',
                'USB Port' => 'visual_usb',
                'Charger' => 'visual_charger',
                'Screws' => 'visual_screws',
                'Camera' => 'visual_camera',
                'Charging' => 'visual_charging',
                'Speaker' => 'visual_speaker',
                'Battery' => 'visual_battery',
                'Wi-Fi' => 'visual_wifi'
            );
        }

        $visualList = array_chunk($fieldsToDisplay, 4, true);

        foreach ($visualList as $visualRow) {
            $html .= '<tr>';

            foreach ($visualRow as $label => $field) {
                $value = jo_field($header, $field, 'Waived');

                $html .= '<td width="25%">
                    <div class="check-label">' . jo_h($label) . '</div>
                    <div class="check-value">' . jo_h($value) . '</div>
                </td>';
            }

            // Fill empty cells to maintain four columns
            for ($i = count($visualRow); $i < 4; $i++) {
                $html .= '<td width="25%">&nbsp;</td>';
            }

            $html .= '</tr>';
        }
    $html .= '
</table>
<div style="margin-bottom:3px;"></div>


<div class="section">PARTS COST SUMMARY</div>
<table width="100%" cellpadding="0" cellspacing="0" class="items">
<thead>
<tr>
    <th width="8%">#</th>
    <th width="19%">ITEM CODE</th>
    <th width="31%">DESCRIPTION</th>
    <th width="8%">QTY</th>
    <th width="16%">AMOUNT</th>
</tr>
</thead>
<tbody>
';

if (is_array($items) && count($items) > 0) {
    $line = 1;
    foreach ($items as $item) {
        $code = jo_field($item, 'code', '');
        $description = jo_field($item, 'description', '');
        $qty = (float)jo_field($item, 'qty', 0);
        $amount = (float)jo_field($item, 'amount_due', jo_field($item, 'amount', 0));
        $html .= '<tr>
            <td align="center">' . $line++ . '</td>
            <td>' . jo_h($code) . '</td>
            <td>' . jo_h($description) . '</td>
            <td align="right">' . jo_h(rtrim(rtrim(number_format($qty, 2, '.', ','), '0'), '.')) . '</td>
            <td align="right">' . jo_money($amount) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center" class="muted">No parts or items recorded for this Job Order.</td></tr>';
}

$html .= '
</tbody>
</table>
<div style="margin-bottom:3px;"></div>
<div class="section">SERVICE COST SUMMARY</div>
<table width="100%" cellpadding="0" cellspacing="0" class="items">
<thead>
<tr>
    <th width="8%">#</th>
    <th width="19%">ITEM CODE</th>
    <th width="31%">DESCRIPTION</th>
    <th width="8%">QTY</th>
    <th width="16%">PRICE</th>
    <th width="16%">AMOUNT DUE</th>
</tr>
</thead>
<tbody>
';

if (is_array($service) && count($service) > 0) {
    $line = 1;
    foreach ($service as $itemService) {
        $code = jo_field($itemService, 'item_code', '');
        $description = jo_field($itemService, 'description', '');
        $qty = (float)jo_field($itemService, 'qty', 0);
        $amount = (float)jo_field($itemService, 'amount', jo_field($itemService, 'amount', 0));
        $amountdue = (float)jo_field($itemService, 'amount_due', jo_field($itemService, 'amount_due', 0));
        $html .= '<tr>
            <td align="center">' . $line++ . '</td>
            <td>' . jo_h($code) . '</td>
            <td>' . jo_h($description) . '</td>
            <td align="right">' . jo_h(rtrim(rtrim(number_format($qty, 2, '.', ','), '0'), '.')) . '</td>
            <td align="right">' . jo_money($amount) . '</td>
            <td align="right">' . jo_money($amountdue) . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center" class="muted">No service recorded for this Job Order.</td></tr>';
}

$html .= '
</tbody>
</table>

<htmlpagefooter name="myfooter">
    <table width="100%" cellpadding="0" cellspacing="0"
           style="margin-top:8px;">
        <tr>
            <td width="52%" valign="top" style="padding-right:10px;">
                <div class="section">PAYMENT HISTORY</div>

                <table width="100%" cellpadding="4" cellspacing="0"
                       border="1"
                       style="border-collapse:collapse;font-size:7pt;">
                    <tr>
                        <th width="25%">DATE</th>
                        <th width="45%">TYPE / METHOD</th>
                        <th width="30%" align="right">AMOUNT</th>
                    </tr>
';

/* Payment history rows */
if (count($paymentRows) > 0) {
    foreach ($paymentRows as $payment) {
        $paymentDescription =
            jo_field($payment, 'payment_type', '') .
            ' / ' .
            jo_field($payment, 'payment_method', '');

        $html .= '
            <tr>
                <td>' . jo_h($payment['payment_date']) . '</td>
                <td>' . jo_h($paymentDescription) . '</td>
                <td align="right">₱ ' .
                    jo_money($payment['amount']) . '
                </td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="3" align="center">
                No payments recorded.
            </td>
        </tr>';
}

/* Total paid */
$html .= '
                    <tr>
                        <td colspan="2">
                            <strong>TOTAL PAID</strong>
                        </td>
                        <td align="right">
                            <strong>₱ ' .
                            jo_money($paymentTotal) . '</strong>
                        </td>
                    </tr>
                </table>

                <br />

                <div class="section">UNIT HISTORY / CUSTOMER CONCERN</div>
                <div class="panel" style="min-height:55px;" colspan="3"s>' .
                    nl2br(jo_h(
                        $remarks !== '' ? $remarks : 'No history recorded.'
                    )) . '
                </div>
            </td>

            <td width="48%" valign="top" style="padding-top:30px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="total-label">Total Parts Cost</td>
                        <td class="total-value">₱ ' .
                            jo_money($partsTotal) . '
                        </td>
                    </tr>
                    <tr>
                        <td class="total-label">Service / Labor Cost</td>
                        <td class="total-value">₱ ' .
                            jo_money($laborTotal) . '
                        </td>
                    </tr>
                     <tr>
                        <td class="grand-label">TRANSACTION TOTAL</td>
                        <td class="grand-value">₱ ' .
                            jo_money($grandTotal) . '
                        </td>
                    </tr>
                    <tr>
                        <td height="5"></td>
                        <td height="5">&nbsp;</td>
                    </tr>
                </table>
                <div class="section" style="text-align: center; margin-top: 10px;">
                    <strong>REMARKS</strong>
                </div>
                <div class="panel" style="min-height:55px;" colspan="3"s>' .
                    nl2br(jo_h(
                        $notes !== '' ? $notes : 'No notes recorded.'
                    )) . '
                </div>
            </td>
        </tr>
    </table>
</htmlpagefooter>

<sethtmlpagefooter name="myfooter" value="on" />
';


$mpdf->WriteHTML($html);
$mpdf->Output('JO-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$joNo) . '.pdf', 'I');
exit;
?>
