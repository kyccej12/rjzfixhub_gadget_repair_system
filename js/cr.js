/******** Collection Receipt ********/
function getTotals(trans_no) {
	$.post("cr.datacontrol.php", { mod: "getTotals", trans_no: trans_no, sid: Math.random() }, function(data) {
		var paid = parseFloat(parent.stripComma($("#amt_paid").val()));
		var discount = parseFloat(parent.stripComma($("#discount").val()));
		var net = parseFloat(data['gross']) - parseFloat(data['ewt']) - discount;
		    net = net.toFixed(2);
		$("#net").val(parent.kSeparator(net));
		$("#ewtgt").val(data['ewt']);
		$("#gross").val(parent.kSeparator(data['gross']));
		
	},"json");
}

function computeNet(discount) {
	var gross = parseFloat(parent.stripComma($("#gross").val()));
	
	if(gross > 0) {
		var disc = parseFloat(parent.stripComma(discount));
		var net = gross-disc;
		    net = net.toFixed(2);
		$("#net").val(parent.kSeparator(net));
		$.post("cr.datacontrol.php", { mod: "applyRebates", trans_no: $("#trans_no").val(), disc: disc, net: net, sid: Math.random() }, function(data) { 
			if(data == "error") { parent.sendErrorMessage("- It appears that the document has yet to be saved..."); } else { parent.popSaver; } 
		});
	} else {
		parent.sendErrorMessage("- You cannot apply any rebates or discount at this moment. Please see to it that there were already invoices applied to this collection receipt");
	}
}

function saveCRHeader() {
	var msg  = "";
	if($("#customer_id").val() == "") { msg = msg + "- Invalid or missing Customer Information.\r\n"; }
	if($("#pay_type").val() == "Check") {
		if($("#check_no").val() == "") { msg = msg + "- Check No. is required when choosing \"Check Payment\" as payment type"; }
		if($("#check_date").val() == "") { msg = msg + "- Check Date is required when choosing \"Check Payment\" as payment type"; }
	}
	if(msg != "") {
		alert(msg);
	} else {
		$.post("cr.datacontrol.php", { mod: "saveHeader", trans_no: $("#trans_no").val(), cr_no: $("#cr_no").val(), trans_ref_no: $("#trans_ref_no").val(), cr_date: $("#cr_date").val(), ccode: $("#customer_id").val(), cname: $("#customer_name").val(), address: $("#cust_address").val(), pay_type: $("#pay_type").val(), bank: $("#bank").val(), check_no: $("#check_no").val(), check_date: $("#check_date").val(), amt_paid: $("#amt_paid").val(), remarks: $("#remarks").val() }, function(data) {
			parent.popSaver();
			$("#details").html(data);
			getTotals($("#trans_no").val());
		},"html");
	}
}

function checkLockDate(el,myDate,prevDate) {
	$.post("src/sjerp.php", { mod: "checkDateLock", myDate: myDate, sid: Math.random() }, function(ret) {
		if(ret != "Ok") {
			parent.sendErrorMessage("Unable to change document as the period you have specified appears to already have been marked as locked!");
			document.getElementById(el).value = prevDate;
		}
	},"html");
}

function applyDocuments() {
	if($("#customer_id").val() == "") { 
		parent.sendErrorMessage("- Customer Information is missing or invalid"); 
	} else {
		$.post("cr.datacontrol.php", { mod: "getInvoices", trans_no: $("#trans_no").val(), cid: $("#customer_id").val(), sid: Math.random() }, function(data) { $("#balances").html(data); },"html");
		$("#applyDivs").dialog({title: "Customer Invoices", width: 400, height: 500, resizable: false });
	}
}

function selectInvoice(doc_no,ino,id8,balance,type) {
	$("#app_docno").val(doc_no);
	$("#invoice_no").val(ino);
	$("#app_doctype").val(type); 
	$("#app_docdate").val(id8);
	$("#app_balance").val(parent.kSeparator(balance));
	$("#app_balance_orig").val(balance);
	$("#app_amount").focus();
}

function comewhatmay(val) {
	var app = parseFloat(parent.kSeparator(val));
	var balance = parent.stripComma($("#app_balance").val());
		balance = parseFloat(balance);

	if(app > balance) {
		parent.sendErrorMessage("Amount specified is greater than the document's balance due...");
		$("#app_amount").val('');
	}
}

function applyNow() {
	var doc_no = $("#app_docno").val();
	var ino = $("#invoice_no").val();
	var doc_date = $("#app_docdate").val();
	var amount = parseFloat(parent.stripComma($("#app_amount").val()));
	var doc_type = $("#app_doctype").val();
	var msg = "";

	if(doc_no == "") { msg = msg + "- You have not selected any invoice yet...<br/>"; }
	if(amount == "" && isNaN(amount) == true) { msg = msg + "- Invalid amount specified...<br/>"; }
	

	if(msg != "") {
		parent.sendErrorMessage("Unable to continue due to the following error(s): <br/><br/>"+msg);
	} else {
		$.post("cr.datacontrol.php", { mod: "addInvoice", trans_no: $("#trans_no").val(), doc_no: doc_no, ino: ino, id8: $("#app_docdate").val(), ref_type: doc_type, balance: $("#app_balance").val(), amount: amount, sid: Math.random() }, function(data) {
			$("#details").html(data);
			getTotals($("#trans_no").val());
			$(document.applydocs)[0].reset();
			$.post("cr.datacontrol.php", { mod: "getInvoices", trans_no: $("#trans_no").val(), cid: $("#customer_id").val(), sid: Math.random() }, function(data) { $("#balances").html(data); },"html");
		},"html");
	}
}

function applyManual() {
	if($("#customer_id").val() == "") { 
		parent.sendErrorMessage("- Customer Information is missing or invalid"); 
	} else {
		$("#manualForm").dialog({title: "Customer Invoices (Other Reference)", width: 400, resizable: false });
	}
}

function applyManualNow() {
	var msg = "";

	/* if($("#man_docno").val() == "") { msg = msg + "- Document No. is required<br/>"; }
		if($("#man_docdate").val() == "") { msg = msg + "- Invalid Doc date<br/>"; }
	*/
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("cr.datacontrol.php", { mod: "addManual", trans_no: $("#trans_no").val(), doc_no: $("#trans_no").val(), doc_date: $("#cr_date").val(), balance: $("#man_amount").val(), amount: $("#man_amount").val(), sid: Math.random() }, function(ht) {
			$("#manualForm").dialog("close");
			$("#details").html(ht);
			getTotals($("#trans_no").val());
			$(document.applydocsManual)[0].reset();
		},"html");
	}
}

function finalizeCR(trans_no,uid) {
	
	var net = parseFloat(parent.stripComma($("#net").val()));
	var received = parseFloat(parent.stripComma($("#amt_paid").val()));
	
	if(net != received) {
		parent.sendErrorMessage("The amount you received as earlier specified does not coincide with the total amount of the reference documents applied to this Collection Receipt. Please check your entries prior to posting.");
	} else {
	
	
		if(confirm("Are you sure you want to Post & Finalize this Collection Receipt?") == true) {
			$.post("cr.datacontrol.php", { mod: "check4print", trans_no: $("#trans_no").val(), cr_date: $("#cr_date").val(), cid: $("#customer_id").val(), sid: Math.random() }, function(data) { 
				if(data == "noerror") {
					parent.viewCR(trans_no);
				} else {
					if(data == "waySulod") { parent.sendErrorMessage("Unable to print document. No details added to this Collection Receipt"); }
				}
			},"html");
		}
	}
}

function setActive(trans_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("cr.datacontrol.php", { mod: "reopenCR", trans_no: trans_no, sid: Math.random() }, function() {
			location.reload();
		});
	}
}

function cancelCR(trans_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("cr.datacontrol.php", { mod: "cancel", trans_no: trans_no,sid: Math.random() }, function(){
			alert("Collection Receipt Successfully Cancelled!");
			location.reload();
		});
	}
}

function reuseCR(trans_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("cr.datacontrol.php", { mod: "reuse", trans_no: trans_no, sid: Math.random() }, function(){
			location.reload();
		});
	}
}