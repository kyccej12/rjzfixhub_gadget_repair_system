function computeItemAmount(qty) {
	if(isNaN(qty) == true) { parent.sendErrorMessage("-Invalid Quantity!"); $("#itemQty").val(''); }
	var cost = parseFloat(parent.stripComma($("#itemCost").val()));
	
	var amount = qty * cost;
		amount = amount.toFixed(2);

	$("#itemAmount").val(parent.kSeparator(amount));
}

function addItem() {
	$("#itemEntry").dialog({title: "Record Details", width: 440, resizable: false, modal: true, buttons: { 
			"Add Item": function() { 
				var msg = "";

				if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
				if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }

				if(msg != '') {
					parent.sendErrorMessage(msg);
				
				} else {
					$.post("phy.datacontrol.php", { 
						mod: "addItem", 
						doc_no: $("#doc_no").val(), 
						trace_no: $("#trace_no").val(), 
						item: $("#itemCode").val(), 
						description: $("#itemDescription").val(), 
						unit: $("#itemUnit").val(), 
						qty: $("#itemQty").val(),
						cost: $("#itemCost").val(), 
						amount: $("#itemAmount").val(), 
						lot_no: $("#itemLotNo").val(),
						expiry: $("#itemExpiry").val(),
						sid: Math.random() }, 
					function(gt) {
						redrawDataTable();
						$("#grandTotal").val(gt);
						$("#frmItemEntry").trigger("reset");
						
					});
				}
			},
			"Cancel": function() { $(this).dialog("close"); $("#frmItemEntry").trigger("rest"); }
		}
	
	});
}

function deleteItem(){
	var table = $("#details").DataTable();
	var arr = [];
   $.each(table.rows('.selected').data(), function() {
	   arr.push(this["id"]);
   });
  
	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to delete.");
	} else {
		if(confirm("Are you sure you want to remove this line entry?") == true) {
			$.post("phy.datacontrol.php", { mod: "deleteLine", lid: arr[0], doc_no: $("#doc_no").val(), sid: Math.random() }, function(gt) { redrawDataTable(); $("#grandTotal").val(gt); });
		}
	}
}

function updateItem() {
	var table = $("#details").DataTable();
	var arr = [];
	$.each(table.rows('.selected').data(), function() {
		arr.push(this["id"]);
	});

	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to update.");
	} else {
		
		$.post("phy.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemDescription").val(parent.decodeEntities(data['description']));
			$("#itemCode").val(data['item_code']);
			$("#itemUnit").val(data['unit']);
			$("#itemQty").val(data['qty']);
			$("#itemCost").val(data['ucost']);
			$("#itemAmount").val(data['amt']);
			$("#itemLotNo").val(data['lot_no']);
			$("#itemExpiry").val(data['exp']);
		
			$("#itemEntry").dialog({title: "Update Line Entry", width: 440, resizable: false, modal: true, buttons: { 
					"Save Changes": function() { 
						var msg = "";
		
						if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
						if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }
		
						if(msg != '') {
							parent.sendErrorMessage(msg);
						
						} else {
							if(confirm("Are you sure you want to save changes made to this entry?") == true) {
								$.post("phy.datacontrol.php", { 
									mod: "updateItem", 
									lid: arr[0],
									doc_no: $("#doc_no").val(), 
									trace_no: $("#trace_no").val(), 
									item: $("#itemCode").val(), 
									description: $("#itemDescription").val(), 
									unit: $("#itemUnit").val(), 
									qty: $("#itemQty").val(),
									cost: $("#itemCost").val(), 
									amount: $("#itemAmount").val(), 
									lot_no: $("#itemLotNo").val(),
									expiry: $("#itemExpiry").val(),
									sid: Math.random() }, 
								function(gt) {
									redrawDataTable();
									$("#grandTotal").val(gt);
									$("#frmItemEntry").trigger("reset");
									
								});
							}
						}
					},
					"Cancel": function() { $(this).dialog("close"); $("#frmItemEntry").trigger("reset"); }
				}
			
			});	
		
		},"json");
	}
}

function savePhyHeader() {
	var msg = "";
	if($("#conducted_by").val() == "") { msg = msg + "- Please specify the person who conducted/approved the this physical inventory"; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("phy.datacontrol.php", { mod: "saveHeader", trace_no: $("#trace_no").val(), doc_no: $("#doc_no").val(), doc_date: $("#doc_date").val(), conducted_by: $("#conducted_by").val(), verified_by: $("#verified_by").val(), remarks: $("#remarks").val(), sid: Math.random() }, function(data) { if($("#doc_no").val() == '') { $("#doc_no").val(data); }  parent.popSaver(); });
	}
}

function finalizePhy() {
	$.post("phy.datacontrol.php", { mod: "check4print", doc_no: $("#doc_no").val(), sid: Math.random() }, function(data) { 
		if(data == "noerror") {
			if(confirm("Are you sure you want to finalize this document?") == true) {
				$.post("phy.datacontrol.php", { mod: "finalizePhy", doc_no: $("#doc_no").val(), sid: Math.random() }, function() {
					parent.viewPhy($("#doc_no").val());
				});
			}
		} else {
			switch(data) {
				case "head": parent.sendErrorMessage("Unable to print document. Document is not yet saved..."); break;
				case "det": parent.sendErrorMessage("Unable to print document. It seems that you have not added any product yet to this Physical Inventory Form..."); break;
				case "both": parent.sendErrorMessage("There is nothing to print. Please make it sure you have saved entries you've made in this Physical Inventory Form..."); break;
			}
		}
	},"html");
}

function reopenPhy() {
	if(confirm("Are you sure you want to set this document to active status?") == true) {
		$.post("phy.datacontrol.php", { mod: "reopenPhy", doc_no: $("#doc_no").val(), sid: Math.random() }, function() {
			parent.viewPhy($("#doc_no").val());
		});
	}
}

function cancelPhy() {
	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("phy.datacontrol.php", { mod: "cancel", doc_no: $("#doc_no").val(), sid: Math.random() }, function(){
			alert("Stocks Transfer Receipt Successfully Cancelled!");
			parent.showPhy();
		});
	}
}

function reusePhy() {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("phy.datacontrol.php", { mod: "reopenPhy", doc_no: $("#doc_no").val(), sid: Math.random() }, function(){
			parent.viewPhy(doc_no);
		});
	}
}

function printDocument() {
	parent.printPhy($("#doc_no").val());
}

function reprintPhy() {
	window.open("print/phy.print.php?doc_no="+$("#doc_no").val()+"&sid="+Math.random()+"&user="+uid+"&reprint=Y","Physical Inventory Form","location=1,status=1,scrollbars=1,width=640,height=720");
}

