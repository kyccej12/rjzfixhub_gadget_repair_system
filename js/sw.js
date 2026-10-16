
function checkLockDate(el,myDate,prevDate) {
	$.post("src/sjerp.php", { mod: "checkDateLock", myDate: myDate, sid: Math.random() }, function(ret) {
		if(ret != "Ok") {
			parent.sendErrorMessage("Unable to change document as the period you have specified appears to already have been marked as locked!");
			document.getElementById(el).value = prevDate;
		}
	},"html");
}

function computeAmount() {
	var price = parseFloat(parent.stripComma($("#unit_price").val()));
	var qty = parseFloat(parent.stripComma($("#qty").val()));

	if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
		parent.sendErrorMessage("Invalid Quantity or Price. Please check your entries and try again...")
	} else {
		var amt = price * qty;
			amt = amt.toFixed(4);
		$("#amount").val(parent.kSeparator(amt));
	}					

}

function saveSWHeader() {
	var msg = "";
	if($("#withdrawn_by").val() == "") { msg = msg + "- Please specify the person who will withdraw these items."; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("sw.datacontrol.php", { mod: "saveHeader", sw_no: $("#sw_no").val(), sw_date: $("#sw_date").val(), wby: $("#withdrawn_by").val(), ref_type: $("#ref_type").val(), by: $("#requested_by").val(), request_date: $("#request_date").val(), remarks: $("#remarks").val(), sid: Math.random() }, function(data) { parent.popSaver(); },"html");
	}
}

function addDetails() {
	var msg = "";
	var sw_no = $("#sw_no").val();
	if(sw_no=='') { parent.sendErrorMessage("Please save changes to the document header first before adding items to this Stocks Withdrawal Slip."); } else {
		
		var icode = $("#product_code").val();
		var idesc = $("#description").val();
		var qty = parseFloat(parent.stripComma($("#qty").val()));
		
		if(icode == "") { msg = msg + "- Product Code not specified<br/>"; }
		if(idesc == "") { msg = msg + "- Product Description not specified<br/>"; }
		if(isNaN(qty) == true || qty == "") { msg = msg + "- Invalid Quantity<br/>"; }
		
		if(msg != "") {
			parent.sendErrorMessage(msg);
		} else {
			$.post("sw.datacontrol.php", { mod: "insertDetail", sw_no: $("#sw_no").val(), icode: icode, desc: idesc, qty: qty, unit: $("#unit").val(), sid: Math.random() }, function(data) {
				$("#details").html(data);
				$("#product_code").val('');
				$("#description").val('');
				$("#autodescription").val('');
				$("#unit").val('');
				$("#qty").val('');
				document.getElementById("autodescription").focus();
			},"html");
		}
	}
}

function updateQty(val,sw_no,lineid,oQty) {
	var txtobj = 'qty['+lineid+']';
	if(isNaN(val) == true || parseFloat(val) < 0) {
		parent.sendErrorMessage("You have specified an invalid quantity!");
		document.getElementById(txtobj).value = oQty;
	} else {
		$.post("sw.datacontrol.php", { mod: "usabQty", lid: lineid, val: val, sw_no: sw_no, sid: Math.random() });
	}
}

function finalizeSW(sw_no,uid) {
	$.post("sw.datacontrol.php", { mod: "check4print", sw_no: $("#sw_no").val(), sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Stocks Withdrawal Slip?") == true) {
				$.post("sw.datacontrol.php", { mod: "finalizeSW", sw_no: $("#sw_no").val(), type: $("#ref_type").val(), sw_date: $("#sw_date").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
					parent.viewSW($("#sw_no").val());
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Purchase Order..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Stocks Withdrawal Slip..."); break;
			}
		}
	},"html");
}

function reopenSW(sw_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("sw.datacontrol.php", { mod: "reopenSW", sw_no: sw_no, sid: Math.random() }, function() {
			parent.viewSW(sw_no);
		});
	}
}

function cancelSW(sw_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("sw.datacontrol.php", { mod: "cancel", sw_no: sw_no, sid: Math.random() }, function(){
			alert("Stocks Withdrawal Slip Successfully Cancelled!");
			parent.showSW();
		});
	}
}

function reuseSW(sw_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("sw.datacontrol.php", { mod: "reopenSW", sw_no: sw_no, sid: Math.random() }, function(){
			parent.viewSW(sw_no);
		});
	}
}