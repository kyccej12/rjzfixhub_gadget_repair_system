
$(document).ready(function($){
	  $('#description').autocomplete({
		source:'suggestItemsCost.php', 
		minLength:3,
		select: function(event,ui) {
			$("#product_code").val(ui.item.item_code);
			$("#unit_price").val(parent.kSeparator(ui.item.unit_price));
			$("#unit").val(ui.item.unit);
			$("#qty").focus();
		}
	});
});

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

function saveSRRHeader() {
	var msg = "";
	if($("#received_from").val() == "") { msg = msg + "- Please specify where this inventory came from by filling up \"<b>Received From</b>\" input field..."; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("srr.datacontrol.php", { mod: "saveHeader", srr_no: $("#srr_no").val(), srr_date: $("#srr_date").val(), from: $("#received_from").val(), by: $("#received_by").val(), ref_type: $("#ref_type").val(), ref_no: $("#ref_no").val(), ref_date: $("#ref_date").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() { parent.popSaver(); });
	}
}

function addDetails() {
	var msg = "";
	var icode = $("#product_code").val();
	var idesc = $("#description").val();
	var qty = parseFloat(parent.stripComma($("#qty").val()));


	if(icode == "") { msg = msg + "- Product Code not specified<br/>"; }
	if(idesc == "") { msg = msg + "- Product Description not specified<br/>"; }
	if(isNaN(qty) == true || qty == "") { msg = msg + "- Invalid Quantity<br/>"; }
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("srr.datacontrol.php", { mod: "insertDetail", srr_no: $("#srr_no").val(), icode: icode, desc: idesc, qty: qty, unit: $("#unit").val(), sid: Math.random() }, function(data) {
			$("#details").html(data);
			$("#product_code").val('');
			$("#description").val('');
			$("#autodescription").val('');
			$("#unit").val('');
			$("#qty").val('');
			$("#description").focus();
		},"html");
	}
}

function updateQty(val,srr_no,lineid,oQty) {
	var val = parent.stripComma(val);
	var txtobj = 'qty['+lineid+']';
	if(isNaN(val) == true || parseFloat(val) < 0) {
		parent.sendErrorMessage("You have specified an invalid Quantity.");
		document.getElementById(txtobj).value = oQty;
	} else {
		$.post("srr.datacontrol.php", { mod: "usabQty", lid: lineid, val: val, srr_no: srr_no, sid: Math.random() });	
	}
}

function finalizeSRR(srr_no,uid) {	
	$.post("srr.datacontrol.php", { mod: "check4print", srr_no: srr_no, sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Stocks Receiving Receipt?") == true) {
				$.post("srr.datacontrol.php", { mod: "finalizeSRR", srr_no: srr_no, srr_date: $("#srr_date").val(), ref_type: $("#ref_type").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
					parent.viewSRR(srr_no);
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Stocks Receiving Receipt..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Stocks Receiving Receipt..."); break;
			}
		}
	},"html");
}

function reopenSRR(srr_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("srr.datacontrol.php", { mod: "reopenSRR", srr_no: srr_no, sid: Math.random() }, function() {
			parent.viewSRR(srr_no);
		});
	}
}

function cancelSRR(srr_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("srr.datacontrol.php", { mod: "cancel", srr_no: srr_no, sid: Math.random() }, function(){
			alert("Stocks Transfer Receipt Successfully Cancelled!");
			parent.showSRR();
		});
	}
}

function reuseSRR(srr_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("srr.datacontrol.php", { mod: "reopenSRR", srr_no: srr_no, sid: Math.random() }, function(){
			parent.viewSRR(srr_no);
		});
	}
}

function reprintSRR(srr_no,uid) {
	window.open("print/srr.print.php?srr_no="+srr_no+"&sid="+Math.random()+"&user="+uid+"&reprint=Y","Sales Order","location=1,status=1,scrollbars=1,width=640,height=720");
}