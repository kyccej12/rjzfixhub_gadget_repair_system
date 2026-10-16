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

function computeAmount() {
	var price = parseFloat(parent.stripComma($("#unit_cost").val()));
	var qty = parseFloat(parent.stripComma($("#qty").val()));

	if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
		/* parent.sendErrorMessage("Invalid Quantity or Price. Please check your entries and try again..."); */
	} else {
		var amt = price * qty;
			amt = amt.toFixed(2);
		$("#amount").val(parent.kSeparator(amt));
	}
}

function getTotals() {
	$.post("po.datacontrol.php", { mod: "getTotals", po_no: $("#po_no").val(), sid: Math.random() }, function(data) {
		$("#amount_b4_discount").val(parent.kSeparator(data['gross']));
		$("#discount_in_peso").val(parent.kSeparator(data['discount']));
		$("#total_due").val(parent.kSeparator(data['net']));
	},"json");
}

function savePOHeader() {
	var msg = "";
	if($("#cSelected").val() == "N") { msg = msg + "- Invalid supplier or source for the Purchase Order<br/>"; }
	if($("#requested_by").val() == "") { msg = msg + "- You have not indicated who requested for the items on this P.O"; }
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else{
		$.post("po.datacontrol.php", { mod: "saveHeader", po_no: $("#po_no").val(), po_date: $("#po_date").val(), terms: $("#terms").val(), proj: $("#proj").val(), cid: $("#customer_id").val(), cname: $("#customer_name").val(), addr: $("#cust_address").val(), requested_by: $("#requested_by").val(), mrs: $("#mrs_no").val(), del_addr: $("#delivery_address").val(), date_needed: $("#date_needed").val(), remarks: $("#remarks").val(), sid: Math.random() },function(data){
			parent.popSaver();
		});
	}
}

function addDetails() {
	var msg = "";
	var icode = $("#product_code").val();
	var idesc = $("#description").val();
	var qty = parseFloat(parent.stripComma($("#qty").val()));
	var cost = parseFloat(parent.stripComma($("#unit_cost").val()));
	var amount = parseFloat(parent.stripComma($("#amount").val()));

	if(icode == "") { msg = msg + "- Product Code not specified<br/>"; }
	if(idesc == "") { msg = msg + "- Product Description not specified<br/>"; }
	if(isNaN(qty) == true || qty == "") { msg = msg + "- Invalid Quantity<br/>"; }
	if(isNaN(cost) == true || cost == "") { msg = msg + "- Invalid Unit Cost<br/>"; }
	if(isNaN(amount) == true || amount == "") { msg = msg + "- Invalid Amount<br/>"; }
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("po.datacontrol.php", { mod: "insertDetail", po_no: $("#po_no").val(), icode: icode, desc: idesc, qty: qty, unit: $("#unit").val(), cost: cost, amount: amount, sid: Math.random() }, function(data) {
			$("#details").html(data);
			$("#product_code").val('');
			$("#autodescription").val('');
			$("#description").val('');
			$("#unit_cost").val('');
			$("#unit").val('');
			$("#qty").val('');
			$("#amount").val('');
			$("#description").focus();
			getTotals();
		},"html");
	}
}

function deleteDetails(lid,po_no) {
	if(confirm("Are you sure you want to remove this entry?") == true) {
		$.post("po.datacontrol.php", { mod: "deleteDetails", lid: lid, po_no: po_no, sid: Math.random() }, function(data) { $("#podetails").html(data); $("#description").focus(); },"html");
	}

}

function updateQty(val,po_no,lineid,price) {
	var txtobj = 'qty['+lineid+']';
	var objamt1 = 'amt['+lineid+']';

	$.post("po.datacontrol.php", { mod: "usabQty", lid: lineid, val: val, price: price, po_no: po_no, sid: Math.random() }, function(data) {
		document.getElementById(objamt1).innerHTML = data['amt1'];
		getTotals();
	  },"json");
}

function updatePrice(val,po_no,lineid) {
	var objQty = 'qty['+lineid+']';
	var objnprice = 'netprice['+lineid+']';
	var objamt1 = 'amt['+lineid+']';
	
	qty = document.getElementById(objQty).value;

	$.post("po.datacontrol.php", { mod: "usabPrice", lid: lineid, qty: qty, price: val, po_no: po_no, sid: Math.random() }, function(data) {
		document.getElementById(objamt1).innerHTML = data['amt1'];
		getTotals();
	  },"json");
}


function printPO(po_no,uid) {
	$.post("po.datacontrol.php", { mod: "check4print", po_no: po_no, sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this Purchase Order?") == true) {
				showLoaderMessage();
				$.post("po.datacontrol.php", { mod: "finalizePO", po_no: po_no, sid: Math.random() }, function() {
					$("#loaderMessage").dialog("close");
					parent.viewPO(po_no);
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Purchase Order..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Purchase Order..."); break;
			}
		}
	},"html");
}

function reopenPO(po_no) {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("po.datacontrol.php", { mod: "reopenPO", po_no: po_no, sid: Math.random() }, function() {
			location.reload();
			parent.viewPO(po_no);
		});
	}
}

function cancelPO(po_no) {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("po.datacontrol.php", { mod: "cancel", po_no: po_no, sid: Math.random() }, function(){
			alert("Receving Report Successfully Cancelled!");
			parent.showPOList();
		});
	}
}

function reusePO(po_no) {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("po.datacontrol.php", { mod: "reopenPO", po_no: po_no, sid: Math.random() }, function(){
			parent.viewPO(po_no);
		});
	}
}

function reprintPO(po_no,uid) {
	parent.printPO(po_no,uid);
}


function applyDiscount(){
	if(line===undefined){
		alert("Please select ");
	}else{
		$("#discountDiv").dialog({
			title: "Apply Line Discount",
			width: 280,
			resizable: false,
			modal: true,
			buttons: {
				
				"Apply Line Discount": function() { 
					var discount = $("#poDiscount").val(); 
					
					$.post("po.datacontrol.php",{
						mod : "applyDiscount" ,
						lineid : line,
						discount : discount,
						po_no : $("#po_no").val(),
						type: $("input:radio[name=type]:checked").val(),
						sid: Math.random()
					},function(data){
						$("#details").html(data);
						getTotals();
					},"html");

					$(this).dialog("close"); 
				}
			}
		});
	}
}
	
function addDescription(){
	if(line===undefined){
		alert("Please select an item from the item list .");
	}else{
		$.post("po.datacontrol.php", { mod: "getCurrentDescription", lineid: line, sid: Math.random() }, function(ret) {
			$("#customDesc").val(ret);
			$("#itemDescDiv").dialog({ 
				title: "Custom Item Description",
				width: 540,
				resizable: false,
				modal: true,
			buttons: {
				"Save Changes": function() { 
					$.post("po.datacontrol.php", { mod: "saveCustomDesc", po_no: $("#po_no").val(), lineid: line, desc: $("#customDesc").val(), sid: Math.random() }, function(res) {
						$("#details").html(res);
						$("#customDesc").val('');
						$("#itemDescDiv").dialog("close");
					},"html");
				},
				"Cancel": function() {
					$("#customDesc").val(''); $(this).dialog("close");
				}
			}
			});
		},"html");
	}
}