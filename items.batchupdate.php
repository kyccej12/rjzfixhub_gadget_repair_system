<?php
	include("includes/dbUSE.php");
	session_start();
	
	
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="src/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>
	/*
	$(document).ready(function(){
		$('#supplier').autocomplete({
			source:'suggestContacts.php', 
			minLength:3,
			select: function(event,ui) {
				$("#supplier").val(ui.item.cid);
				$("#supplier_name").val(decodeURIComponent(ui.item.cname));
			}
		});
	});
	*/
	$(document).ready(function(){
		getData();
	});
	
	var myFunction;

	function send(){
		myFunction = setTimeout(function(){ 
			$.post("batchupdate.datacontrol.php",{mod:"search2", text : $("#inputSearch").val() },function(data){ $("#itemlist").html(data);  },"html");
		},1500);
	}

	function abort(){
		clearTimeout(myFunction); 
	}

	function getData(){
		$.post("batchupdate.datacontrol.php",{mod:"getData" , igroup : $("#item_group").val() , isgroup : $("#item_sgroup").val() ,itype : $("#item_itype").val() , text : $("#inputSearch").val() },function(data){ $("#itemlist").html(data);  },"html");
	}
	
	function retrieveSubgroups(grp){
		$.post("batchupdate.datacontrol.php",{mod:"getSubGroup", grp:grp},function(data){ $("#item_sgroup").html(data); },"html");
		getData();
	}

	function retrieveItemType(sgrp){
		$.post("batchupdate.datacontrol.php",{mod:"getItemType", grp:sgrp},function(data){ $("#item_sgroup").html(data); },"html");
		getData();
	}

	function tagItem(value){

		var obj = document.getElementById(value);
			var myURL;
			if(obj.checked == true) { var push = "Y"; } else { var push = "N"; }
			$.post("batchupdate.datacontrol.php", { mod: "tagItem", push: push, val: value, sid: Math.random() },function(data){  });
	}

	function batchUpdateplevel(){
		var pl; var walk_in; var a;  var b;  var c;  var aaa;  var bbb;  var ccc;  var proj;  var ox; var kim; var can; var clen;
		if( $("#chexkbox_pricePL").is(":checked") == true){  pl =  $("#chexkbox_pricePL").val() } 
		if( $("#chexkbox_priceWI").is(":checked") == true){  walk_in =  $("#chexkbox_priceWI").val() } 
		if( $("#chexkbox_priceA").is(":checked") == true){  a =  $("#chexkbox_priceA").val() } 
		if( $("#chexkbox_priceB").is(":checked") == true){  b =  $("#chexkbox_priceB").val() } 
		if( $("#chexkbox_priceC").is(":checked") == true){  c =  $("#chexkbox_priceC").val() } 
		if( $("#chexkbox_priceAAA").is(":checked") == true){  aaa =  $("#chexkbox_priceAAA").val() } 
		if( $("#chexkbox_priceBBB").is(":checked") == true){  bbb =  $("#chexkbox_priceBBB").val() } 
		if( $("#chexkbox_priceCCC").is(":checked") == true){  ccc =  $("#chexkbox_priceCCC").val() } 
		if( $("#chexkbox_pricePr").is(":checked") == true){  proj =  $("#chexkbox_pricePr").val() } 
		if( $("#chexkbox_priceOX").is(":checked") == true){  ox =  $("#chexkbox_priceOX").val() } 
		
		if( $("#chexkbox_priceKIM").is(":checked") == true){  kim =  $("#chexkbox_priceKIM").val() } 
		if( $("#chexkbox_priceCAN").is(":checked") == true){  can =  $("#chexkbox_priceCAN").val() } 
		if( $("#chexkbox_priceCLEN").is(":checked") == true){  clen =  $("#chexkbox_priceCLEN").val() } 
		//if( $("#chexkbox_priceWI").checked == true){  walk_in =  $("#chexkbox_priceWI").val() } 
		
		var r = confirm("Are you sure you want to update the selected records?");
		if(r==true){
			$.post("batchupdate.datacontrol.php",{ mod : "updateLevel", percent : $("#percent").val(),
												pl : pl , 
												walk_in:  walk_in , 
												a : a,
												b : b,
												c :c,
												aaa : aaa,
												bbb : bbb,
												ccc : ccc, 
												proj : proj,
												ox : ox,
												kim : kim,
												can : can,
												clen : clen
											 },function(data){   
				getData();
			});
		}
		
	}

	function clearCheckBox(){
		$.post("batchupdate.datacontrol.php",{ mod : "clear" },function(data){
			getData();
		});
	}

	function clearAmount(){
		$.post("batchupdate.datacontrol.php",{ mod : "reset" },function(data){
			var pl; var walk_in; var a;  var b;  var c;  var aaa;  var bbb;  var ccc;  var proj;  var ox;
			if( $("#chexkbox_pricePL").is(":checked") == true){  pl =  $("#chexkbox_pricePL").val() } 
			if( $("#chexkbox_priceWI").is(":checked") == true){  walk_in =  $("#chexkbox_priceWI").val() } 
			if( $("#chexkbox_priceA").is(":checked") == true){  a =  $("#chexkbox_priceA").val() } 
			if( $("#chexkbox_priceB").is(":checked") == true){  b =  $("#chexkbox_priceB").val() } 
			if( $("#chexkbox_priceC").is(":checked") == true){  c =  $("#chexkbox_priceC").val() } 
			if( $("#chexkbox_priceAAA").is(":checked") == true){  aaa =  $("#chexkbox_priceAAA").val() } 
			if( $("#chexkbox_priceBBB").is(":checked") == true){  bbb =  $("#chexkbox_priceBBB").val() } 
			if( $("#chexkbox_priceCCC").is(":checked") == true){  ccc =  $("#chexkbox_priceCCC").val() } 
			if( $("#chexkbox_pricePr").is(":checked") == true){  proj =  $("#chexkbox_pricePr").val() } 
			if( $("#chexkbox_priceOX").is(":checked") == true){  ox =  $("#chexkbox_priceOX").val() } 
			//if( $("#chexkbox_priceWI").checked == true){  walk_in =  $("#chexkbox_priceWI").val() } 
			$.post("batchupdate.datacontrol.php",{ mod : "reset", price_level : $("#price_level").val(), percent : $("#percent").val(),
													pl : pl , 
													walk_in:  walk_in , 
													a : a,
													b : b,
													c :c,
													aaa : aaa,
													bbb : bbb,
													ccc : ccc, 
													proj : proj,
													ox : ox
												 },function(data){   
				getData();
				});
		});
	}


</script>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<form name="merchandise" id="merchandise">
</form>
<form name="changeModPage" id="changeModPage" >
	<table width="100%" border="0" cellspacing="0" cellpadding="0"  >
		<tr>
			<td width="15%" class="spandix-l">
				Product Category :
			</td>
			<td width="30%">
				<select name="item_category" id="item_category" style="width: 160px;" class="nInput" onchange="javascript: if(this.value != 1) { getMyCode(); } else { retrieveGroups(this.value); }">
					<option value="">- Select Category -</option>
					<?php
						$mit = mysql_query("select mid,mgroup from options_mgroup;");
						while(list($o,$oo) = mysql_fetch_array($mit)) {
							echo "<option value='$o' ";
								if($res['category'] == $o) { echo "selected"; }
							echo ">$oo</option>";
						}
					?>
				</select>
			</td>
			<td width="55%"  class="spandix-l" > Percent : &nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="percent" id="percent" style="width: 54pt; text-align: right;" value='' class="nInput"> % </td>
		</tr>
		<tr> <td colspan=3 height='4'></td> </tr>
		<tr>
			<td width=15% class="spandix-l">Product Group :</td>
			<td width=30%>
				<select name="item_group" id="item_group" style="width: 160px;" class="nInput" onchange="retrieveSubgroups(this.value);">
					<option value="">- Not Applicable -</option>
					<?php
						$iut = mysql_query("select `group`,group_description from options_igroup order by group_description asc;");
						while(list($t,$tt) = mysql_fetch_array($iut)) {
							echo "<option value='$t' ";
								if($res['group'] == $t) { echo "selected"; }
							echo ">$tt</option>";
						}
					?>
				</select>
			</td>
			<td width="55%" class="spandix-l" > Price Level : 
				<!--<select class="nInput" id = "price_level"> 
					<option value="price_a"> A </option> 
					<option value="price_b"> B</option> 
					<option value="price_c"> C</option> 
					<option value="price_aaa"> AAA</option>
					<option value="price_bbb"> BBB</option>
					<option value="price_ccc"> CCC</option>
					<option value="price_proj"> Project</option>
					<option value="price_ox"> OX</option>
				</select> -->
			</td>
		</tr>
		<tr> <td colspan=3 height='4'></td> </tr>
		<tr>
			<td width=15% class="spandix-l">Product Sub-group :</td>
			<td width=30%>
				<select name="item_sgroup" id="item_sgroup" style="width: 160px;" class="nInput" onchange="javascript: retrieveItemType(this.value);">
					<option value="">- Not Applicable -</option>
					<?php
						$sg = dbquery("select subgroup_id,subgroup_description from options_isgroup where parent_gid = '$res[group]' order by subgroup_description asc;");
						while($t = mysql_fetch_array($sg)) {
							echo "<option value='$t[0]' ";
							if($res['sgroup'] == $t[0]) { echo "selected"; }
							echo ">$t[1]</option>\n";
						}
					?>
				</select>
			</td>
			<td width="55%" class="spandix-l" > 
				<input type = button onclick="batchUpdateplevel()" value="Update"  Update /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<input type = button onclick="clearCheckBox()" value="Clear"  Update /> 
				<!--<input type = button onclick="clearAmount()" value="Set to 0"  Update /> -->
			
			</td>
		</tr>
		<tr> <td colspan=3 height='4'></td> </tr>
		<tr>
			<td width=15% class="spandix-l">Item Type:</td>
			<td width=30%>
				<select name="item_itype" id="item_itype" style="width: 160px;" class="nInput" onchange="javascript: getData();">
					<option value="">- Not Applicable -</option>
					<?php
						$sg = dbquery("select subgroup_id,subgroup_description from options_isgroup where parent_gid = '$res[group]' order by subgroup_description asc;");
						while($t = mysql_fetch_array($sg)) {
							echo "<option value='$t[0]' ";
							if($res['sgroup'] == $t[0]) { echo "selected"; }
							echo ">$t[1]</option>\n";
						}
					?>
				</select>
			</td>
			<td width="55%" class="spandix-l" > 
				
			</td>
		</tr>
		<tr> <td colspan=3 height='6'></td> </tr>
		<tr> <td colspan=3 ><input class="gridInput" type="text" id="inputSearch" onkeydown="abort()" onkeyup="send()" style="width: 40%;" /></td>  </tr>
		<tr>
			<td colspan=3 height='500px' top=0>
				<table  cellspacing=0 cellpadding=0 border=0 width=100% style="font-size:9pt;" >
					<tr> 
						<td align=center width="9%" style='font-size:7pt;' >Item Code</td>
						<td align=left width="10%" style='font-size:7pt;' >Stock Code</td>
						<td align=left width="15%" style='font-size:7pt;' >Description</td>
						<td align=center width="8%" style='font-size:7pt;' >Group</td>
						<td align=center width="7%" style='font-size:7pt;' >Sub Group</td>
						<td align=center width="7%" style='font-size:7pt;' >Item Type</td>
						<td align=center width="4%" style='font-size:7pt;' >Unit Cost</td>
						<td align=center width="4%" style='font-size:7pt;' >Price List</td>
						<td align=center width="4%" style='font-size:7pt;' >A</td>
						<td align=center width="4%" style='font-size:7pt;' >B</td>
						<td align=center width="4%" style='font-size:7pt;' >C</td>
						<td align=center width="4%" style='font-size:7pt;' >AAA </td>
						<td align=center width="4%" style='font-size:7pt;' >BBB</td>
						<td align=center width="4%" style='font-size:7pt;' >CCC</td>
						<td align=center width="4%" style='font-size:7pt;' >OX</td>
						<td align=center width="4%" style='font-size:7pt;' >Project</td>
						<td align=center width="4%" style='font-size:7pt;' >Walk In</td>
						<td align=center width="4%" style='font-size:7pt;' >KIM</td>
						<td align=center width="4%" style='font-size:7pt;' >CANDICE</td>
						<td align=center width="4%" style='font-size:7pt;' >CLENT</td>
						<td align=center width="4%" >&nbsp;</td>
					</tr> 
					<tr> 
						<td align=center width="9%" style='font-size:7pt;' ></td>
						<td align=left width="10%" style='font-size:7pt;' ></td>
						<td align=left width="15%" style='font-size:7pt;' ></td>
						<td align=center width="8%" style='font-size:7pt;' ></td>
						<td align=center width="7%" style='font-size:7pt;' ></td>
						<td align=center width="4%" style='font-size:7pt;' ></td>
						<td align=center width="4%" style='font-size:7pt;' ></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_pricePL' value='chexkbox_pricePL' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceA' value='chexkbox_priceA' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceB' value='chexkbox_priceB' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceC' value='chexkbox_priceC' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceAAA' value='chexkbox_priceAAA' /> </td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceBBB' value='chexkbox_priceBBB' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceCCC' value='chexkbox_priceCCC' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceOX' value='chexkbox_priceOX' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_pricePr' value='chexkbox_pricePr' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceWI' value='chexkbox_priceWI' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceKIM' value='chexkbox_priceKIM' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceCAN' value='chexkbox_priceCAN' /></td>
						<td align=center width="4%" style='font-size:7pt;' ><input type=checkbox id='chexkbox_priceCLEN' value='chexkbox_priceCLEN' /></td>
						
						<td align=center width="20px" >&nbsp;</td>
					</tr> 
					<tbody id="itemlist">

					</tbody>
				</table>
				
				<!--
				><div  style="width:99.9%;height:99.9%;overflow-y: scroll;">
			   		<table style="font-size:8pt;" id="itemlist" cellspacing=0 cellpadding=0 border=0 width=100% > </table>
			    </div>
				-
			</td>
		</tr>
	</table>
</form>
</body>
</html>
<?php mysql_close($con);