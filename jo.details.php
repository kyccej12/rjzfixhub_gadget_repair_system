<?php	
	session_start();
	// ini_set("display_errors","on");
	require_once("handlers/session.guard.php");
	include("handlers/_generics.php");
	
	$mydb = new _init;
	$bid = $_SESSION['branchid'];

	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['jo_no']) && $_REQUEST['jo_no'] != '') { 
		$res = $mydb->getArray("SELECT *, LPAD(jo_no,6,0) AS jono, DATE_FORMAT(jo_date,'%m/%d/%Y') AS d8, LPAD(customer_code,6,'0') AS cid, trace_no FROM jo_header WHERE jo_no = '$_REQUEST[jo_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $jo_no = $res['jono']; $deviceCat= $res['device_type']; $status = $res['status']; $traceNo = $res['trace_no'];
	} else {  
		$status = "Active"; $traceNo = $mydb->generateRandomString();
	}

	list($joTotal) = $mydb->getArray("select count(*) from jo_details where trace_no = '$res[trace_no]';");

	$totalPaid = 0;

	if (isset($res['jo_no']) && $res['jo_no'] != '') {
		$paidData = $mydb->getArray("SELECT IFNULL(SUM(amount), 0) FROM jo_payments WHERE jo_no = '".$res['jo_no']."' AND branch = '$bid';");

		$totalPaid = (float)$paidData[0];
	}
	
function setSOClickers($status, $jo_no, $urights) {
		global $mydb;

		$headerControls = '';

		switch ($status) {
			case "Received":
			case "On-going Repair":
			case "For Release":
					$headerControls .= '
						<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'saveJOHeader\');">
							<span class="ui-icon ui-icon-disk"></span>Save Data
						</button>

						<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.printJO(\'' . htmlspecialchars($jo_no, ENT_QUOTES, 'UTF-8') . '\', 0);">
							<span class="ui-icon ui-icon-print"></span>Print Job Order
						</button>

						<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'viewJOComment\');">
							<span class="ui-icon ui-icon-print"></span>View Comment
						</button>

						 <button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'openReceivePayment\');">
							<span class="ui-icon ui-icon-cart"></span>Receive Payment
						</button>

					';
			break;

			case "Cancelled":
				if ($urights == "admin") {
					$headerControls .= '
						<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'reuse\');">
							<span class="ui-icon ui-icon-document-b"></span>Recycle this Document
						</button>
					';
				}
			break;

			case "Active":
			default:
				$headerControls .= '
					<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'receivedJO\');">
						<span class="ui-icon ui-icon-check"></span>Receive Job Order
					</button>

					<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'saveJOHeader\');">
						<span class="ui-icon ui-icon-disk"></span>Save Data
					</button>
				';

				if ($urights == "admin" && $jo_no != '') {
					$headerControls .= '
						<button type="button" class="ui-button ui-widget ui-corner-all" onclick="parent.callJOFunction(\'cancel\');">
							<span class="ui-icon ui-icon-cancel"></span>Cancel Job Order
						</button>
					';
				}
				break;
		}

		echo $headerControls;
	}
	
	function setSONavs($jo_no) {
		global $mydb;
		list($fwd) = $mydb->getArray("select jo_no from jo_header where jo_no > $jo_no and branch = '$_SESSION[branchid]' limit 1;");
		list($prev) = $mydb->getArray("select jo_no from jo_header where jo_no < $jo_no and branch = '$_SESSION[branchid]' order by jo_no desc limit 1;");
		list($last) = $mydb->getArray("select jo_no from jo_header where branch = '$_SESSION[branchid]' order by jo_no desc limit 1;");
		list($first) = $mydb->getArray("select jo_no from jo_header where branch = '$_SESSION[branchid]' order by jo_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewJO('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewJO('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewJO('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewJO('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}

?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>RJZ Fix Hub Repair Shop System Ver. 1.0a</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/jo.js?sid=<?php echo uniqid(); ?>"></script>
	<script>
	
		$(document).ready(function($) {
			$("#unit_pdate").datepicker({changeMonth: true, changeYear: true,  yearRange: "-90:+00"});

			$('#customer_code').autocomplete({
				source: 'suggestCustomer.php',
				minLength: 3,

				select: function(event, ui) {
					// Explicitly set the selected customer code BEFORE saving
					$("#customer_code").val(ui.item.value);

					$("#customer_name").val(ui.item.name);
					$("#customer_address").val(ui.item.addr);
					$("#customer_contactno").val(ui.item.contact_no);
					$("#customer_email").val(ui.item.email);
					saveJOHeader();

					// Prevent autocomplete from overwriting the selected value
					return false;
				}
			});

			var visualResults = [
				"WORKING",
				"DEFECTIVE"
			]

			var speakerVisaul = [
				"WORKING",
				"DISTORTED",
				"DEFECTIVE"
			]

			var visualCharging = [
				"YES",
				"NO"
			]

			var visualScrews = [
				"COMPLETE",
				"MISSING "
			]

				$("#visual_lcd, #visual_usb, #visual_bitlocker, #visual_charger, " +
				"#visual_storage, #visual_camera, #visual_touchpad, " +
				"#visual_charging, #visual_hdmi, #visual_bios, " +
				"#visual_keyboard, #visual_battery, #visual_ram, #visual_wifi" ).autocomplete({
					source: visualResults, minLength: 0
				}).focus(function() {
					$(this).data("uiAutocomplete").search($(this).val());
				});

				$(" #visual_speaker" ).autocomplete({
					source: speakerVisaul, minLength: 0
				}).focus(function() {
					$(this).data("uiAutocomplete").search($(this).val());
				});
				
				$(" #visual_charging" ).autocomplete({
					source: visualCharging, minLength: 0
				}).focus(function() {
					$(this).data("uiAutocomplete").search($(this).val());
				});

				$(" #visual_screws" ).autocomplete({
					source: visualScrews, minLength: 0
				}).focus(function() {
					$(this).data("uiAutocomplete").search($(this).val());
				});


			 $("#device_type,#unit_name, #unit_pdate, #unit_lcd, #unit_battery, " +
				"#unit_model, #unit_warranty, #unit_storage, #unit_charger, " +
				"#unit_category, #unit_processor, #unit_ram, #unit_motherboard, " +
				"#visual_lcd, #visual_usb, #visual_bitlocker, #visual_charger, " +
				"#visual_storage, #visual_screws, #visual_camera, #visual_touchpad, " +
				"#visual_charging, #visual_speaker, #visual_hdmi, #visual_bios, " +
				"#visual_keyboard, #visual_battery, #visual_ram, #visual_wifi, #laborTotal, #remarks, #action_taken")
				.on("change", function() {
					saveJOHeader();
			});


			if (parent && parent.$("#joHeaderButtons").length) {
				parent.$("#joHeaderButtons").html(
					<?php echo json_encode($joButtons); ?>
				);
			}

			$('#details').dataTable({
				"ajax": {
					"url": "jo.datacontrol.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrieve", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "150",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				
				"aoColumns": [
					{ mData: 'id' },
					{ mData: 'code' },
					{ mData: 'brand' },
					{ mData: 'description' },
					{ mData: 'qty', render: $.fn.dataTable.render.number(',', '.', 2, '')},
					{ mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
					{ mData: 'amount_due', render: $.fn.dataTable.render.number(',', '.', 2, '') }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,3,4,5,6]},
					{ "targets": [0], "visible": false }
				]
			});
			
			<?php if($status == 'Completed' || $status == 'Cancelled') {
				echo "$(\"#xform :input:not([name=setActive], [name=setPrint], [name=setRecycle])\").prop('disabled',true);";
			} else { ?>
				$("#jo_date").datepicker();
			
			
			<?php } ?>
		});

		function redrawDataTable() {
			$('#details').DataTable().ajax.url("jo.datacontrol.php?mod=retrieve&trace_no=<?php echo $traceNo; ?>").load();
		}

	</script>

	<style>
		 body {
			font-family: dejavusans, sans-serif;
		}
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; 
			width: 100%; 
		}
		
		table.dataTable tr.even { background-color: #f5f5f5;  }
		table.dataTable tr.odd { background-color: white; }
		.dataTables_filter input { width: 250px; }

		.unit-details-fieldset {
			width: 100%;
			height: 100%;
			min-height: 200px;
			box-sizing: border-box;
			border: 1px solid #b8c5d1;
			border-radius: 5px;
			padding: 12px;
			margin: 0;
			background: #f3f2f2;
		}

		.unit-details-fieldset legend {
			padding: 4px 10px;
			font-weight: bold;
			color: #2c4053;
		}

		.section {
			background: #c9dbe6;
			color: #12304a;
			font-weight: bold;
			font-size: 7pt;
			padding: 4px 5px;
			border-left: 2px solid #1c999b;
		}

	</style>

</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform" onsubmit="return false;">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="trace_no" id="trace_no" value="<?php echo $traceNo; ?>">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
			<tr>
				<td class="upper_menus">

					
					<?php
						ob_start();
						setSOClickers($status, $jo_no, $_SESSION['utype']);
						$joButtons = ob_get_clean();

						ob_start();
						if ($jo_no != '') {
							setSONavs($jo_no);
						}
						$joNavs = ob_get_clean();
					?>

					<script>
					$(document).ready(function() {
						if (parent && parent.$("#joHeaderButtons").length) {
							parent.$("#joHeaderButtons").html(
								"<div class='jo-left-buttons'></div>" +
								"<div class='jo-right-buttons'></div>"
							);

							parent.$("#joHeaderButtons .jo-left-buttons").html(
								<?php echo json_encode($joButtons); ?>
							);

							parent.$("#joHeaderButtons .jo-right-buttons").html(
								<?php echo json_encode($joNavs); ?>
							);
						}
					});
					</script>

				</td>
			</tr>
			<tr><td height="2"></td></tr>
		</table>
		<table width=100% style="text-align: center; padding: 5px;" class="section">
			<tr>
				<td height=2></td>
				<td align=center class="bareBold" style="font-weight:bold;" valign=top>CUSTOMER DETAILS</td>
				<td <height=2></td>
			</tr>
		</table>
		<table border="0" cellpadding="0" cellspacing="1" width=100% style="background-color: #f3f2f2;">
			<tr>
				<td width=60% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td colspan=2 style="border-buttom: 1px solid #fff;"></td></tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;" valign=top>&nbsp;</td>
							<td align=left>
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_code" name="customer_code" value="<?php  echo $res['cid']?>" class="inputSearch2" style="padding-left: 22px; width:98%;" placeholder = "0" onchange="javascript: checkClear(this.value);"></td>
										<td width=75% align=right colspan=2><input class="gridInput" type="text" name="customer_name" id="customer_name" autocomplete="off" value="<?php echo $res['customer_name']; ?>" style="width: 100%;" placeholder="Walkin Customer" readonly></td>
									</tr>
									<tr>
										<td style="font-size: 9px; color: gray; padding-left: 5px;">Customer ID</td><td colspan=2 style="font-size: 9px; padding-left: 5px; color: gray;">Customer Name</td>
									</tr>
									<tr>
										<td width=100% colspan=2><input class="gridInput" type="text" id="customer_contactno" name="customer_contactno" value="<?php echo $res['customer_contactno']?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; color: gray; padding-left: 5px;" colspan=2 >Customer Contact No.</td>
									</tr>
									<tr>
										<td width=100% colspan=2><input class="gridInput" type="text" id="customer_address" name="customer_address" value="<?php echo $res['customer_address']?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; color: gray; padding-left: 5px;" colspan=2 >Customer Address</td>
									</tr>
									<tr>
										<td width=100% colspan=2><input class="gridInput" type="text" id="customer_email" name="customer_email" value="<?php echo $res['customer_email']?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; color: gray; padding-left: 5px;" colspan=2 >Customer Email</td>
									</tr>
								</table>
							</td>				
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Job Order No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="jo_no" id="jo_no" value="<?php echo $jo_no; ?>" >
							</td>				
						</tr>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="jo_date" id="jo_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
							</td>				
						</tr>
						<tr><td height=2></td></tr>					
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Device Type&nbsp;:</td>
							<td align=left>
								<select name="device_type" id="device_type" style="width: 152px;" class="gridInput">
									<?php
										$bQuery = $mydb->dbquery("SELECT device_id, category FROM options_devicetype ORDER BY device_id;");
										while($bRow = $bQuery->fetch_array()) {
											echo "<option value = '$bRow[0]'>$bRow[1]</option>";
										}
									?>
								</select>							
							</td>				
						</tr>					
					</table>
				</td>
			</tr>
		</table>

		<!-- UNIT DETAILS HEADER -->
		<table width="100%" style="text-align:center; padding:5px;" class="section">
			<tr>
				<td height="2"></td>
				<td align="center" class="bareBold" style="font-weight:bold; border-bottom: 1px solid 17a2a4;" valign="top"> UNIT DETAILS
				</td>
				<td height="2"></td>
			</tr>
		</table>

		<!-- UNIT DETAILS BODY -->
		<table border="0" cellpadding="0" cellspacing="1" width="100%" style="background-color: #f3f2f2;">
			<tr>
				<!-- COLUMN 1 & 2: GENERAL INFORMATION -->
				<td width="66.6%" valign="top">
					<fieldset class="unit-details-fieldset">
						<legend>General Information</legend>

						<table width="100%" cellpadding="0" cellspacing="8" border="0">
							<tr>
								<!-- LEFT SIDE -->
								<td width="50%" valign="top">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td width="40%" class="bareBold">Device/Unit Name:</td>
											<td>
												<input type="text" name="unit_name" id="unit_name"
													value="<?php echo $res['unit_name']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">Purchase Date:</td>
											<td>
												<input type="text" name="unit_pdate" id="unit_pdate"
													value="<?php echo $res['unit_pdate']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">LCD/LED:</td>
											<td>
												<input type="text" name="unit_lcd" id="unit_lcd"
													value="<?php echo $res['unit_lcd']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">Battery:</td>
											<td>
												<input type="text" name="unit_battery" id="unit_battery"
													value="<?php echo $res['unit_battery']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
									</table>
								</td>

								<!-- RIGHT SIDE -->
								<td width="50%" valign="top">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td width="40%" class="bareBold">Model:</td>
											<td>
												<input type="text" name="unit_model" id="unit_model"
													value="<?php echo $res['unit_model']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">Warranty Status:</td>
											<td>
												<input type="text" name="unit_warranty" id="unit_warranty"
													value="<?php echo $res['unit_warranty']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">Motherboard:</td>
											<td>
												<input type="text" name="unit_motherboard" id="unit_motherboard"
													value="<?php echo $res['unit_motherboard']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
										<tr><td height="5"></td></tr>
										<tr>
											<td class="bareBold">Charger:</td>
											<td>
												<input type="text" name="unit_charger" id="unit_charger"
													value="<?php echo $res['unit_charger']?>"
													class="gridInput" style="width:100%;">
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>

					</fieldset>
				</td>
				<!-- COLUMN 3 -->
				<td width="33.3%" valign="top">
					<fieldset class="unit-details-fieldset">
    					<legend>Laptop / PC Unit Details</legend>
							<table width="100%" cellpadding="0" cellspacing="5" border="0">
								<tr>
									<td colspan="2" style="border-bottom:1px solid #fff;"></td>
								</tr>
								<tr>
									<td align="left" class="bareBold"
										style="padding-left:35px;" valign="top">&nbsp;</td>
									<td align="left">
										<!-- LAPTOP / PC UNIT DETAILS -->

										<table cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td width="40%" class="bareBold">Category:</td>
												<td>
													<input type="text" name="unit_category" id="unit_category" class="gridInput" style="width:100%;" value="<?php echo $res['unit_category']?>">
												</td>
											</tr>
											<tr><td height="5"></td></tr>
											<tr>
												<td class="bareBold">Processor:</td>
												<td>
													<input type="text" name="unit_processor" id="unit_processor" class="gridInput" style="width:100%;" value="<?php echo $res['unit_processor']?>">
												</td>
											</tr>
											<tr><td height="5"></td></tr>
											<tr>
												<td class="bareBold">RAM:</td>
												<td>
													<input type="text" name="unit_ram" id="unit_ram" class="gridInput" style="width:100%;" value="<?php echo $res['unit_ram']?>">
												</td>
											</tr>
											<tr><td height="5"></td></tr>
											<tr>
												<td class="bareBold">HDD/SSD:</td>
												<td>
													<input type="text" name="unit_storage" id="unit_storage" class="gridInput" style="width:100%;" value="<?php echo $res['unit_storage']?>">
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
					</fieldset>
				</td>
			</tr>
		</table>

		<table width=100% style="text-align: center; padding: 5px;" class="section">
			<tr>
				<td height=2></td>
				<td align=center class="bareBold" style="font-weight:bold; border-bottom: 1px solid 17a2a4;" valign=top>VISUAL PARTS INSPECTION</td>
				<td <height=2></td>
			</tr>
		</table>
		<table border="0" cellpadding="0" cellspacing="1" width=100% style="background-color: #f3f2f2;">
			<tr>
				<!-- COLUMN 1 & 2: GENERAL INFORMATION -->
				<td width=50% valign=top>
					<fieldset class="unit-details-fieldset">
						<legend>General Information</legend>

						<table width="100%" cellpadding="5" cellspacing="8" border="0">
							<tr>
								<!-- LEFT SIDE -->
 								<td width="50%" valign="top">
									<table cellspacing=0 cellpadding=0 border=0 width=100%>
										<tr>
											<td width="40%" class="bareBold">LCD:</td>
											<td>
												<input type="text" name="visual_lcd" id="visual_lcd" class="gridInput" style="width:100%;" value="<?php echo $res['visual_lcd']?>">
											</td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class="bareBold">USB Port:</td>
											<td>
												<input type="text" name="visual_usb" id="visual_usb" class="gridInput" style="width:100%;" value="<?php echo $res['visual_usb']?>">
											</td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class="bareBold">Battery:</td>
											<td>
												<input type="text" name="visual_battery" id="visual_battery" class="gridInput" style="width:100%;" value="<?php echo $res['visual_battery']?>">
											</td>
										</tr>
										
										<tr><td height=2></td></tr>
										<tr>
											<td class="bareBold">Charger:</td>
											<td>
												<input type="text" name="visual_charger" id="visual_charger" class="gridInput" style="width:100%;" value="<?php echo $res['visual_charger']?>">
											</td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class="bareBold">Screws:</td>
											<td>
												<input type="text" name="visual_screws" id="visual_screws" class="gridInput" style="width:100%;" value="<?php echo $res['visual_screws']?>">
											</td>
										</tr>
									</table>
								</td>
								<!-- RIGHT SIDE -->
								 <td width=50% valign=top>
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tr>											
											<tr>
												<td width="40%" class="bareBold">Camera:</td>
												<td>
													<input type="text" name="visual_camera" id="visual_camera" class="gridInput" style="width:100%;" value="<?php echo $res['visual_camera']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">Charging:</td>
												<td>
													<input type="text" name="visual_charging" id="visual_charging" class="gridInput" style="width:100%;" value="<?php echo $res['visual_charging']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">Speaker:</td>
												<td>
													<input type="text" name="visual_speaker" id="visual_speaker" class="gridInput" style="width:100%;" value="<?php echo $res['visual_speaker']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">Wi-Fi:</td>
												<td>
													<input type="text" name="visual_wifi" id="visual_wifi" class="gridInput" style="width:100%;" value="<?php echo $res['visual_wifi']?>">
												</td>
											</tr>
										</tr>
									</table>
								</td>
							</tr>
						</table>

					</fieldset>
				</td>
				<!-- COLUMN 3 -->
				<td width=50% valign=top>
					<fieldset class="unit-details-fieldset">
    					<legend>Laptop / PC Visual Details</legend>
							<table width="100%" cellpadding="4" cellspacing="8" border="0">
								<tr>
									<!-- LAPTOP / PC UNIT DETAILS LEFT SIDE-->
									<td width="50%" valign="top">
										<table width="100%" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td width="40%" class="bareBold">Keyboard:</td>
												<td>
													<input type="text" name="visual_keyboard" id="visual_keyboard" class="gridInput" style="width:100%;" value="<?php echo $res['visual_keyboard']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">BIOS Password:</td>
												<td>
													<input type="text" name="visual_bios" id="visual_bios" class="gridInput" style="width:100%;" value="<?php echo $res['visual_bios']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">BitLocker:</td>
												<td>
													<input type="text" name="visual_bitlocker" id="visual_bitlocker" class="gridInput" style="width:100%;" value="<?php echo $res['visual_bitlocker']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">RAM:</td>
												<td>
													<input type="text" name="visual_ram" id="visual_ram" class="gridInput" style="width:100%;" value="<?php echo $res['visual_ram']?>">
												</td>
											</tr>
										</table>
									</td>
									<td width="50%" valign="top">
										<table width="100%" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td class="bareBold">HDD/SSD:</td>
												<td>
													<input type="text" name="visual_storage" id="visual_storage" class="gridInput" style="width:100%;" value="<?php echo $res['visual_storage']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">Touchpad:</td>
												<td>
													<input type="text" name="visual_touchpad" id="visual_touchpad" class="gridInput" style="width:100%;" value="<?php echo $res['visual_touchpad']?>">
												</td>
											</tr>
											<tr><td height=2></td></tr>
											<tr>
												<td class="bareBold">HDMI/VGA:</td>
												<td>
													<input type="text" name="visual_hdmi" id="visual_hdmi" class="gridInput" style="width:100%;" value="<?php echo $res['visual_hdmi']?>">
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
					</fieldset>
				</td>
			</tr>
		</table>

		<table class="cell-border" id="details">
			<thead>
				<tr>
					<th></th>
					<th width=12%>ITEM CODE</th>
					<th width=10%>BRAND</th>
					<th >DESCRIPTION</th>
					<th width=10%>QTY</th>
					<th width=10%>PRICE</th>
					<th width=10%>AMT. DUE</th>
				</tr>
			</thead>
		</table>

		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Unit History: <br/>
					<textarea rows=4 type="text" id="remarks" style="width:90%;"><?php echo $res['remarks']; ?></textarea> <br />
					Action Taken: <br/>
					<textarea rows=4 type="text" id="action_taken" style="width:90%;"><?php echo $res['action_taken']; ?></textarea> <br />
				</td>
				<td align=right width=50% valign=top>
					Total Parts Cost : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type=text name="pgrandTotal" id="pgrandTotal" value="<?php echo number_format($res['pamount'],2); ?>" readonly> <br />
					Service Cost : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type="text" name="laborTotal" id="laborTotal" value="<?php echo number_format($res['labor'], 2, '.', ''); ?>" oninput="computeTransactionTotal()" onchange="saveLaborTotal()" onblur="console.log('Service Cost field lost focus')"> <br/>					
					Transaction Total : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type=text name="grandTotal" id="grandTotal" value="<?php echo number_format($res['amount'],2); ?>" readonly><br />
					Total Paid / Downpayment : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type="text" id="totalPaid" value="<?php echo number_format($totalPaid, 2); ?>" readonly><br />
					Remaining Balance : &nbsp;&nbsp;<input style="width:200px;text-align:right;font-weight:bold;" type="text" id="remainingBalance" value="<?php echo number_format(isset($res['remaining_balance']) ? $res['remaining_balance'] : 0,2); ?>"  readonly>
				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active' || $status == 'Received' || $status == 'On-going Repair') { ?>
						<a href="#" class="topClickers" onClick="javascript:addItem();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Parts Used</a>&nbsp;
						<!--a href="#" class="topClickers" onClick="javascript:updateItem();"><img src="images/icons/discount-icon.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Apply Line Discount</a-->&nbsp;
						<a href="#" class="topClickers" onClick="javascript:deleteItem();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Item</a>
					<?php } ?>
				</td>
			</tr>
		</table>	
	</form>
</div>
<div id="queueingslip" style="display: none;"></div>
</body>
</html>