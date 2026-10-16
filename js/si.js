
var SLid;

function showLoaderMessage() {
	$("#loaderMessage").dialog({ width: 400, height: 150, closable: false, modal: true,  open: function(event, ui) {
        $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    }});
}

function computeAmount() {
	var price = parseFloat(parent.stripComma($("#unit_price").val()));
	var qty = parseFloat(parent.stripComma($("#qty").val()));

	if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
		
	} else {
		var amt = price * qty;
			amt = amt.toFixed(2);
		

		$("#amount").val(parent.kSeparator(amt));
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

function updateTotals() {
	$.post("si.datacontrol.php", { mod: "getTotals", trace_no: $("#trace_no").val(), sid: Math.random() }, function(data) {
		
		var applied = parseFloat(parent.stripComma($("#amount_applied").val()));
		var net = parseFloat((data['gross'])) - parseFloat((data['discount']));
		    net = net.toFixed(2);

		var balance = net - applied;
			balance = balance.toFixed(2);
			console.log(balance);
		
		$("#amount_b4_discount").val(parent.kSeparator(data['gross']));
		$("#discount_in_peso").val(parent.kSeparator(data['discount']));
		$("#total_due").val(parent.kSeparator(net));
		$("#balance_due").val(parent.kSeparator(balance));
	},"json");
}

function saveInvHeader() {
	$.post("si.datacontrol.php", { mod: "saveHeader", trace_no : $("#trace_no").val(), doc_no: $("#doc_no").val(), type: $("#docno_type").val(), invoice_date: $("#invoice_date").val(), postDate: $("#posting_date").val(), cid: $("#customer_id").val(), cname: $("#customer_name").val(), addr: $("#cust_address").val(), discount: $("#sales_discount").val(), terms: $("#terms").val(), srep: $("#sales_rep").val(), remarks: $("#remarks").val(), sid: Math.random() }, function(data) {
		parent.popSaver();
		$("#cSelected").val('Y');
	},"json");
	
}


function addDetails() {
	if($("#cSelected").val() != "Y") {
		//parent.sendErrorMessage("It appears this document has yet to be initially saved... Please save changes made first prior to adding item details to it.");
		saveInvHeader();
	} 
	
	var msg = "";
	var trace_no = $("#trace_no").val();
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
		parent.sendErrorMessage(msg);
	} else {
		$.post("si.datacontrol.php", { mod: "insertDetail", trace_no: trace_no, doc_no: $("#doc_no").val(), icode: icode, desc: idesc, qty: qty, unit: $("#unit").val(), price: price, amount: amount, sid: Math.random() }, function(data) {
			$("#product_code").val('');
			$("#description").val('');
			$("#autodescription").val('');
			$("#unit").val('');
			$("#unit_price").val('');
			$("#amount").val('');
			$("#qty").val('');
			$("#details").html(data);
			updateTotals(trace_no);
		},"html");
	}
	
}

function deleteLine() {
	if(SLid == '') {
		parent.sendErrorMessage("It appears you have yet to select an entry to remove.");
	} else {
		if(confirm("Are you sure you want to remove this entry?") == true) {
			$.post("si.datacontrol.php", { mod: "deleteLine", lid: SLid, trace_no: $("#trace_no").val(), sid: Math.random() }, function(data) {
				$("#details").html(data)
				updateTotals($("#trace_no").val());
			},"html");
		}
	}
}

function clearItems() {
	if(confirm("Are you sure you want to clear all item entries loaded into this Invoice?") == true) {
		$.post("si.datacontrol.php", { mod: "clearItems", trace_no: $("#trace_no").val(), sid: Math.random() }, function(ret) {
			$("#details").html(ret);
			updateTotals($("#trace_no").val());
		},"html");
	}
}	

function updateQty(val,lineid,price,qty) {
	var msg = "";
	var txtobj = 'qty['+lineid+']';

	if(isNaN(val) == true) { 
		var msg = msg + "It appears you have specified an invalid quantity."; 
	} else {
		if(parseFloat(val) > parseFloat(qty)) { msg = msg + "- You have specified a quantity greater than the original Sales Order Value"; document.getElementById(txtobj).value = qty; }
	}
	
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("si.datacontrol.php", { mod: "usabQty", trace_no: $("#trace_no").val(), lid: lineid, qty: val, price: price, trace_no: $("#trace_no").val(), sid: Math.random() }, function(data) {
			$("#details").html(data);
			updateTotals($("#trace_no").val());
		},"html");
	}
}

function updatePrice(val,lineid,price,qty) {
	var msg = "";
	var txtobj = 'price['+lineid+']';
	
	if(isNaN(val) == true || parseFloat(val) < 0) { var msg = msg + "It appears you have specified an invalid product price."; document.getElementById(txtobj).value = price; } 

	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("si.datacontrol.php", { mod: "usabPrice", trace_no: $("#trace_no").val(), lid: lineid, price: val, qty: qty, trace_no: $("#trace_no").val(), sid: Math.random() }, function(data) {
			$("#details").html(data);
			updateTotals($("#trace_no").val());
		},"html");
	}
}

function finalizeSI() {
	if(confirm("Are you sure you want to Finalize & Post this document to the General Ledger?") == true) {
		var msg = "";
		saveInvHeader();

		if(msg == ""){
			showLoaderMessage(); 

			$.post("si.datacontrol.php", { mod: "check4print", trace_no: $("#trace_no").val(), sid: Math.random() }, function(data) { 
				if(data == "noerror") {
					if($("#terms").val() == '0'){
						$("#loaderMessage").dialog("close");
						$("#payment_mode").dialog({ width: 530, closable: true, modal: true});
					} else {
						$.post("si.datacontrol.php", { mod: "finalize", doc_no: $("#doc_no").val(), trace_no: $("#trace_no").val(), sid: Math.random() }, function() {
							$.post("si.datacontrol.php", { mod: "flushTempGL", trace_no: $("#trace_no").val(), sid: Math.random() }, function() {
								parent.viewSI($("#doc_no").val());
							});
						});
					}
				} else {
					$("#loaderMessage").dialog("close");
					switch(data) {
						case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
						case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Receiving Report..."); break;
						case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved what entries you've made..."); break;
					}
				}
				
			},"html");
		}
	} 
}


function reopenSI() {
	var applied = parseFloat(parent.stripComma($("#amount_applied").val()));
	if(applied > 0 && $("#terms").val() != 0 && $("#terms").val() != 1) {
		parent.sendErrorMessage("- It appears that a payment has already been made to this invoice.");
	} else {
		if(confirm("Are you sure you want to set this document to active status?") == true) {
			showLoaderMessage(); $("#uppermenus").html('');
			$.post("si.datacontrol.php", { mod: "reopen", trace_no: $("#trace_no").val(), doc_no: $("#doc_no").val(), sid: Math.random() }, function() {
				parent.viewSI($("#doc_no").val());
				$("#loaderMessage").dialog("close");
			});
		}
	}
}

function cancelSI() {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("si.datacontrol.php", { mod: "cancel", trace_no: $("#trace_no").val(), doc_no: $("#doc_no").val(), sid: Math.random() }, function(){
			alert("Receving Report Successfully Cancelled!");
			parent.viewSI($("#doc_no").val());
		});
	}
}

function reuseSI() {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("si.datacontrol.php", { mod: "reopen", trace_no: $("#trace_no").val(), doc_no: $("#doc_no").val(), sid: Math.random() }, function(){
			parent.viewSI($("#doc_no").val());
		});
	}
}

function printSI(rprint) {
	parent.printSI($("#doc_no").val(),rprint);
}

function printPackingList() {
	parent.printPackingList($("#doc_no").val());
}

function viewDoc(){
	$("#docinfo").dialog({title: "Document Info", width: 340, height: 200, resizable: false });
}

function applyDiscount(){
	if(SLid == ''){
		parent.sendErrorMessage("It appears you have yet to select an item to apply for discount.");
	}else{

		var dis = $("#discountDiv").dialog({ 
			title: "Sales Discount",
			width: 280,
			resizable: false,
			modal: true,
			buttons: {
				
				"Apply Sales Discount": function() { 
					var discount = $("#salesDiscount").val(); 
					
					$.post("si.datacontrol.php",{
						mod : "applyDiscount" ,
						lineid : SLid,
						discount : discount,
						trace_no : $("#trace_no").val(),
						type: $("input:radio[name=type]:checked").val(),
						sid: Math.random()
					},function(data){
						$("#details").html(data);
						updateTotals($("#trace_no").val());
					},"html");

					dis.dialog("close"); 
				}
			}
		});
	}
}

function cashCheckOut(tid) {
	$("#amountDue").val($("#total_due").val());
	$("#amountTendered").focus();
	$("#cashCheckOutForm").dialog({
		title: "Cash Checkout",
		modal: true, 
		title: "Cash Payment Method", 
		width: 500, 
		resizable: false, 
			buttons: {
			"Finalize Transaction":  function() { finalizePOScash(); }
		}
	});
}

function finalizePOScash() {
	var	tendered = parseFloat(parent.stripComma($("#amountTendered").val()));
	var	due = parseFloat(parent.stripComma($("#amountDue").val()));
		
	if(tendered < due || tendered == '' || tendered ==0) { parent.sendErrorMessage("Amount Tendered is less than the amount due"); $("#amountTendered").val(''); $("#changeDue").val('0.00'); } else {
		$.post("si.datacontrol.php",{  mod: "finalizePOScash", trace_no : $("#trace_no").val(), due: $("#amountDue").val(), tendered: $("#amountTendered").val(), change: $("#changeDue").val(), sid: Math.random() },function(data){
			$("#cashCheckOutForm").dialog("close");
			parent.viewSI($("#doc_no").val());
		});
	}
}

function computeChange(val) {
	var	tendered = parseFloat(parent.stripComma(val)); //parseFloat(val);
	var	due = parseFloat(parent.stripComma($("#amountDue").val()));
		
	if(tendered < due) { parent.sendErrorMessage("Amount Tendered is less than the amount due"); $("#amountTendered").val(''); $("#changeDue").val('0.00'); } else {
		var change = tendered - due;
			change = change.toFixed(2);
		$("#changeDue").val(parent.kSeparator(change));
	}
}

function ccCheckOut() {
	$("#cardCheckOutForm").dialog({
		modal: true, 
		title: "Credit Card Payment Method", 
		width: 380, 
		height: 405, 
		modal: true,
		resizable: false, 
			buttons: {
			"Finalize Transaction":  function() { finalizePOScard(); }
		}
	});	
}

function finalizePOScard() {
	if(confirm("Are you sure you want to finalize this transaction?") == true) {
		var msg = "";
		if($("#cc_name").val() == "") { msg = msg + "- Card Holder Name is empty<br/>"; }
		if($("#cc_no").val() == "") { msg = msg + "- Card No. is empty<br/>"; }
		if($("#cc_expiry").val() == "") { msg = msg + "- Card Expiry Date is empty<br/>"; }
		if($("#cc_approvalno").val() == "") { msg = msg + "- Transaction Approval No. is empty<br/>"; }
		
		if(msg!="") { parent.sendErrorMessage(msg); } else {
			$.post("si.datacontrol.php", { mod: "finalizePOScard", trace_no: $("#trace_no").val(), bank: $("#cc_bank").val(), cc_type: $("#cc_type").val(), cc_name: $("#cc_name").val(), cc_expiry: $("#cc_expiry").val(), approvalno: $("#cc_approvalno").val(), cc_no : $("#cc_no").val(), sid: Math.random()  }, function() {
				alert("Transaction Successfully Posted!");
				parent.viewSI($("#doc_no").val());
			});
		}
	}
}
