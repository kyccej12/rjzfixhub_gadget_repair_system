<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");
	ini_set("memory_limit","2048M");
	ini_set("max_execution_time","0");

	$mpdf=new mPDF('win-1252','letter-l','','',8,8,32,40,8,8);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);
	
	$con = new _init();

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '1';");
		
		$query = $con->dbquery("
		SELECT a.doc_no as xdoc, if(invoice_no=0,'',CONCAT('SI-',LPAD(a.invoice_no,6,'0'))) AS inv_no, b.description as xterms, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, a.customer, if(a.customer=0,'Walkin Sales',a.customer_name) as customer_name, 0 AS cash_amt, '' AS cash_ref, a.amount AS chg_amt, '' AS chg_refamount, '' AS col_amt, '' as col_ref, '' as col_pdc_amt, '' as col_pdc_ref FROM invoice_header a left join options_terms b on a.terms=b.terms_id WHERE a.invoice_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Finalized' AND terms != 0
		UNION ALL SELECT doc_no as xdoc, if(invoice_no=0,'',CONCAT('SI-',LPAD(a.invoice_no,6,'0'))),'' as xterms, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, a.customer, if(a.customer=0,'Walkin Sales',a.customer_name) as customer_name, a.amount AS cash_amt, IF(pay_type='cash','CSH',IF(pay_type='ccard',CONCAT('CC-','*',RIGHT(card_no,3)),CONCAT('CK-',issue_bank,': ',cheq_no))) AS cash_ref, '' AS chg_amt, '' AS chg_ref, '' AS col_amt, '' AS col_ref, '' as col_pdc_amt, '' as col_pdc_ref FROM invoice_header a WHERE a.invoice_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Finalized' AND a.terms = 0 
		UNION ALL SELECT doc_no as xdoc, if(a.invoice_no=0,'**',CONCAT('SI-',LPAD(a.invoice_no,6,'0'),'*')) AS inv_no, b.description AS xterms, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, a.customer, if(a.customer=0,'Walkin Sales',a.customer_name) as customer_name, 0 AS cash_amt, '' AS cash_ref, '' AS chg_amt, '' AS chg_refamount, '' AS col_amt, '' as col_ref, '' as col_pdc_amt, '' as col_pdc_ref FROM invoice_header a LEFT JOIN options_terms b ON a.terms=b.terms_id WHERE a.invoice_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Active' 
		UNION ALL SELECT doc_no as xdoc, if(a.invoice_no=0,'**',CONCAT('SI-',LPAD(a.invoice_no,6,'0'),'**')) AS inv_no, b.description AS xterms, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, a.customer, if(a.customer=0,'Walkin Sales',a.customer_name) as customer_name, 0 AS cash_amt, '' AS cash_ref, '' AS chg_amt, '' AS chg_refamount, '' AS col_amt, '' as col_ref, '' as col_pdc_amt, '' as col_pdc_ref FROM invoice_header a LEFT JOIN options_terms b ON a.terms=b.terms_id WHERE a.invoice_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Cancelled'  
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0')) AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, a.net AS col_amt, 'CSH' AS col_ref, '' AS col_pdc_amt, '' AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Posted' AND cr_date != check_date AND pay_type IN ('Cash') 
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0')) AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, a.net AS col_amt, pay_type AS col_ref, '' AS col_pdc_amt, '' AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Posted' AND pay_type IN ('OL-MBTC','OL-BDO') 
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0')) AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, a.net AS col_amt, CONCAT(bank,' &raquo; ',check_no, ' dtd. ',DATE_FORMAT(check_date,'%m/%d/%y')) AS col_ref, '' AS col_pdc_amt, '' AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Posted' AND pay_type IN ('Check') AND check_date = cr_date 
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0')) AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, '' AS col_amt, '' AS col_ref, a.net AS col_pdc_amt, CONCAT(bank,' &raquo; ',check_no, ' dtd. ',DATE_FORMAT(check_date,'%m/%d/%y')) AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['date'])."' AND a.status = 'Posted' AND cr_date != check_date AND pay_type = 'Check' 
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0'),'*') AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, '' AS col_amt, '' AS col_ref, '' AS col_pdc_amt, '' AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['DATE'])."' AND a.status = 'Active' 
		UNION ALL SELECT trans_no as xdoc, CONCAT('CR-',LPAD(cr_no,6,'0'),'**') AS inv_no, '' AS xterms, DATE_FORMAT(cr_date,'%m/%d/%Y') AS doc_date, a.customer, a.customer_name, '' AS cash_amt,'' AS cash_ref, '' AS chg_amt, '' AS chg_ref, '' AS col_amt, '' AS col_ref, '' AS col_pdc_amt, '' AS col_pdc_ref FROM cr_header a WHERE a.cr_date = '".$con->formatDate($_GET['DATE'])."' AND a.status = 'Cancelled' order by xdoc;");
		
		
		list($siCash) = $con->getArray("select sum(amount) from invoice_header a where invoice_date = '".$con->formatDate($_GET['date'])."' and terms = 0 and pay_type = 'cash' and a.status = 'Finalized'");
		list($siCheck) = $con->getArray("select sum(amount) from invoice_header a where invoice_date = '".$con->formatDate($_GET['date'])."' and terms = 0 and pay_type = 'check' and a.status = 'Finalized'");
		list($siChg) = $con->getArray("select sum(amount) from invoice_header a where invoice_date = '".$con->formatDate($_GET['date'])."' and terms != 0 and a.status = 'Finalized'");
		
		list($crCash) = $con->getArray("select sum(net) from cr_header a where cr_date = '".$con->formatDate($_GET['date'])."' and pay_type = 'Cash' and a.status = 'Posted'");
		list($crCheck) = $con->getArray("select sum(net) from cr_header a where cr_date = '".$con->formatDate($_GET['date'])."' and pay_type = 'Check' and a.status = 'Posted' and cr_date = check_date");
		list($crCheckPDC) = $con->getArray("select sum(net) from cr_header a where cr_date = '".$con->formatDate($_GET['date'])."' and pay_type = 'Check' and a.status = 'Posted' and cr_date != check_date");

	
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 8pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

.lowerHeader {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}

.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td.lowertotals {
	border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td style="color:#000000;" width=80><img src="../images/'.$co['headerlogo'].'" height=70 /></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 9pt; color: #000000;">Daily Sales & Collection Report</span><br /><span style="font-size: 6pt; font-style: italic;">'.$myBranch.'<br/>' . $_GET[date] . '</span>
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 6pt; width: 100%" cellpadding=1>
	<tr>
		<td colspan=5 width="100%" align="left"><b>SALES & COLLECTION BREAKDOWN FOR THE DAY &raquo;</b></td>
	</tr>
	<tr>
		<td width=20%><b>CHARGE SALES<br/>&raquo; &#8369;'.number_format($siChg,2).'</b></td>
		<td width=20% align=left><b>CASH SALES<br/>&raquo; &#8369;'.number_format($siCash,2).'</b></td>
		<td width=20% align=left><b>CASH SALES (On-Date Checks)<br/>&raquo; &#8369;'.number_format($siCheck,2).'</b></td>
		<td width=20% align=left style="padding-left:20px;"><b>C.R (Cash)<br/>&raquo; &#8369;'.number_format($crCash,2).'</b></td>
		<td width=20% align=left><b>C.R (On-Date Checks)<br/>&raquo; &#8369;'.number_format($crCheck,2).'</b></td>
	</tr>
	<tr>
		<td><b>C.R (Post-Dated)<br/>&raquo; &#8369;'.number_format($crCheckPDC,2).'</b></td>
		<td align=left></td>
		<td align=center></td>
		<td width=20% align=left style="padding-left:20px;"></td>
		<td align=right></td>
	</tr>
	<tr><td align="left"><b>TOTAL CASH & ON-DATE CHECKS &raquo;</td>
		<td colspan=4><b>&#8369;'.number_format(($siCash+$siCheck+$crCash+$crCheck),2).'</b></td>
	</tr>
	<tr>
		<td align="left"><b>TOTAL CASH-ON-HAND &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&raquo; </td>
		<td colspan=4><b>&#8369;'.number_format(($siCash+$crCash),2).'</b></td>
	</tr>
</table>
<table style="border-top: 1px solid #000000; font-size: 6pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right">Run Date: ' . $now . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table border=1 class="items" width="100%" align=center style="font-size: 6pt; border-collapse: collapse;" cellpadding="1">
<thead>
	<tr>
		<td colspan=4 align=center styl="border: none;"></td>
		<td width="20%" align=center colspan=2><b>CHARGE SALES</b></td>
		<td width="20%" align=center colspan=2><b>CASH SALES</b></td>
		<td width="20%" align=center colspan=2><b>COLLECTION <br/>(Cash/On-Date Checks)</b></td>
		<td width="20%" align=center colspan=2><b>COLLECTION <br/> (Post Dated Checks)</b></td>
	</tr>
	<tr>
		<td width="10%" align=left><b>TRANS. NO.</b></td>
		<td width="10%" align=left><b>SI NO.</b></td>
		<td width="10%" align=center><b>TRANS DATE</b></td>
		<td align=left><b>CUSTOMER</b></td>
		<td width="5%" align=center><b>TERMS</b></td>
		<td width="7%" align=center><b>AMOUNT</b></td>
		<td width="7%" align=center><b>PAY REF#</b></td>
		<td width="7%" align=center><b>AMOUNT</b></td>
		<td width="10%" align=center><b>PAY REF#</b></td>
		<td width="7%" align=center><b>AMOUNT</b></td>
		<td width="10%" align=center><b>PAY REF#</b></td>
		<td width="7%" align=center><b>AMOUNT</b></td>
	</tr>
</thead>
<tbody>';

while($row =  $query->fetch_array()) {
	
	$html = $html . '<tr>
		<td align=center>' . $row['xdoc'] . '</td>
		<td align=center>' . $row['inv_no'] . '</td>
		<td align=center>' . $row['doc_date'] . '</td>
		<td align=left>' . $row['customer_name'] . '</td>
		<td align=center>' . $row['xterms'] . '</td>
		<td align=right>' . number_format($row['chg_amt'],2) . '</td>
		<td align=center>' . $row['cash_ref'] . '</td>
		<td align=right>' . number_format($row['cash_amt'],2) . '</td>
		<td align=center>' . $row['col_ref'] . '</td>
		<td align=right>' . number_format($row['col_amt'],2) . '</td>
		<td align=center>' . $row['col_pdc_ref'] . '</td>
		<td align=right>' . number_format($row['col_pdc_amt'],2) . '</td>
	</tr>
	';
}


$html = $html . '</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>