<div id="inventoryReportMain" style="display: none;">
	<div style="padding: 20px;">
		<div style="height: 10px;"></div>
		<div class="fileObjects" onclick="parent.showIBook()"><a href="#"><img src="images/icons/inventory.png" width=60 height=60 /><br/><br/>Inventory Book</a></div>
		div class="fileObjects" onclick="inventoryDistribution();"><a href="#"><img src="images/icons/inventory2.png" width=60 height=60 /><br/><br/>Inventory Distribution</a></div>
		<div class="fileObjects" onclick="showDRS();"><a href="#"><img src="images/icons/dr.png" width=60 height=60 /><br/><br/>Stocks Transfer Summary</a></div
		<div class="fileObjects" onclick="showPOSummary();"><a href="#"><img src="images/icons/dr.png" width=60 height=60 /><br/><br/>Purchase Order Summary</a></div>
		<div class="fileObjects" onclick="showRRS();"><a href="#"><img src="images/icons/dscr.png" width=60 height=60 /><br/><br/>RR Summary</a></div>
		<div class="fileObjects" onclick="showSGW();"><a href="#"><img src="images/icons/widraw.png" width=60 height=60 /><br/><br/>Summary of Goods Withdrawn</a></div>
		<div class="fileObjects" onclick="showSRRS();"><a href="#"><img src="images/icons/trading.png" width=60 height=60 /><br/><br/>Summary of Goods Returned</a></div>
		<div class="fileObjects" onclick="showFASummary();"><a href="#"><img src="images/icons/fasset.png" width=60 height=60 /><br/><br/>Fixed Asset Summary</a></div>
	</div>
</div>
<div id="posummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100%>
		<tr>
			<td width=40%><span class="spandix-l">Supplier :</span></td>
			<td>
				<select id="pos_cid" style="width: 80%; font-size: 11px;" class="gridInput"/>
					<option value="">- All Suppliers -</option>
					<?php
						$_b = $o->dbquery("SELECT DISTINCT supplier, LPAD(supplier,4,0) AS cid, supplier_name FROM po_header where supplier != 0 ORDER BY supplier_name ASC;");
						while(list($_zz,$_za,$_zb) = $_b->fetch_array()) {
							echo "<option value='$_zz'>($_za) $_zb</option>";
						}
						unset($_b);
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="pos_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="pos_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">PO Status</span></td>
			<td>
				<select  id="pos_status" class="gridInput" style="width: 80%; font-size: 11px;">
					<option value=''>- ALL -</option>
					<option value='1'>Fully Delivered</option>
					<option value='2'>Partially Delivered</option>
					<option value='3'>Undelivered</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Item Code or Description: </span></td>
			<td>
				<input type="text" id="pos_item" class="gridInput" style="width: 80%;"/>
			</td>
		</tr>
	</table>
</div>
<div id="rrsummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100%>
		<tr>
			<td width=40%><span class="spandix-l">Supplier :</span></td>
			<td>
				<select id="rrs_cid" style="width: 80%; font-size: 11px;" class="gridInput"/>
					<option value="">- All Suppliers -</option>
					<?php
						$_b = $o->dbquery("SELECT DISTINCT supplier, LPAD(supplier,4,0) AS cid, supplier_name FROM rr_header where  branch = '$_SESSION[branchid]' and supplier != 0 ORDER BY supplier_name ASC;");
						while(list($_zz,$_za,$_zb) = $_b->fetch_array()) {
							echo "<option value='$_zz'>($_za) $_zb</option>";
						}
						unset($_b);
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="rrs_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="rrs_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Item Code or Description: </span></td>
			<td>
				<input type="text" id="rrs_item" class="gridInput" style="width: 80%;"/>
			</td>
		</tr>
	</table>
</div>
<div id="sgwsummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% >
		<tr>
			<td width=40%><span class="spandix-l">Cost Center :</span></td>
			<td>
				<select id="sgw_costcenter" style="width: 80%; font-size: 11px;" class="gridInput" onchange="javascript: if(this.value == '150') { $('#sgw_ppp').attr({ disabled: false }); } else { $('#sgw_ppp').attr({ disabled: true }); $('#sgw_ppp').val(''); }">
					<option value="">- All -</option>
					<?php $cc = $o->dbquery("SELECT a.unitcode,a.costcenter FROM options_costcenter a order by a.costcenter;");
						$poption = "<option value = ''>- NA -</option>";
						while(list($unitcode,$costcenter) = $cc->fetch_array()) {
							echo "<option value = '$unitcode'>$costcenter</option>";
						}
				
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">PPP Site (If Applicable) :</span></td>
			<td align=left>
				<select class="gridInput" style="width:80%;" name="sgw_ppp" id="sgw_ppp" disabled>
					<option value=''>- All -</option>
					<?php
						$pppQuery = $o->dbquery("select ppp_id, ppp_name from options_ppp order by ppp_name;");
						while($pppRow = $pppQuery->fetch_array()) {
							echo "<option value='$pppRow[0]'>$pppRow[1]</option>";
						}
					?>
				</select>
			</td>				
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Clinic (If Applicable) :</span></td>
			<td align=left>
				<select class="gridInput" style="width:80%;" name="sgw_clinic" id="sgw_clinic">
					<option value=''>- All -</option>
					<?php
						$clinicQuery = $o->dbquery("select id, clinic from options_clinics order by clinic;");
						while($clinicRow = $clinicQuery->fetch_array()) {
							echo "<option value='$clinicRow[0]'>$clinicRow[1]</option>";
						}
					?>
				</select>
			</td>				
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="sgw_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="sgw_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Purpose Of Withdrawal :</span></td>
			<td align=left>
				<select class="gridInput" style="width:80%;" name="sgw_type" id="sgw_type" >
					<option value=''>- All -</option>
					<?php
						$sgwtquery = $o->dbquery("select id, `type` from options_wtype order by `type`;");
						while(list($sgw_t,$sgw_d) = $sgwtquery->fetch_array()) {
							echo "<option value='$sgw_t'>$sgw_d</option>";
						}
					?>
				</select>
			</td>				
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Code or Description: </span></td>
			<td>
				<input type="text" id="sgw_item" class="gridInput" style="width: 80%;"/>
			</td>
		</tr>
	</table>
</div>
<div id="srrsummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=40%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="srrs_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="srrs_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Item Code or Description: </span></td>
			<td>
				<input type="text" id="srrs_item" class="gridInput" style="width: 80%;"/>
			</td>
		</tr>
	</table>
</div>
<div id="preIdist" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=40%><span class="spandix-l">Inventory Group :</span></td>
			<td>
				<select id="idist_group" style="width: 80%; font-size: 11px;" class="gridInput" onchange="javascript: getCategories(this.value,'idist_category');">
					<option value="">- All Groups -</option>
					<?php
						$cc = $o->dbquery("SELECT mid,mgroup FROM options_mgroup order by mgroup;");
						while(list($g,$gd) = $cc->fetch_array()) {
							echo "<option value='$g'>$gd</option>";
						}
						unset($cc);
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Category :</span></td>
			<td>
				<select id="idist_category" style="width: 80%; font-size: 11px;" class="gridInput"/>
					<option value="">- All Categories -</option>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Code or Description :</span></td>
			<td>
				<input type="text" id = "idist_item" class="gridInput" style="width: 80%; font-size: 11px">
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Date As Of :</span></td>
			<td>
				<input type="text" id = "idist_date" class="gridInput" style="width: 80%; font-size: 11px" value="<?php echo date('m/d/Y'); ?>">
			</td>
		</tr>
	</table>
</div>
<div id="fasummary" style="display: none;">

	<table width=100% cellpadding=0 cellspacing=0>
		<tr>
			<td width=40% class="spandix-l">Asset Category :</td>
			<td>
				<select name="fa_category" id="fa_category" class="nInput" style="width:80%; font-size: 11px;">
					<option value="">- All Categories -</option>
					<?php 
						$c = $o->dbquery("select id, category from fa_category;");
						while(list($a,$b) = $c->fetch_array()) {
							echo "<option value='$a'>$b</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr>
			<td width=40% class="spandix-l">Cost Center :</td>
			<td>
				<select name="fa_costcenter" id="fa_costcenter" class="nInput" style="width:80%; font-size: 11px;">
					<option value="">- All Cost Centers -</option>
					<?php 
						$c = $o->dbquery("select unitcode, costcenter from options_costcenter;");
						while(list($a,$b) = $c->fetch_array()) {
							echo "<option value='$a'>$b</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr>
			<td width=40% class="spandix-l">Date Acquired :</td>
			<td><input type="text" class="gridInput" name="fa_dtf" id="fa_dtf" style="width: 80%;"></td>
		</tr>
		<tr><td height=2></td></tr>
		<tr>
			<td width=40% class="spandix-l"></td>
			<td><input type="text" class="gridInput" name="fa_dt2" id="fa_dt2" style="width: 80%;"></td>
		</tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="exportFASUmmary();" class="buttonding" style="font-size: 11px;"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export to Excel</button>
			</td>
		</tr>
	</table>

</div>
<div id="inventorybook" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Type :</span></td>
			<td>
				<select id="ibook_group" style="width: 90%; font-size: 11px;" class="gridInput">
				<option value="">- All Inventory Items -</option>
				<?php
					$iut = $o->dbquery("select `mid`,mgroup from options_mgroup;");
					while(list($t,$tt) = $iut->fetch_array()) {
						echo "<option value='$t'>$tt</option>";
					}
				?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="ibook_dtf" class="gridInput" style="width: 90%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="ibook_dt2" class="gridInput" style="width: 90%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processInventory();" class="buttonding" style="font-size: 11px;"><img src="images/icons/processraw.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View Inventory</button>
				<button onClick="exportInventoryNow();" class="buttonding" style="font-size: 11px;"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export to Excel</button>
			</td>
		</tr>
	</table>
</div>