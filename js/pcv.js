function saveHeader() {
	var msg = "";
	var amt = parent.stripComma($("#amount").val());
	
	
	if($("#payee_name").val() == "") { msg = msg + "- Invalid Payee Name<br/>"; }
	if($("#payee_code").val() == "") { msg = msg + "- Invalid Payee<br/>"; }
	if(isNaN(amt) == true) { msg = msg + "- Invalid Amount<br/>"; }
	if($("#date_approved").val() == "") { msg = msg + "- Invalid Aprroved Date<br/>"; }
	if($("#approved_by").val() == "") { msg = msg + "- Person who authorized the petty cash voucher must be identified<br/>"; }
	
	if(msg!="") {
		parent.sendErrorMessage(msg);
	} else {
		var url = $("#xform").serialize();
		    url = url + "&mod=saveHeader&sid="+Math.random()+"";
		
		$.ajax({
			url:"pcv.datacontrol.php",
			type:"POST",
			data: url,
			success: function () { parent.popSaver(); }
		}); 
	}
}

function encodeInvoices() {
	
	$.post("pcv.datacontrol.php", { mod: "retrieveLiquidation", pcv_no: $("#pcv_no").val() }, function(data) {
		
		if(data) {
			$("#supplier").val(data['payee_name']);
			$("#address").html(data['payee_address']);
			$("#tin").val(data['payee_tin']);
			$("#invoice").val(data['invoice_no']);
			$("#invoice_date").val(data['idate']);
			$("#liquidation_date").val(data['ldate']);
			$("#invoice_amount").val(data['amt']);
			$("#other_amount").val(data['oamt']);
			$("#return_amount").val(data['camt']);
			$("#liquidation_particulars").html(data['particulars']);
		}
		
		$("#myInvoices").dialog({
			title: "Liquidation Details", 
			width: 420,  
			resizable: false, 
				buttons: {
				"Save Changes":  function() { saveLiquidation(); },
				"Clear Liquidation": function() { clearLiquidation(); }
			}
		});
	
	},"json");
		
}

function viewLiquidation() {
	
	$.post("pcv.datacontrol.php", { mod: "retrieveLiquidation", pcv_no: $("#pcv_no").val() }, function(data) {
		
		if(data) {
			$("#supplier").val(data['payee_name']);
			$("#address").html(data['payee_address']);
			$("#tin").val(data['payee_tin']);
			$("#invoice").val(data['invoice_no']);
			$("#invoice_date").val(data['idate']);
			$("#liquidation_date").val(data['ldate']);
			$("#invoice_amount").val(data['amt']);
			$("#other_amount").val(data['oamt']);
			$("#return_amount").val(data['camt']);
			$("#liquidation_particulars").html(data['particulars']);
		}
		
		$("#myInvoices").dialog({
			title: "Liquidation Details", 
			width: 420,  
			resizable: false
		});
	
	},"json");
		
}

function saveLiquidation() {
	var msg = "";
	if($("#invoice_amount").val() != '') {
		var amount = parent.stripComma($("#invoice_amount").val());
		if(isNaN(amount) == true) { msg = msg + "- Invalid Amount!<br/>"; }
	} else { msg = msg + "- Invalid Amount!<br/>"; }
	
	if($("#supplier").val() == '') { msg = msg + "- Invalid Supplier or Payee!<br/>"; }
	if($("#liquidation_particulars").val() == '') { msg = msg + "- Please specify liquidation particulars<br/>"; }
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		var url = $("#frmLiquidation").serialize();
		    url = url + "&mod=saveLiquidation&sid="+Math.random()+"&pcv_no="+$("#pcv_no").val();
		
		$.ajax({
			url:"pcv.datacontrol.php",
			type:"POST",
			data: url,
			success: function () { alert("Liquidation Successfully Saved!"); parent.viewPCV($("#pcv_no").val());}
		}); 				
			
	}
	
}

function clearLiquidation() {
	if(confirm("Are you sure you want to clear or remove liquidation made for this PCV?") == true) {
		$.post("pcv.datacontrol.php", { mod: "clearLiquidation", pcv_no: $("#pcv_no").val(), sid: Math.random() }, function() {
			$('#frmLiquidation')[0].reset();
			$('#particulars').html('');
			$('#address').html('');
		});
	}
}

function finalize() {
	if(confirm("Are you sure you want to post & finalize this document?") == true) {
		$.post("pcv.datacontrol.php", { mod: "finalize", pcv_no: $("#pcv_no").val(), sid: Math.random() }, function() { alert("Document Successfully Posted & Finalized!"); parent.showPCV(); });
	}
}

function postPCV(pcvNo) {
	var amount = parseFloat(parent.stripComma($("#amount").val()));
	var amount2 = parseFloat(parent.stripComma($("#amount_liquidated").val()));
	
	if(amount != amount2) {
		parent.sendErrorMessage("It appears that this PCV is improperly liquidated. Please check liquidation details and try again.");
	} else {
		if(confirm("Are you sure want to post this PCV to the General Ledger?") == true) {
			$.post("pcv.datacontrol.php", { mod: "post", pcvNo: pcvNo, pcvAcct: $("#pcv_type").val(), sid: Math.random() }, function() {
				alert("PCV Successfully Posted to the General Ledger");
				parent.viewPCV($("#pcv_no").val());
			});
		}
	}
	
}

function unpostVoucher(pcvNo) {
	if(confirm("Are you sure you want to unpost this voucher from the General Ledger?") == true) {
		$.post("pcv.datacontrol.php", { mod: "unpost", pcvNo: pcvNo, sid: Math.random() }, function() {
			alert("PCV Successfully Unposted to the General Ledger");
			parent.viewPCV($("#pcv_no").val());
		});
	}
}

function reopen() {
	if(confirm("Are you sure want to set this document to \"Active\" status?") == true) {
		$.post("pcv.datacontrol.php", { mod: "reopen", pcv_no: $("#pcv_no").val(), sid: Math.random() }, function() { parent.viewPCV($("#pcv_no").val()); });
	}
}

function cancel() {
	if(confirm("Are you sure want to cancel this document?") == true) {
		$.post("pcv.datacontrol.php", { mod: "cancel", pcv_no: $("#pcv_no").val(), sid: Math.random() }, function() { parent.viewPCV($("#pcv_no").val()); });
	}
}