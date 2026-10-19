function computeItemAmount() {
    var qty = parseFloat(parent.stripComma(parent.$("#itemQty").val() || "0"));
    var amount = parseFloat(parent.stripComma(parent.$("#itemAmount").val() || "0"));

    if (isNaN(qty) || qty <= 0) {
        parent.$("#itemAmountDue").val("0.00");
        return;
    }

    if (isNaN(amount)) amount = 0;

    var amountdue = qty * amount;

    parent.$("#itemAmountDue").val(parent.kSeparator(amountdue.toFixed(2)));
}


function computeTransactionTotal() {
    var parts = parseFloat(parent.stripComma($("#pgrandTotal").val() || "0")) || 0;
    var labor = parseFloat(parent.stripComma($("#laborTotal").val() || "0")) || 0;

    var total = parts + labor;
    $("#grandTotal").val(parent.kSeparator(total.toFixed(2)));
}


function saveLaborTotal() {
    var jo_no = $("#jo_no").val();
    var labor = $("#laborTotal").val();

    console.log("Saving labor...");
    console.log("JO Number:", jo_no);
    console.log("Labor:", labor);

    $.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "updateLabor",
            jo_no: jo_no, 
            trace_no: frameWindow.$("#trace_no").val(),
            laborTotal: labor,
            sid: Math.random()
        },
        success: function(result) {
            console.log("PHP response:", result);

            if (result.error) {
                alert(result.error);
                return;
            }

            $("#pgrandTotal").val(result.partsTotal);
            $("#laborTotal").val(result.laborTotal);
            $("#grandTotal").val(result.grandTotal);

        },
    });
}


function addItem() {
    var $p = parent.$;
    var frameWindow = window;

    var customerField = document.getElementById("customer_code");
    var customerCode = customerField ? customerField.value : "";

    console.log("Current page:", window.location.href);
    console.log("Customer code:", customerCode);

    var $itemEntry = $p("#itemEntry");
    var $form = $p("#frmItemEntry");

    $itemEntry.dialog({
        title: "Add Item",
        width: 600,
        resizable: false,
        modal: true,
        buttons: [
            {
                text: "Add Item",
                click: function() {
                    var msg = "";

                    var itemCode = $p("#itemCode").val();
                    var itemQty = $p("#itemQty").val();

                    if (itemCode == "") {
                        msg += "- Invalid Item Code<br/>";
                    }

                    if (itemQty == "" || isNaN(itemQty) ||
                        parseFloat(itemQty) <= 0) {
                        msg += "- Invalid Quantity<br/>";
                    }

                    if (msg != "") {
                        frameWindow.parent.sendErrorMessage(msg);
                        return;
                    }


					$p.ajax({
						url: "jo.datacontrol.php",
						type: "POST",
						dataType: "json",
						data: {
							mod: "addItem",
							jo_no: frameWindow.$("#jo_no").val(),
							trace_no: frameWindow.$("#trace_no").val(),
							description: $p("#itemDescription").val(),
							item: itemCode,
							brand: $p("#itemBrand").val(),
							qty: itemQty,
							amount: $p("#itemAmount").val(),
							amountdue: $p("#itemAmountDue").val(),
							laborTotal: frameWindow.$("#laborTotal").val(),
							sid: Math.random()
						},
						success: function(result) {
							if (result.error) {
                                frameWindow.parent.sendErrorMessage(result.error);
                                return;
                            }

                            // Refresh the services list.
                           if (typeof frameWindow.redrawIItemDataTable === "function") {
                                frameWindow.redrawIItemDataTable();
                            }

                            // Update JO totals and payment balance.
                            frameWindow.$("#pgrandTotal").val(result.partsTotal);
                            frameWindow.$("#laborTotal").val(result.laborTotal);
                            frameWindow.$("#grandTotal").val(result.grandTotal);

                            if (frameWindow.$("#totalPaid").length) {
                                frameWindow.$("#totalPaid").val(result.totalPaid);
                            }

                            if (frameWindow.$("#remainingBalance").length) {
                                frameWindow.$("#remainingBalance").val(result.remainingBalance);
                            }

							$form[0].reset();
						},
						error: function(xhr) {
							console.error("Add item error:", xhr.responseText);
							frameWindow.parent.sendErrorMessage(
								"Unable to add item. Check the browser console."
							);
						}
					});

                },
                icons: { primary: "ui-icon-check" }
            },
            {
                text: "Close",
                click: function() {
                    $itemEntry.dialog("close");
                    $form[0].reset();
                },
                icons: { primary: "ui-icon-closethick" }
            }
        ]
    });
	

    $p("#itemDescription").autocomplete({
		source: function(request, response) {
			$p.getJSON("suggestJOItems.php", {
				cid: customerCode,
				term: request.term,
				sid: Math.random()
			}, function(data) {
				response(data);
			}).fail(function(xhr) {
				console.error("Autocomplete error:", xhr.responseText);
				response([]);
			});
		},
		
		select: function(event, ui) {
			var item = ui.item;

			$p("#itemDescription").val(item.brand + " - " + item.description);
			$p("#itemCode").val(item.item_code);
			$p("#itemBrand").val(item.brand);

			$p("#itemRemaining").val(
				parseFloat(item.remaining || 0).toFixed(2)
			);


			$p("#itemAmount").val(
				parseFloat(item.price || 0).toFixed(2)
			);

			$p("#itemQty").val(1);

			$p("#itemAmountDue").val("0.00");
			frameWindow.parent.computeItemAmount();

			return false;
		}
		
	});
}

function addService() {
    var $p = parent.$;
    var frameWindow = window;

    var $serviceEntry = $p("#servicesEntry");
    var $form = $p("#frmServicesEntry");

    $serviceEntry.dialog({
        title: "Add Service",
        width: 600,
        resizable: false,
        modal: true,
        buttons: [
            {
                text: "Add Service",
                click: function() {
                    var msg = "";

                    var serviceCode = $p("#serviceCode").val();
                    var qty = $p("#serviceQty").val();
                    var amount = $p("#serviceAmount").val();

                    if (serviceCode == "") {
                        msg += "- Please select a service.<br/>";
                    }

                    if (qty == "" || isNaN(qty) ||
                        parseFloat(qty) <= 0) {
                        msg += "- Invalid Quantity.<br/>";
                    }

                    if (msg != "") {
                        frameWindow.parent.sendErrorMessage(msg);
                        return;
                    }

                    $p.ajax({
                        url: "jo.datacontrol.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            mod: "addService",
                            jo_no: frameWindow.$("#jo_no").val(),
                            trace_no: frameWindow.$("#trace_no").val(),
                            item_code: serviceCode,
                            description: $p("#serviceDescription").val(),
                            qty: qty,
                            amount: amount,
                            sid: Math.random()
                        },
                       success: function(result) {
                            if (result.error) {
                                frameWindow.parent.sendErrorMessage(result.error);
                                return;
                            }

                            // Refresh the services list.
                            if (typeof frameWindow.redrawServiceDataTable === "function") {
                                frameWindow.redrawServiceDataTable();
                            }

                            // Update JO totals and payment balance.
                            frameWindow.$("#pgrandTotal").val(result.partsTotal);
                            frameWindow.$("#laborTotal").val(result.laborTotal);
                            frameWindow.$("#grandTotal").val(result.grandTotal);

                            if (frameWindow.$("#totalPaid").length) {
                                frameWindow.$("#totalPaid").val(result.totalPaid);
                            }

                            if (frameWindow.$("#remainingBalance").length) {
                                frameWindow.$("#remainingBalance").val(result.remainingBalance);
                            }

                            // Reset the service entry form.
                            $form[0].reset();
                            $p("#serviceQty").val("1");
                            $p("#serviceAmount").val("0.00");
                            $p("#serviceAmountDue").val("0.00");

                            $serviceEntry.dialog("close");
                        },
                        error: function(xhr) {
                            console.error(
                                "Add service error:",
                                xhr.responseText
                            );

                            frameWindow.parent.sendErrorMessage(
                                "Unable to add service. Check jo.datacontrol.php and the browser console."
                            );
                        }
                    });
                },
                icons: { primary: "ui-icon-check" }
            },
            {
                text: "Close",
                click: function() {
                    $serviceEntry.dialog("close");
                    $form[0].reset();
                    $p("#serviceQty").val("1");
                    $p("#serviceAmount").val("0.00");
                    $p("#serviceAmountDue").val("0.00");
                },
                icons: { primary: "ui-icon-closethick" }
            }
        ]
    });

    // Service description autocomplete.
    $p("#serviceDescription").autocomplete({
        minLength: 1,

        source: function(request, response) {
            console.log("Service autocomplete triggered:", request.term);

            $p.getJSON("suggestJOService.php", {
                term: request.term,
                sid: Math.random()
            }, function(data) {
                console.log("Service results:", data);
                response(data);
            }).fail(function(xhr) {
                console.error(
                    "suggestJOService.php failed:",
                    xhr.status,
                    xhr.responseText
                );
                response([]);
            });
        },

       select: function(event, ui) {
            $p("#serviceDescription").val(ui.item.description);
            $p("#serviceCode").val(ui.item.code);

            var unitPrice = parseFloat(ui.item.amount) || 0;

            $p("#serviceAmount").val(unitPrice.toFixed(2));
            $p("#serviceQty").val("1");

            computeServiceAmount();

            return false;
        }
    });

    // Calculate when the quantity changes.
    $p("#serviceQty")
        .off("input.service")
        .on("input.service", function() {
            computeServiceAmount();
        });
}

function computeServiceAmount() {
    var $p = parent.$;

    var qty = parseFloat($p("#serviceQty").val()) || 0;
    var amount = parseFloat($p("#serviceAmount").val()) || 0;

    if (qty < 0) {
        qty = 0;
    }

    if (amount < 0) {
        amount = 0;
    }

    var amountDue = qty * amount;

    $p("#serviceAmountDue").val(amountDue.toFixed(2));
}


function openReceivePayment() {
    var $p = parent.$;
    var frameWindow = window;
    var joNo = $("#jo_no").val();

    if (!joNo) {
        parent.sendErrorMessage("Please save the Job Order first.");
        return;
    }

    var $dialog = $p("#joPaymentDialog");

	if (!$p("#joPaymentDialog").length) {
		$p("body").append(
			'<div id="joPaymentDialog" style="display:none;font-family:Arial,sans-serif;font-size:13px;">' +

				'<div id="joPaymentSummary" style="' +
					'padding:10px;' +
					'border:1px solid #ddd;' +
					'border-radius:4px;' +
					'background:#fafafa;' +
					'margin-bottom:12px;' +
					'box-sizing:border-box;' +
				'">' +
					'<div style="margin-bottom:8px;">' +
						'<b>Transaction Total:</b>' +
						'<span id="payGrandTotal" style="float:right;">0.00</span>' +
					'</div>' +
					'<div style="margin-bottom:8px;">' +
						'<b>Total Paid:</b>' +
						'<span id="payTotalPaid" style="float:right;">0.00</span>' +
					'</div>' +
					'<hr style="border:0;border-top:1px solid #ddd;">' +
					'<div style="font-size:14px;margin-top:8px;">' +
						'<b>Remaining Balance:</b>' +
						'<b id="payBalance" style="float:right;">0.00</b>' +
						'<div style="clear:both;"></div>' +
					'</div>' +
				'</div>' +

                '<div id="payPreviewRow" style="' +
                    'display:none;' +
                    'margin-bottom:12px;' +
                    'padding:10px;' +
                    'border:1px solid #ddd;' +
                    'border-radius:4px;' +
                    'background:#fafafa;' +
                    'box-sizing:border-box;' +
                '">' +
                    '<b>Balance After Payment:</b>' +
                    '<strong id="payPreviewBalance" style="float:right;">0.00</strong>' +
                    '<div style="clear:both;"></div>' +
                '</div>' +
				'<div class="jo-payment-form">' +

					'<label for="payType"><b>Payment Type</b></label>' +
					'<select id="payType" style="' +
						'width:100%;padding:7px;box-sizing:border-box;margin-top:5px;' +
					'">' +
						'<option value="Downpayment">Downpayment</option>' +
						'<option value="Partial Payment">Partial Payment</option>' +
						'<option value="Full Payment">Full Payment</option>' +
					'</select>' +

					'<label for="payAmount" style="display:block;margin-top:12px;">' +
						'<b>Payment Amount</b>' +
					'</label>' +
					'<input type="number" id="payAmount" min="0.01" step="0.01" ' +
						'style="width:100%;box-sizing:border-box;padding:8px;margin-top:5px;" ' +
						'placeholder="Enter payment amount">' +

					'<label for="payMethod" style="display:block;margin-top:12px;">' +
						'<b>Payment Method</b>' +
					'</label>' +
					'<select id="payMethod" style="' +
						'width:100%;padding:7px;box-sizing:border-box;margin-top:5px;' +
					'">' +
						'<option value="Cash">Cash</option>' +
						'<option value="GCash">GCash</option>' +
						'<option value="Bank Transfer">Bank Transfer</option>' +
						'<option value="Debit/Credit Card">Debit/Credit Card</option>' +
						'<option value="Other">Other</option>' +
					'</select>' +

					'<label for="payReference" style="display:block;margin-top:12px;">' +
						'<b>Reference No. (Optional)</b>' +
					'</label>' +
					'<input type="text" id="payReference" maxlength="100" ' +
						'style="width:100%;box-sizing:border-box;padding:8px;margin-top:5px;" ' +
						'placeholder="Enter receipt or transaction reference">' +

					'<label for="payRemarks" style="display:block;margin-top:12px;">' +
						'<b>Remarks (Optional)</b>' +
					'</label>' +
					'<textarea id="payRemarks" rows="3" ' +
						'style="width:100%;box-sizing:border-box;padding:8px;margin-top:5px;" ' +
						'placeholder="Enter payment remarks..."></textarea>' +

				'</div>' +
                '<div id="payMessage" style="' +
                    'color:#b00020;' +
                    'margin-top:10px;' +
                    'font-weight:bold;' +
                '"></div>' +
			'</div>'
		);
        $dialog = $p("#joPaymentDialog");
	}

    function showPaymentMessage(message) {
        $dialog.find("#payMessage")
            .text(message)
            .show();
    }

    function money(value) {
        var number = parseFloat(value);
        if (isNaN(number)) {
            number = 0;
        }
        return number.toFixed(2);
    }

    
        function refreshPreview() {
            var balance = parseFloat($dialog.data("balance")) || 0;
            var amount = parseFloat($dialog.find("#payAmount").val()) || 0;

            var preview = balance - amount;

            if (preview < 0) {
                preview = 0;
            }

            // Keep the original remaining balance visible.
            $dialog.find("#payBalance").text(money(balance));

            // Update the existing preview instead of creating new HTML.
            $dialog.find("#payPreviewBalance").text(money(preview));

            if (amount > 0) {
                $dialog.find("#payPreviewRow").show();
            } else {
                $dialog.find("#payPreviewRow").hide();
            }
        }


        $p.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "getPaymentSummary",
            jo_no: joNo,
            trace_no: $("#trace_no").val(),
            sid: Math.random()
        },
        success: function(result) {
            if (!result.success) {
                parent.sendErrorMessage(
                    result.message || "Unable to retrieve payment summary."
                );
                return;
            }

            if (result.status == "Cancelled" ||
                result.status == "Completed") {
                parent.sendErrorMessage(
                    "This Job Order cannot receive additional payments."
                );
                return;
            }

            var total = parseFloat(result.grandTotal) || 0;
            var paid = parseFloat(result.totalPaid) || 0;
            var balance = parseFloat(result.remainingBalance) || 0;

            $dialog.data("balance", balance);
            $dialog.find("#payGrandTotal").text(money(total));
            $dialog.find("#payTotalPaid").text(money(paid));
            $dialog.find("#payBalance").text(money(balance));
            $dialog.find("#payAmount").val("");
            $dialog.find("#payReference").val("");
            $dialog.find("#payRemarks").val("");
            $dialog.find("#payType").val(
                paid <= 0 ? "Downpayment" : "Partial Payment"
            );
            $dialog.find("#payMethod").val("Cash");
            $dialog.find("#payMessage").text("");

            $dialog.dialog({
                title: "Receive Payment - JO " + joNo,
                width: 440,
                modal: true,
                resizable: false,
                buttons: [
                    {
                        text: "Receive Payment",
                        click: function() {
                            var amount = parseFloat(
                                $dialog.find("#payAmount").val()
                            ) || 0;

                            var currentBalance =
                                parseFloat($dialog.data("balance")) || 0;

                            if (amount <= 0) {
                                $dialog.find("#payMessage").text(
                                    "Enter a valid payment amount."
                                );
                                return;
                            }

                            if (amount > currentBalance + 0.001) {
                                $dialog.find("#payMessage").text(
                                    "Payment exceeds the remaining balance."
                                );
                                return;
                            }

                            var paymentData = {
                                mod: "savePayment",
                                jo_no: joNo,
                                trace_no: frameWindow.$("#trace_no").val(),
                                amount: money(amount),
                                payment_type: $dialog.find("#payType").val(),
                                payment_method: $dialog.find("#payMethod").val(),
                                reference_no: $dialog.find("#payReference").val(),
                                remarks: $dialog.find("#payRemarks").val(),
                                sid: Math.random()
                            };

                            var $button = $p(this);

                            $button.prop("disabled", true);

                            $p.ajax({
                                url: "jo.datacontrol.php",
                                type: "POST",
                                dataType: "json",
                                data: paymentData,
                                success: function(saved) {
                                    if (!saved.success) {
                                        $dialog.find("#payMessage").text(
                                            saved.message ||
                                            "Unable to save payment."
                                        );
                                        return;
                                    }

                                    var totals = saved.totals || {};

                                    frameWindow.$("#pgrandTotal").val(
                                        totals.partsTotal || "0.00"
                                    );
                                    frameWindow.$("#laborTotal").val(
                                        totals.laborTotal || "0.00"
                                    );
                                    frameWindow.$("#grandTotal").val(
                                        totals.grandTotal || "0.00"
                                    );

									frameWindow.$("#totalPaid").val(
										totals.totalPaid || "0.00"
									);

									frameWindow.$("#remainingBalance").val(
										totals.remainingBalance || "0.00"
									);
   
                                   $dialog.dialog("close");

                                    // Show a browser message above all dialogs.
                                    parent.alert("Payment recorded successfully.");

                                    // Refresh the Job Order after the user clicks OK.
                                    parent.viewJO(joNo);

                                },
                                error: function(xhr) {
                                    console.error(
                                        "Payment save error:",
                                        xhr.responseText
                                    );
                                    $dialog.find("#payMessage").text(
                                        "Unable to save payment. Check the console."
                                    );
                                },
                                complete: function() {
                                    $button.prop("disabled", false);
                                }
                            });
                        }
                    },
                    {
                        text: "Close",
                        click: function() {
                            $dialog.dialog("close");
                        }
                    }
                ]
            });

            $dialog.find("#payAmount")
                .off("input.payment")
                .on("input.payment", refreshPreview);

            $dialog.find("#payType")
                .off("change.payment")
                .on("change.payment", function() {
                    if (this.value == "Full Payment") {
                        $dialog.find("#payAmount").val(
                            money($dialog.data("balance"))
                        );
                    }
                    refreshPreview();
                });

            refreshPreview();
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error("Payment summary error:", textStatus, errorThrown);
            console.error(xhr.responseText);
            parent.sendErrorMessage(
                "Unable to load payment summary. Check the browser console."
            );
        }
    });
}


function deleteItem() {
    var table = $("#details").DataTable();
    var selected = table.rows(".selected").data();

    if (selected.length === 0) {
        parent.sendErrorMessage("Please select a record to delete.");
        return;
    }

    var lid = selected[0]["id"];

    if (!lid) {
        parent.sendErrorMessage("Invalid item selected.");
        return;
    }

    if (!confirm("Are you sure you want to remove this line entry?")) {
        return;
    }

    $.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "deleteLine",
            lid: lid,
            jo_no: $("#jo_no").val(),
            laborTotal: $("#laborTotal").val(),
            sid: Math.random()
        },
        success: function(result) {
            if (result.error) {
                parent.sendErrorMessage(result.error);
                return;
            }

            redrawDataTable();

            $("#pgrandTotal").val(result.partsTotal);
            $("#laborTotal").val(result.laborTotal);
            $("#grandTotal").val(result.grandTotal);
        },
        error: function(xhr) {
            console.error("Delete item error:", xhr.responseText);
            parent.sendErrorMessage(
                "Unable to delete item. Check the browser console."
            );
        }
    });
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
		
		$.post("so.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemDescription").val(parent.decodeEntities(data['description']));
			$("#itemCode").val(data['item_code']);
			$("#itemUnit").val(data['unit']);
			$("#itemQty").val(data['qty']);
			$("#itemCost").val(data['ucost']);
			$("#itemAmount").val(data['amt']);
		
			$("#itemEntry").dialog({
				title: "Update Line Entry", 
				width: 440, 
				resizable: false, 
				modal: true, 
				buttons: [
					{ 
						text: "Save Changes",
						click: function() { 
							var msg = "";
			
							if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
							if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }
			
							if(msg != '') {
								parent.sendErrorMessage(msg);
							
							} else {
								if(confirm("Are you sure you want to save changes made to this entry?") == true) {
									$.post("so.datacontrol.php", { 
										mod: "updateItem", 
										lid: arr[0],
										jo_no: $("#jo_no").val(), 
										trace_no: $("#trace_no").val(), 
										item: $("#itemCode").val(), 
										description: $("#itemDescription").val(), 
										unit: $("#itemUnit").val(), 
										qty: $("#itemQty").val(),
										cost: $("#itemCost").val(), 
										amount: $("#itemAmount").val(), 
										sid: Math.random() }, 
									function(gt) {
										redrawDataTable();
										$("#pgrandTotal").val(gt);
										$("#frmItemEntry").trigger("reset");
										
									});
								}
							}
						},
						icons: { primary: "ui-icon-check" }
					},
					{
						text: "Close",
						click: function() { $(this).dialog("close"); $("#frmItemEntry").trigger("reset"); },
						icons: { primary: "ui-icon-closethick" }
					}
				]
			});	
		
		},"json");
	}

	$('#itemDescription').autocomplete({
		source:"suggestService.php?cid="+$("#customer_code").val()+"&sid="+Math.random()+"", 
		minLength:3,
		select: function(event,ui) {
			$("#itemCode").val(ui.item.code);
			$("#itemUnit").val(ui.item.unit);
			$("#itemCost").val(ui.item.price);
			$("#itemSpecial").val(ui.item.specialprice);
			$("#is_sprice").val(ui.item.sprice);
			computeItemAmount($("#itemQty").val());
		}
	});
}

function checkClear(val) {
	if(val == '' || val == 0) {
		$("#customer_code").val(''); $("#customer_name").val(''); $("#customer_address").val('');
	}
}

function saveJOHeader() {
	var msg = "";
	if($("#cSelected").val() == "N") { msg = msg + "- Invalid or missing Customer Information<br/>"; }
	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("jo.datacontrol.php", { 
				mod: "saveHeader", 
				trace_no: $("#trace_no").val(), 
				jo_no: $("#jo_no").val(), 
				jo_date: $("#jo_date").val(), 
				device_type: $("#device_type").val(), 
				customer_code: $("#customer_code").val(), 
				customer_name: $("#customer_name").val(), 
				customer_contactno : $("#customer_contactno").val(), 
				customer_address: $("#customer_address").val(), 
				customer_email: $("#customer_email").val(),  
				unit_name: $("#unit_name").val(),  
				unit_pdate: $("#unit_pdate").val(),  
				unit_lcd: $("#unit_lcd").val(),  
				unit_battery: $("#unit_battery").val(),  
				unit_model: $("#unit_model").val(),  
				unit_warranty: $("#unit_warranty").val(),  
				unit_storage: $("#unit_storage").val(),  
				unit_charger: $("#unit_charger").val(),  
				unit_category: $("#unit_category").val(),  
				unit_processor: $("#unit_processor").val(),  
				unit_ram: $("#unit_ram").val(),  
				unit_motherboard: $("#unit_motherboard").val(),  
				visual_lcd: $("#visual_lcd").val(),  
				visual_usb: $("#visual_usb").val(),  
				visual_bitlocker: $("#visual_bitlocker").val(),  
				visual_charger: $("#visual_charger").val(),  
				visual_storage: $("#visual_storage").val(),  
				visual_screws: $("#visual_screws").val(),  
				visual_camera: $("#visual_camera").val(),  
				visual_touchpad: $("#visual_touchpad").val(),  
				visual_charging: $("#visual_charging").val(),  
				visual_speaker: $("#visual_speaker").val(),  
				visual_hdmi: $("#visual_hdmi").val(),  
				visual_bios: $("#visual_bios").val(),  
				visual_keyboard: $("#visual_keyboard").val(),  
				visual_battery: $("#visual_battery").val(),  
				visual_ram: $("#visual_ram").val(),  
				visual_wifi: $("#visual_wifi").val(),  
				remarks: $("#remarks").val(), 
				action_taken: $("#action_taken").val(), 
				sid: Math.random() }, 
				function (data) {
			if($("#jo_no").val() == "") { $("#jo_no").val(data); }
		},"html");
		parent.popSaver();
	}
}

function receivedJO() {
    if (confirm("Are you sure you want to receive this Job Order?") != true) {
        return;
    }

    saveJOHeader();

    $.post("jo.datacontrol.php", {
        mod: "check4print",
        jo_no: $("#jo_no").val(),
        trace_no: $("#trace_no").val(),
        sid: Math.random()
    }, function(data) {

        data = $.trim(data);
        console.log("check4print response:", data);

        if (data == "noerror") {

            $("#uppermenus").html('');

            $.post("jo.datacontrol.php", {
                mod: "receivedJO",
                jo_no: $("#jo_no").val(),
                trace_no: $("#trace_no").val(),
                sid: Math.random()
            }, function(response) {

                console.log("receivedJO response:", response);

                parent.viewJO($("#jo_no").val());
                printJO();

            }).fail(function(xhr) {
                console.error("receivedJO failed:", xhr.responseText);
                parent.sendErrorMessage(
                    "Unable to update the Job Order status."
                );
            });

        } else {
            switch (data) {
                case "head":
                    parent.sendErrorMessage(
                        "Unable to receive this document because it has not been saved yet."
                    );
                    break;

                case "det":
                    parent.sendErrorMessage(
                        "Unable to receive this document because products or services have not been added yet."
                    );
                    break;

                case "both":
                    parent.sendErrorMessage(
                        "Unable to receive this document because it has not been saved yet."
                    );
                    break;

                default:
                    parent.sendErrorMessage(
                        "Unexpected response from check4print: " + data
                    );
                    break;
            }
        }

    }, "html").fail(function(xhr) {
        console.error("check4print failed:", xhr.responseText);
        parent.sendErrorMessage("Unable to validate the Job Order.");
    });
}

function removeJOEntry(type, lineId) {
    if (!lineId || (type != "parts" && type != "service")) {
        parent.sendErrorMessage("Invalid entry selected.");
        return;
    }

    var message = type == "parts"
        ? "Are you sure you want to remove this part?"
        : "Are you sure you want to remove this service?";

    if (!confirm(message)) {
        return;
    }

    var frameWindow = window;
    var mod = type == "parts" ? "deleteLine" : "deleteServiceLine";

    $.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: mod,
            lid: lineId,
            jo_no: $("#jo_no").val(),
            trace_no: $("#trace_no").val(),
            laborTotal: $("#laborTotal").val(),
            sid: Math.random()
        },
        success: function(result) {
            if (result.error) {
                parent.sendErrorMessage(result.error);
                return;
            }

            if (type == "parts") {
                redrawIItemDataTable();
            } else {
                redrawServiceDataTable();
            }

            if (result.partsTotal !== undefined) {
                $("#pgrandTotal").val(result.partsTotal);
            }

            if (result.laborTotal !== undefined) {
                $("#laborTotal").val(result.laborTotal);
            }

            if (result.grandTotal !== undefined) {
                $("#grandTotal").val(result.grandTotal);
            }

            if (result.totalPaid !== undefined) {
                $("#totalPaid").val(result.totalPaid);
            }

            if (result.remainingBalance !== undefined) {
                $("#remainingBalance").val(result.remainingBalance);
            }
        },
        error: function(xhr) {
            console.error("Remove entry error:", xhr.responseText);
            parent.sendErrorMessage(
                "Unable to remove entry. Check jo.datacontrol.php and the browser console."
            );
        }
    });
}

function reopen() {
	$.post("so.datacontrol.php", { mod: "checkBilled", jo_no: $("#jo_no").val(), trace_no: $("#trace_no").val(), sid: Math.random() }, function(stat) {
		if(stat == 'processed') {
			parent.sendErrorMessage("- It appears that this Sales Order has already been paid or billed...");
		} else {
			if(confirm("Are you sure you want to set this document to active status?") == true) {
				$.post("so.datacontrol.php", { mod: "reopen", jo_no: $("#jo_no").val(), trace_no: $("#trace_no").val(), terms: $("#terms").val(), sid: Math.random() }, function() {
					parent.viewSO($("#jo_no").val()); 
				});
			}
		}
	},"html");
}

function cancel() {

	if(confirm("Are you sure you want to Cancel this Job Order?") == true) {
		$.post("jo.datacontrol.php", { mod: "cancel", jo_no: $("#jo_no").val(), trace_no: $("#trace_no").val(), sid: Math.random() }, function(){
			alert("Job Order Successfully Cancelled!");
			parent.viewJO($("#jo_no").val());
		});
	}
}

function reuse() {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("jo.datacontrol.php", { mod: "reopen", jo_no: $("#jo_no").val(), trace_no: $("#trace_no").val(), sid: Math.random() }, function(){
			parent.viewJO($("#jo_no").val());
		});
	}
}

function printSO() {
	var jo_no = $("#jo_no").val();
	parent.printSO(jo_no);
}

function printLetterSO() {
	var jo_no = $("#jo_no").val();
	parent.printLetterSO(jo_no);
}


function viewJOComment() {

    var $p = parent.$;
    var jo_no = $("#jo_no").val();
    var trace_no = $("#trace_no").val();

    if (!jo_no) {
        parent.sendErrorMessage("Please save the Job Order first.");
        return;
    }

    // Create the dialog only once.
    if (!$p("#joCommentDialog").length) {
        $p("body").append(
            '<div id="joCommentDialog" style="display:none;">' +
                '<div id="joCommentHistory" style="' +
					'height:250px;' +
					'max-height:250px;' +
					'overflow-y:auto;' +
					'overflow-x:hidden;' +
					'padding:8px;' +
					'border:1px solid #ddd;' +
					'border-radius:4px;' +
					'background:#fafafa;' +
					'box-sizing:border-box;' +
					'"></div>' +
                '<hr>' +
                '<div class="jo-comment-form">' +
                    '<label for="joCommentStatus"><b>JO Status</b></label>' +
                    '<select id="joCommentStatus" style="width:100%;padding:7px;">' +
                        '<option value="">Loading statuses...</option>' +
                    '</select>' +

                    '<label for="joCommentText" style="display:block;margin-top:12px;">' +
                        '<b>Comment / Repair Update</b>' +
                    '</label>' +
                    '<textarea id="joCommentText" rows="4" ' +
                        'style="width:100%;box-sizing:border-box;padding:8px;" ' +
                        'placeholder="Enter repair progress or remarks..."></textarea>' +
                '</div>' +
            '</div>'
        );
    }

    var $dialog = $p("#joCommentDialog");

    $dialog.data("jo_no", jo_no);
    $dialog.data("trace_no", trace_no);

	var dialogHeight = Math.min(550, $p(window).height() - 40);

    $dialog.dialog({
		title: "JO Comments - " + jo_no,
		width: 520,
		height: dialogHeight,
		modal: true,
		resizable: false,
		buttons: [
			{
				text: "Save Comment",
				click: function() {
					saveJOCommentDialog();
				}
			},
			{
				text: "Close",
				click: function() {
					$dialog.dialog("close");
				}
			}
		]
	});

    $p("#joCommentText").val("");

    // Load the status dropdown.
    $p.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "retrieveJOCommentStatuses",
            trace_no: trace_no,
            sid: Math.random()
        },
        success: function(data) {
            var $status = $p("#joCommentStatus");

            $status.empty();
            $status.append(
                $p("<option>").val("").text("Select status...")
            );

            $p.each(data, function(i, item) {
                $status.append(
                    $p("<option>")
                        .val(item.id)
                        .text(item.stat)
                );
            });
        },
        error: function(xhr) {
            console.error("Status retrieval error:", xhr.responseText);
            parent.sendErrorMessage("Unable to load JO statuses.");
        }
    });

    loadJOCommentHistory(jo_no, trace_no);
}


function loadJOCommentHistory(jo_no, trace_no) {

    var $p = parent.$;
    var $history = $p("#joCommentHistory");

    $history.html(
        '<p style="text-align:center;">Loading comments...</p>'
    );

    $p.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "retrieveJOComments",
            jo_no: jo_no,
            trace_no: trace_no,
            sid: Math.random()
        },
        success: function(result) {

            $history.empty();

            if (result.error) {
                $history.text(result.error);
                return;
            }

            if (!result.comments || result.comments.length === 0) {
                $history.html(
                    '<p style="color:#777;text-align:center;">' +
                    'No comments recorded yet.' +
                    '</p>'
                );
                return;
            }

            $p.each(result.comments, function(i, item) {

                var $card = $p("<div>").css({
                    "border": "1px solid #ddd",
                    "border-left": "4px solid #2878c8",
                    "border-radius": "4px",
                    "padding": "10px",
                    "margin-bottom": "10px",
                    "background": "#fafafa"
                });

                var $heading = $p("<div>").css({
                    "font-weight": "bold",
                    "margin-bottom": "5px"
                });

                $heading.text(item.status_name || "Unknown status");

                var $meta = $p("<div>").css({
                    "font-size": "11px",
                    "color": "#777",
                    "margin-bottom": "8px"
                });

                $meta.html(
                    item.date_created + "&nbsp;&nbsp; By:&nbsp;&nbsp;" + (item.fullname || "Unknown User")
                );

                var $comment = $p("<div>").css({
                    "white-space": "pre-wrap",
                    "overflow-wrap": "break-word"
                });

                $comment.text(item.comment);

                $card.append($heading, $meta, $comment);
                $history.append($card);
            });

            // Show the latest comment when the history is long.
            var el = $history[0];

            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },
        error: function(xhr) {
            console.error("Comment retrieval error:", xhr.responseText);
            $history.text("Unable to load comments. Check the browser console.");
        }
    });
}


function saveJOCommentDialog() {

    var $p = parent.$;
    var $dialog = $p("#joCommentDialog");

    var jo_no = $dialog.data("jo_no");
    var trace_no = $dialog.data("trace_no");
    var stat = $p("#joCommentStatus").val();
    var comment = $p("#joCommentText").val();

    if (!stat) {
        parent.sendErrorMessage("Please select a JO status.");
        return;
    }

    if ($p.trim(comment) === "") {
        parent.sendErrorMessage("Please enter a comment.");
        return;
    }

    var $buttons = $dialog.parent().find(".ui-dialog-buttonpane button");
    $buttons.prop("disabled", true);

    $p.ajax({
        url: "jo.datacontrol.php",
        type: "POST",
        dataType: "json",
        data: {
            mod: "saveJOComment",
            jo_no: jo_no,
            trace_no: trace_no,
            stat: stat,
            comment: comment,
            sid: Math.random()
        },
        success: function(result) {

            if (result.error) {
                parent.sendErrorMessage(result.error);
                return;
            }

            if (result.success) {
                $p("#joCommentText").val("");
                loadJOCommentHistory(jo_no, trace_no);

                // Refresh the JO screen to show the new status.
                if (typeof viewJOComment === "function") {
                    viewJOComment();
                }
            }
        },
        error: function(xhr) {
            console.error("Save comment error:", xhr.responseText);
            parent.sendErrorMessage(
                "Unable to save comment. Check the browser console."
            );
        },
        complete: function() {
            $buttons.prop("disabled", false);
        }
    });
}

function soSummary() {
	var jo_no = $("#jo_no").val();
	parent.soSummary(jo_no);
}