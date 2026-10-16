function showLoaderMessage() {
	$("#loaderMessage").dialog({ width: 400, height: 150, closable: false, modal: true,  open: function(event, ui) {
        $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    }});
}

function checkLockDate(el,myDate,prevDate) {
	$.post("src/sjerp.php", { mod: "checkDateLock", myDate: myDate, sid: Math.random() }, function(ret) {
		if(ret != "Ok") {
			parent.sendErrorMessage("Unable to change document as the period you have specified appears to already have been marked as locked!");
			document.getElementById(el).value = prevDate;
		}
	},"html");
}

function itemLookup(inputString,el) {
	$("#isSearch").val(1);
	if(inputString.length == 0) {
		$('#suggestions').hide();
	} else {
		var op = $("#"+el+"").offset();
		$.post("itemlookupcost.php", {queryString: ""+inputString+"" }, function(data){
		if(data.length > 0) {
			$('#suggestions').css({top: op.top+20, left: op.left, width: '500px'});
			$('#suggestions').show();
			$('#autoSuggestionsList').html(data);
		} else { $("#suggestions").hide(); }
		});
	}
}

function pickItem(icode,idesc,cost,unit) {
	$("#product_code").val(icode);
	$("#description").val(decodeURIComponent(idesc));
	$("#unit_price").val(parent.kSeparator(cost));
	$("#unit").val(unit);
	$("#qty").focus();
}

function computeAmount() {
	var price = parseFloat(parent.stripComma($("#unit_price").val()));
	var qty = parseFloat(parent.stripComma($("#qty").val()));

	if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
		parent.sendErrorMessage("Invalid Quantity or Price. Please check your entries and try again...")
	} else {
		var amt = price * qty;
			amt = amt.toFixed(2);
		$("#amount").val(parent.kSeparator(amt));
	}
}

function getTotals() {
	$.post("rr.datacontrol.php", { mod: "getTotals", rr_no: $("#rr_no").val(), sid: Math.random() }, function(data) {
		$("#total_amount").val(data[0]);
	},"json");
}

function saveRRHeader() {
	var msg = "";
	if($("#cSelected").val() == "N") { msg = msg + "- Invalid Supplier Details.<br/>"; }
	if($("#invoice_no").val() == "") { msg = msg + "- Please Specify Purchase Reference (Invoice, DR, etc.) for this Receivng Report.<br/>"; }
	if($("#invoice_date").val() == "") { msg = msg + "- Please Specify Purchase Reference Date for Receiving Report<br/>"; }
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("rr.datacontrol.php", { mod: "saveHeader", rr_no: $("#rr_no").val(), rr_date: $("#rr_date").val(), cid: $("#customer_id").val(), cname: $("#customer_name").val(), addr: $("#cust_address").val(), recby: $("#received_by").val(), ino: $("#invoice_no").val(), idate: $("#invoice_date").val(), remarks: $("#remarks").val(), sid: Math.random() },function(data){
			parent.popSaver();
		});
	}
}

function checkDuplicateInvoice(val) {
	$.post("rr.datacontrol.php", {mod: "checkDuplicateInvoice", ref_no: val, cust: $("#customer_id").val(), sid: Math.random() }, function(data) {
		if(data['err_msg'] == "DUP") {
			parent.sendErrorMessage("Duplicate Reference No. Detected for this Supplier. <br/><br/><b>RR No.:</b> "+data['rr_no'] + "<br/><b>RR Date: </b>" +data['rr_date']);
			$("#invoice_no").val('');
		}
	},"json");
}

function downloadPO() {
	if($("#customer_id").val() == "") {
		parent.sendErrorMessage("Please select supplier first before downloading unserved Purchase Orders.");
	} else {
		$.post("rr.datacontrol.php", { mod: "getPOS", rr_no: $("#rr_no").val(), cid: $("#customer_id").val(), sid: Math.random() }, function(data) {
			if(data.length > 0) {
				$("#invoiceAttachment").html(data);
				$("#invoiceAttachment").dialog({title: "Unserved Purchase Orders", width: 640, height: 360, resizable: false, modal: true, buttons: {
						"Upload Purchase Order":  function() { loadPO(); },
						"Close Window": function() { $(this).dialog("close"); }
					}
				 }).dialogExtend({
					"closable" : true,
					"maximizable" : false,
					"minimizable" : true
				});
			} else {
				parent.sendErrorMessage("Unable to find any Purchase Order issued to this supplier. Make sure you have specified the correct supplier code...");
			}
		});
	}
}

function tagPO(el,val) {
	var obj = document.getElementById(el);
	var myURL;
	if(obj.checked == true) { var push = "Y"; } else { var push = "N"; }
	$.post("rr.datacontrol.php", { mod: "tagPO", push: push, val: val, sid: Math.random() });
}

function loadPO() {
	$.post("rr.datacontrol.php", { mod: "loadPO", rr_no: $("#rr_no").val(), sid: Math.random() }, function(data) {
		if(data.length > 0) {
			$("#details").html(data);
			getTotals();
			$("#invoiceAttachment").dialog("close");
		} else { parent.sendErrorMessage("There nothing to upload. Please make sure you have selected purchases from the list given..."); }
	});
}

function addDetails() {
	var msg = "";
	var po_no = $("#po_no").val();
	var po_date = $("#po_date").val();
	var icode = $("#product_code").val();
	var idesc = $("#description").val();
	var qty = parseFloat(parent.stripComma($("#qty").val()));
	var price = parseFloat(parent.stripComma($("#unit_price").val()));
	var amount = parseFloat(parent.stripComma($("#amount").val()));

	if(icode == "") { msg = msg + "- Product Code not specified<br/>"; }
	if(idesc == "") { msg = msg + "- Product Description not specified<br/>"; }
	if(isNaN(qty) == true || qty == "") { msg = msg + "- Invalid Quantity<br/>"; }
	if(isNaN(price) == true || price == "") { msg = msg + "- Invalid Unit Price<br/>"; }
	if(isNaN(amount) == true || amount == "") { msg = msg + "- Invalid Amount<br/>"; }
	
	if(msg != "") {
		parent.sendErrorMessage("Unable to continue due to the following error(s): <br/><br/>"+msg+"");
	} else {
		$.post("rr.datacontrol.php", { mod: "insertDetail", rr_no: $("#rr_no").val(), po_no: po_no, po_date: po_date, icode: icode, desc: idesc, qty: qty, unit: $("#unit").val(), price: price, amount: amount, sid: Math.random() }, function(data) {
			$("#details").html(data)
			$("#product_code").val('');
			$("#description").val('');
			$("#autdescription").val('');
			$("#unit_price").val('');
			$("#unit").val('');
			$("#qty").val('');
			$("#amount").val('');
			$("#description").focus();
			getTotals();
		},"html");
	}
}

function updateQty(val,rr_no,lineid,price,qty,po_no,poQty,poTdld) {
	var msg = "";
	var txtobj = 'qty['+lineid+']';
	var objamt1 = 'amt['+lineid+']';
	if(isNaN(val) == true || parseFloat(val) < 1) { msg = msg + "- You have specified an invalid qty<br/>"; }
	
	var poQty = parseFloat(poQty);
	var poTdld = parseFloat(poTdld);
	var val = parseFloat(val);
	var qty = parseFloat(qty);
	var curDld = parseFloat(val+poTdld);
	
		
	if(curDld > poQty && po_no!='') { msg = msg + "- You have specified a quantity greater than what was originally ordered. Please refer to Accounting to resolve this issue.<br/>"; document.getElementById(txtobj).value = qty; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("rr.datacontrol.php", { mod: "usabQty", lid: lineid, val: val, price: price, rr_no: rr_no, sid: Math.random() }, function(data) {
			document.getElementById(objamt1).innerHTML = data['amt'];
			$("#total_amount").val(data['total']);
		},"json");
	}
}

function printRR(rr_no,uid) {
	$.post("rr.datacontrol.php", { mod: "check4print", rr_no: rr_no, sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Receiving Report?") == true) {
				$.post("rr.datacontrol.php", { mod: "finalizeRR", rr_no: rr_no, sid: Math.random() }, function() {
					parent.viewRR(rr_no);
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Receiving Report..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved what entries you've made..."); break;
			}
		}
	},"html");
}

function reopenRR(rr_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("rr.datacontrol.php", { mod: "reopenRR", rr_no: rr_no, sid: Math.random() }, function() {
			parent.viewRR(rr_no);
		});
	}
}

function cancelRR(rr_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("rr.datacontrol.php", { mod: "cancel", rr_no: rr_no, sid: Math.random() }, function(){
			alert("Receving Report Successfully Cancelled!");
			parent.showRRList();
		});
	}
}

function reuseRR(rr_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("rr.datacontrol.php", { mod: "reopenRR", rr_no: rr_no, sid: Math.random() }, function(){
			parent.viewRR(rr_no);
		});
	}
}

function reprintRR(rr_no,uid) {
	//window.open("print/rr.print.php?rr_no="+rr_no+"&sid="+Math.random()+"&user="+uid+"&reprint=Y","Receiving Report","location=1,status=1,scrollbars=1,width=640,height=720");
	parent.printRR(rr_no,uid);
}