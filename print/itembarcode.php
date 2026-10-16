<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/initDB.php");

    $con = new myDB;
    $icode = $_GET['item'];
    $a = $con->getArray("select b.mgroup as category, b.code as prefix, a.item_code as `code`, brand, description as `desc`, unit, unit_cost from products_master a left join options_mgroup b on a.category = b.mid where item_code = '$icode';");

    $rand = str_pad(mt_rand(0, 99999999999999), 10, '0', STR_PAD_LEFT);

    $mpdf=new mPDF('win-1252','BARCODE','','',0,0,3,0,0,0);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Black Smoke Haus");
    $mpdf->SetDisplayMode(100);

$html = "
<html>
<head>
    <title>Item Barcode</title>
    <style>
        body {
            font-family: sans;
            font-size: 5.5pt;
        }    
    </style>
</head>
<body>
    <table width='100%' cellpadding='0' cellspacing='0' style='font-weight: bold;'>
        <tr>
            <td align='left' colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".strtoupper($a['desc'])."</td>
        </tr>
        <tr>
            <td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$a['code']."</td>
            <td align='right' width=50% align=right>".strtoupper($a['category'])."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>

        </tr>
        <tr>
            <td colspan='2' align='center'>
            <barcode code='".$icode."' type='C128A' height='1' size='1.3'>
            </td>
        </tr> 
    </table>
        <table width='100%' cellpadding='0' cellspacing='0' style='font-weight: bold;'>
            <tr>
                <td align='center' style='font-size:5.5pt; letter-spacing:10px;'>".$rand."</td>
            </tr>
        </table>
</body>
</html>
";


 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>