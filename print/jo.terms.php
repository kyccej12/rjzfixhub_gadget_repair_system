<?php

session_start();
require_once("../handlers/session.guard.php");

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include("../lib/mpdf6/mpdf.php");


$mpdf = new mPDF('utf-8', 'FOLIO-P', 0, 'dejavusans', 8, 8, 8, 8, 6, 6);
$mpdf->SetTitle('Job Order ' . $joNo);
$mpdf->SetAuthor('RJZ Fix Hub');
$mpdf->SetDisplayMode('fullpage');

$html = '
<style>

    body {
        font-family: dejavusans, sans-serif;
        font-size: 7pt;
        color: #243247;
        margin: 0;
        padding: 0;
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
        padding-top: 4px;
        font-size: 7pt;
        width: 100%;
        box-sizing: border-box;
    }

    .small {
        font-size: 6.5pt;
        color: #64748b;
    }

    img {
        max-width: 100%;
        height: auto;
    }

     /* Section headings */
    .section {
        background: #eaf2f7;
        color: #12304a;
        font-weight: bold;
        font-size: 7pt;
        padding: 4px 5px;
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
        font-size: 8pt;
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

</style>
<div class="section">TERMS &amp; CONDITIONS</div>
<div class="panel terms">
    <b>1. Initial Inspection.</b> The condition and accessories listed in this Job Order are based on the initial visual inspection. Hidden or intermittent faults may only be discovered during diagnosis or repair.<br />
    <b>2. Data and Backup.</b> The customer is responsible for backing up important data. Repair, testing, software work, or storage failure may result in data loss. Where possible, remove or protect confidential information before surrendering the device.<br />
    <b>3. Passwords and Security.</b> Any password, PIN, or access credential supplied for testing will be handled only as needed to perform the authorized service. The customer should disclose only what is necessary and change credentials after service if appropriate.<br />
    <b>4. Parts and Approval.</b> Parts replacement, additional work, or costs beyond the agreed scope should be discussed with the customer before proceeding. Replaced parts may be retained or returned by prior agreement, subject to applicable requirements.<br />
    <b>5. Repair Time.</b> Estimated completion dates are not guaranteed and may change due to parts availability, the extent of damage, or additional faults discovered during diagnosis.<br />
    <b>6. Warranty.</b> Any repair warranty, its duration, and exclusions must be stated on the official receipt or separate warranty record. Physical damage, liquid damage, misuse, unauthorized repair, and unrelated faults may be excluded where stated in that warranty policy.<br />
    <b>7. Unrepairable / Uncollected Devices.</b> The shop will contact the customer regarding diagnosis, repair outcome, and collection. Any diagnostic fee, storage fee, or unclaimed-device policy must be disclosed to the customer before it is applied and must comply with applicable law.<br />
    <b>8. Authorization.</b> By signing below, the customer confirms that the recorded device details and initial condition have been reviewed and authorizes the shop to perform the service agreed for this Job Order.
</div>
<br />

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="width:100%; table-layout:fixed; border-collapse:collapse; margin-top:50px;">

    <tr>
        <!-- CUSTOMER SIGNATURE -->
        <td width="50%" valign="bottom"
            style="width:50%; padding:0 18px 0 0;">

            <div style="height:45px;">&nbsp;</div>

            <div class="signature">
                Customer Signature over Printed Name
            </div>
        </td>

        <!-- RECEIVED / INSPECTED BY -->
        <td width="50%" valign="bottom"
            style="width:50%; padding:0 0 0 18px;">

            <div style="height:45px;">&nbsp;</div>

            <div class="signature">
                Received / Inspected By
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2" class="small"
            style="padding-top:14px;">
            I confirm that I have read or had the above terms explained to me
            and have received a copy of this Job Order.
        </td>
    </tr>

</table>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
?>
