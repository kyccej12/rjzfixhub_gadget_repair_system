/* Percentage of Screen Size */
var wWidth = $(window).width();
var xWidth = wWidth * 0.8;
//var xWidth = wWidth * 0.95;

$(document).ready(function() {
    var warningTimer = null;
    var logoutTimer = null;
    var countdownTimer = null;
    var warningOpen = false;
    var redirecting = false;
    var lastPing = 0;
	var refreshInProgress = false;
	var pendingRefreshCallbacks = [];

   	var WARNING_AFTER = 25 * 60 * 1000; // 25 minutes
	var LOGOUT_AFTER = 5 * 60 * 1000;   // 5-minute countdown
	var PING_INTERVAL = 15 * 1000;  // Check every 15 seconds, if needed

	// var WARNING_AFTER = 60 * 1000; // 1 minute
	// var LOGOUT_AFTER = 60 * 1000;  // 1-minute countdown
	// var PING_INTERVAL = 15 * 1000; // Server refresh interval


    // Adjust if your application URL is different.
  	var LOGIN_URL = "/rjzfixhub/login/index.php?expired=1";
   	var ACTIVITY_URL = "/rjzfixhub/session.activity.php";

    var $dialog = $(
        '<div id="sessionTimeoutDialog" style="display:none;">' +
        '<p>Your session will expire soon due to inactivity.</p>' +
		'<p>Time remaining: <strong id="sessionCountdown">01:00</strong></p>' +
        '<p>Would you like to stay logged in?</p>' +
        '</div>'
    );

    $("body").append($dialog);

    function redirectToLogin() {
		console.trace("[Session] redirectToLogin() was called");

		if (redirecting) {
			return;
		}

		redirecting = true;

		clearTimeout(warningTimer);
		clearTimeout(logoutTimer);
		clearInterval(countdownTimer);

		window.location.href = LOGIN_URL;
	}

    // Server-side validation; never assume the session is active.
    
	function checkSession(callback) {
		$.ajax({
			url: ACTIVITY_URL,
			type: "POST",
			data: { action: "check" },
			dataType: "json",
			cache: false,

			success: function(response) {
				if (response && response.status === "ok") {
					if (callback) {
						callback(true);
					}
				} else {
					redirectToLogin();
				}
			},

			error: function(xhr) {
				if (xhr.status === 401) {
					redirectToLogin();
					return;
				}

				if (callback) {
					callback(false);
				}
			}
		});
	}


	function refreshSession(callback, reason) {
		reason = reason || "Manual refresh";

		// If a request is already running, wait for its result.
		if (refreshInProgress) {
			console.log("[Session] Refresh already in progress; waiting.");

			if (callback) {
				pendingRefreshCallbacks.push(callback);
			}

			return;
		}

		refreshInProgress = true;

		// Register the callback for this request.
		pendingRefreshCallbacks = [];

		if (callback) {
			pendingRefreshCallbacks.push(callback);
		}

		console.log("[Session] " + reason + ": sending refresh request...");
		console.log("[Session] Browser time:", new Date());

		$.ajax({
			url: ACTIVITY_URL,
			type: "POST",
			data: { action: "refresh" },
			dataType: "json",
			cache: false,

			success: function(response) {
				console.log("[Session] Refresh response:", response);

				var valid = response && response.status === "ok";

				if (valid) {
					console.log("[Session] Refresh successful.");
				} else {
					console.error("[Session] Unexpected response:", response);
				}

				var callbacks = pendingRefreshCallbacks.slice(0);
				pendingRefreshCallbacks = [];

				$.each(callbacks, function(i, fn) {
					fn(valid);
				});
			},

			error: function(xhr, status, error) {
				console.error(
					"[Session] Refresh failed:",
					xhr.status,
					xhr.responseText
				);

				var valid = false;

				var callbacks = pendingRefreshCallbacks.slice(0);
				pendingRefreshCallbacks = [];

				if (xhr.status === 401) {
					redirectToLogin();
				}

				$.each(callbacks, function(i, fn) {
					fn(valid);
				});
			},

			complete: function() {
				refreshInProgress = false;
			}
		});
	}



    function closeWarning() {
        if (warningOpen) {
            warningOpen = false;
            clearInterval(countdownTimer);

            if ($dialog.hasClass("ui-dialog-content")) {
                $dialog.dialog("close");
            }
        }
    }

    function startTimers() {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);

        warningTimer = setTimeout(
            showWarning,
            WARNING_AFTER
        );
    }

    function showWarning() {
		if (redirecting || warningOpen) {
			return;
		}

		clearTimeout(warningTimer);
		clearTimeout(logoutTimer);
		clearInterval(countdownTimer);

		console.log("[Session] Warning displayed at:", new Date());

		warningOpen = true;

		var secondsLeft = Math.floor(LOGOUT_AFTER / 1000);

		$dialog.dialog({
			title: "Session Expiring",
			modal: true,
			closeOnEscape: false,
			width: 400,
			resizable: false,

			open: function(event, ui) {
				$(this).closest(".ui-dialog")
					.find(".ui-dialog-titlebar-close")
					.hide();
			},

			buttons: {
				"Stay Logged In": function() {
					var $this = $(this);

					$this.dialog("option", "buttons", {});

					console.log("[Session] Stay Logged In clicked.");

					refreshSession(function(valid) {
						if (!valid || redirecting) {
							redirectToLogin();
							return;
						}

						console.log("[Session] Refresh accepted. Resetting timers.");

						clearTimeout(logoutTimer);
						clearInterval(countdownTimer);
						clearTimeout(warningTimer);

						warningOpen = false;
						$this.dialog("close");

						startTimers();
					}, "Stay Logged In");
				},

				"Log Out": function() {
					console.log("[Session] Log Out clicked.");
					redirectToLogin();
				}
			}
		});

		function updateCountdown() {
			var minutes = Math.floor(secondsLeft / 60);
			var seconds = secondsLeft % 60;

			$("#sessionCountdown").text(
				(minutes < 10 ? "0" : "") + minutes + ":" +
				(seconds < 10 ? "0" : "") + seconds
			);

			if (secondsLeft <= 0) {
				clearInterval(countdownTimer);
				clearTimeout(logoutTimer);
				redirectToLogin();
				return;
			}

			secondsLeft--;
		}

		updateCountdown();

		countdownTimer = setInterval(updateCountdown, 1000);

		logoutTimer = setTimeout(function() {
			redirectToLogin();
		}, LOGOUT_AFTER);
	}
	
	function recordActivity() {
		// Ignore activity while warning dialog is open.
		if (redirecting || warningOpen) {
			return;
		}

		// Reset the inactivity warning timer.
		startTimers();

		// Do not send another request while a refresh is in progress.
		if (refreshInProgress) {
			return;
		}

		var now = new Date().getTime();

		if (now - lastPing >= PING_INTERVAL) {
			lastPing = now;

			console.log("[Session] Automatic activity refresh:", new Date());

			refreshSession(null, "Automatic activity");
		}
	}

    // Track actual user interactions.
    $(document).on(
        "mousemove.sessionTimeout " +
        "mousedown.sessionTimeout " +
        "keydown.sessionTimeout " +
        "scroll.sessionTimeout " +
        "touchstart.sessionTimeout " +
        "click.sessionTimeout",
        recordActivity
    );

    // Start inactivity monitoring.
    startTimers();
});

$(document).ajaxError(function(event, xhr, settings, error) {
    if (xhr.status === 401) {
        console.warn("Unauthorized AJAX request:", settings.url);
    }
});

var monthNames = ["January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

function popSaver() {
	$('#popSaver').fadeIn('fast').delay(1000).fadeOut('slow');
}

function stripComma(val) {
	return val.replace(/,/g,"");
}

function kSeparator(val) {
	var val = parseFloat(val);
		val = val.toFixed(2);
	var a = val.split(".");
	var kValue = a[0];
	//if(a[1] == '' || a[1] == 'undefined') { a[1] = '00'; }

	var sRegExp = new RegExp('(-?[0-9]+)([0-9]{3})');
	while(sRegExp.test(kValue)) {
		kValue = kValue.replace(sRegExp, '$1,$2');
	}

	if(a[1] != "") {
		kValue = kValue + "." + a[1]; 
		return kValue;
	} else {
		return kValue + ".00";
	}
}
	
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}	

function sendErrorMessage(msg) {
	$("#message").html(msg);
	$("#errorMessage").dialog({
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"OK": function() { $(this).dialog("close"); }
		}
	})
}

function showLoaderMessage() {
	$("#loaderMessage").dialog({ show: 'fade', width: 480, height: 180, closable: false, modal: true,  open: function(event, ui) {
        $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    }});
}

function showMenu() {
	$("#mainMenu").dialog({show: 'fade', title: "Main Menu", width: 1124, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function closeDialog(frame) {
	$(frame).dialog("close");
}

/* JO */

function showJO() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jo.list.php?sid="+Math.random()+"'></iframe>";
	$("#jolist").html(txtHTML);
	$("#jolist").dialog({title: "Job Order Summary", width: xWidth, height: 600,resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}
function printTerms() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/jo.terms.php?sid="+Math.random()+"'></iframe>";
	$("#joprint").html(txtHTML);
	$("#joprint").dialog({title: "Terms & Conditions", width: 850, height: 700,resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}
function viewJO(jo_no) {
	var txtHTML = "<iframe id='frmcust' frameborder='0' width='100%' height='100%' src='jo.details.php?jo_no="
		+ encodeURIComponent(jo_no) + "&sid=" + Math.random() + "'></iframe>";

	$("#jodetails").html(txtHTML);

	$("#jodetails").dialog({
		title: "Job Order Details",
		width: 1120,
		height: 640,
		resizable: false,
		modal: true,
		open: function() {
			// Create a separate header for the buttons
			if ($("#joHeaderButtons").length === 0) {
				$("<div id='joHeaderButtons'></div>").insertBefore("#jodetails");
			}

			$("#joHeaderButtons").css({
				display: "flex",
				justifyContent: "space-between",
				alignItems: "center",
				width: "100%",
				boxSizing: "border-box",
				padding: "5px 8px",
				gap: "10px"
			}).empty();
		}
	});
}

function callJOFunction(functionName) {
	var iframe = $("#jodetails").find("iframe#frmcust")[0];

	if (!iframe || !iframe.contentWindow) {
		console.error("JO details iframe not found.");
		return;
	}

	var joWindow = iframe.contentWindow;
	var fn = joWindow[functionName];

	if (typeof fn === "function") {
		var args = Array.prototype.slice.call(arguments, 1);
		fn.apply(joWindow, args);
	} else {
		console.error(
			"Function " + functionName +
			" is not available in the JO iframe."
		);
	}
}

function printJO(jo_no, rePrint) {
    console.log("JO Number:", jo_no);
    console.log("Reprint:", rePrint);

    if (!jo_no) {
        alert("Please save the Job Order first.");
        return;
    }

    var printUrl = "print/jo.print.php?jo_no=" +
        encodeURIComponent(jo_no) +
        "&rePrint=" + encodeURIComponent(rePrint || 0) +
        "&sid=" + Math.random();

    // Access the print container in the parent index.php
    var $printDialog = parent.$("#joprint");

    console.log("Print dialog element:", $printDialog.length);

    if (!$printDialog.length) {
        console.error("#joprint was not found in parent page.");
        return;
    }

    $printDialog.html(
        "<iframe frameborder='0' " +
        "style='width:100%;height:100%;border:0;' " +
        "src='" + printUrl + "'></iframe>"
    );

    $printDialog.dialog({
        title: "PRINT >> JOB ORDER",
        width: 850,
        height: 700,
        resizable: true,
        modal: true,
        autoOpen: true
    });

    // Use the parent's jQuery UI dialog
    $printDialog.dialog("open");
}

/* Sales */
function showOptions() {
	$("#salesReportMain").dialog({ show: 'fade', title: "Main Menu", width: 980, height: 580, closable: false, modal: true,  open: function(event, ui) {
      
    }});
}

function showItems() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.master.php?sid="+Math.random()+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Product & Supplies", width: xWidth, height: 600,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showItemInfo(rid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.details.php?id="+rid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Product Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function showSgroup() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='isubgroup.master.php'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Inventory Sub Group List", width: 800, height: 400,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportProducts(item_code,unit,mydate) {
	window.open("export/products.php?sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showSI() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='si.list.php'></iframe>";
	$("#silist").html(txtHTML);
	$("#silist").dialog({title: "Sales Invoice Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSI(docno) {
	var txtHTML = "<iframe id='siFrame' frameborder=0 width='100%' height='100%' src='si.details.php?docno="+docno+"&sid="+Math.random()+"'></iframe>";
	$("#sidetails").html(txtHTML);
	$("#sidetails").dialog({title: "Sales Invoice Details", width: xWidth, height: 600, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true,
		"maximize": function(event, ui) { 
			$('#siFrame').contents().find('#details').css({"height":"55vh", "overflow-x": "auto"});
		},
		"restore": function(event, ui) { 
			$('#siFrame').contents().find('#details').css({"height":"120px", "overflow-x": "auto"});
		}
	});
}

function printSI(docno,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/si.print.php?docno="+docno+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#siprint").html(txtHTML);
	$("#siprint").dialog({title: "PRINT >> SALES INVOICE", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function jumpSIPage(pageNum,stext,sdetails) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='si.list.php?page="+pageNum+"&searchtext="+stext+"&includeDetails="+sdetails+"'></iframe>";
	$("#silist").html(txtHTML);
	$("#silist").dialog({title: "Sales Invoice Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showCR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='cr.list.php'></iframe>";
	$("#crlist").html(txtHTML);
	$("#crlist").dialog({title: "Collection Receipts Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewCR(trans_no) {
	var txtHTML = "<iframe id='crFrame' frameborder=0 width='100%' height='100%' src='cr.details.php?trans_no="+trans_no+"'></iframe>";
	$("#crdetails").html(txtHTML);
	$("#crdetails").dialog({title: "Collection Receipt Details", width: 1200, height: 600, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true,
		"maximize": function(event, ui) { 
			$('#crFrame').contents().find('#details').css({"height":"52vh", "overflow-x": "auto"});
		},
		"restore": function(event, ui) { 
			$('#crFrame').contents().find('#details').css({"height":"120px", "overflow-x": "auto"});
		}
	});	
}

function printCR(trans_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/cr.print.php?trans_no="+trans_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#crprint").html(txtHTML);
	$("#crprint").dialog({title: "PRINT >> COLLECTION RECEIPT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPOList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='po.list.php'></iframe>";
	$("#polist").html(txtHTML);
	$("#polist").dialog({title: "Purchase Order Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPO(po_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='po.details.php?po_no="+po_no+"'></iframe>";
	$("#podetails").html(txtHTML);
	$("#podetails").dialog({title: "Purchase Order Details", width: xWidth, height: 540, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printPO(po_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/po.print.php?po_no="+po_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PURCHASE ORDER", width: 560, height: 620, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showRRList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rr.list.php'></iframe>";
	$("#rrlist").html(txtHTML);
	$("#rrlist").dialog({title: "Receiving Report Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewRR(rr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rr.details.php?rr_no="+rr_no+"'></iframe>";
	$("#rrdetails").html(txtHTML);
	$("#rrdetails").dialog({title: "Receiving Report Details", width: xWidth, height: 560, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}
function jumpRRPage(pageNum,stext,sdetails) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rr.list.php?page="+pageNum+"&searchtext="+stext+"&includeDetails="+sdetails+"'></iframe>";
	$("#rrlist").html(txtHTML);
	$("#rrlist").dialog({title: "Receiving Report Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printRR(rr_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/rr.print.php?rr_no="+rr_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#rrprint").html(txtHTML);
	$("#rrprint").dialog({title: "PRINT >> RECEIVING REPORT", width: 560, height: 620, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSW() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.list.php'></iframe>";
	$("#swlist").html(txtHTML);
	$("#swlist").dialog({title: "Stocks Withdrawal Slip Summary", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSW(sw_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.details.php?sw_no="+sw_no+"'></iframe>";
	$("#swdetails").html(txtHTML);
	$("#swdetails").dialog({title: "Stocks Withdrawal Slip Details", width: xWidth, height: 560, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printSW(sw_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/sw.print.php?sw_no="+sw_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS WITHDRAWAL SLIP", width: 560, height: 620, resizable: true, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSRR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.list.php'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Stocks Receiving Slip Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSRR(srr_no) {
	var txtHTML = "<iframe id='siFrame' frameborder=0 width='100%' height='100%' src='srr.details.php?srr_no="+srr_no+"'></iframe>";
	$("#srrdetails").html(txtHTML);
	$("#srrdetails").dialog({title: "Stocks Receiving Slip Details", width: 960, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true,
		"maximize": function(event, ui) { 
			$('#siFrame').contents().find('#details').css({"height":"47vh", "overflow-x": "auto"});
		},
		"restore": function(event, ui) { 
			$('#siFrame').contents().find('#details').css({"height":"160px", "overflow-x": "auto"});
		}
	});
}

function jumpSRRPage(pageNum,stext,sdetails) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.list.php?page="+pageNum+"&searchtext="+stext+"&includeDetails="+sdetails+"'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Stocks Receiving Slip Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printSRR(srr_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/srr.print.php?srr_no="+srr_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS RECEIVING SLIP", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPCV() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pcv.list.php'></iframe>";
	$("#pcvlist").html(txtHTML);
	$("#pcvlist").dialog({title: "Petty Cash Voucher", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPCV(pcv_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pcv.details.php?pcv_no="+pcv_no+"'></iframe>";
	$("#pcvdetails").html(txtHTML);
	$("#pcvdetails").dialog({title: "Petty Cash Voucher", width: 960, height: 400, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printBarcode(item_code) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/itembarcode.php?item="+item_code+"&sid="+Math.random()+"'></iframe>";
	$("#barcode").html(txtHTML);
	$("#barcode").dialog({title: "Print - Barcode", width: 400, height: 200, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

/* Inventory Management */
function showIBook() {
	$("#ibook_dtf").datepicker(); $("#ibook_dt2").datepicker(); 
	$("#inventorybook").dialog({title: "Inventory Summary", width: 480 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function processInventory() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function jumpIBookPage(page,stxt,group,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?page="+page+"&searchtext="+stxt+"&group="+group+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewStockcard(item_code,lot_no,expiry,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='stockcard.php?item_code="+item_code+"&lot_no="+lot_no+"&expiry="+expiry+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#stockcard").html(txtHTML);
	$("#stockcard").dialog({title: "Inventory Stockcard", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportStockcard(item_code,unit,dtf,dt2) {
	window.open("export/stockard.php?item_code="+item_code+"&unit="+unit+"&dtf="+dtf+"&dt2="+dt2+"&sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function exportInventoryNow() {
	window.open("export/ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"&sid="+Math.random()+"","Inventory Book","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showPhy() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.list.php'></iframe>";
	$("#phylist").html(txtHTML);
	$("#phylist").dialog({title: "Physical Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}
function viewPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.details.php?doc_no="+doc_no+"'></iframe>";
	$("#phydetails").html(txtHTML);
	$("#phydetails").dialog({title: "Physical Inventory Form", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}


function printPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/phy.print.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Physical Inventory Form", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

/* End Inventory */


/* REPORTS */

function showDSCR() {
	$("#sinq_date").datepicker();
	$("#salesinquiry").dialog({
		title: "Daily Sales & Collection Report", 
		width: 400,
		buttons: {
			"Generate Report": function() {
				var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/dscr.php?date="+$("#sinq_date").val()+"&branch="+$("#sinq_branch").val()+"&sid="+Math.random()+"'></iframe>";
				$("#report3").html(txtHTML);
				$("#report3").dialog({title: "Daily Sales & Collection Report", width: 560, height: 620, resizable: true }).dialogExtend({
					"closable" : true,
					"maximizable" : true,
					"minimizable" : true
				});
			},
			"Close": function() { 
				$(this).dialog("close");
			}
		}
	});
}

function generateInquiry() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/dscr.php?date="+$("#sinq_date").val()+"&branch="+$("#sinq_branch").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Daily Sales & Collection Report", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showSalesReport() {

	$("#ds_dtf").datepicker({ changeMonth: true, changeYear: true });
	$("#ds_dt2").datepicker({ changeMonth: true, changeYear: true });

	$("#dailySales").dialog({
		title: "Sales Report",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report": function() { 
				var txtHTML = "<iframe id='frmSalesReport' frameborder=0 width='100%' height='100%' src='reports/salesreport.php?dtf="+$("#ds_dtf").val()+"&dt2="+$("#ds_dt2").val()+"&group="+$("#ds_group").val()+"&desc="+$("#ds_desc").val()+"&sid="+Math.random()+"'></iframe>";
				$("#report1").html(txtHTML);
				$("#report1").dialog({title: "Sales Report", width: 560, height: 620, resizable: true }).dialogExtend({
					"closable" : true,
					"maximizable" : true,
					"minimizable" : true
				});
			},
			"Export Report to Excel": function() {
				window.open("export/salesreport.php?group="+$("#ds_group").val()+"&dtf="+$("#ds_dtf").val()+"&dt2="+$("#ds_dt2").val()+"&sid="+Math.random()+"","Sales Report","location=1,status=1,scrollbars=1,width=640,height=720");
				
			}
		}
	});
}

function showInventoryReport() {

	$("#ir_dtf").datepicker({ changeMonth: true, changeYear: true });
	$("#ir_dt2").datepicker({ changeMonth: true, changeYear: true });

	$("#inventoryReport").dialog({
		title: "Inventory Report",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report in Excel": function() {
				window.open("export/ibook.php?group="+$("#ir_group").val()+"&warehouse="+$("#ir_location").val()+"&dtf="+$("#ir_dtf").val()+"&dt2="+$("#ir_dt2").val()+"&sid="+Math.random()+"","Inventory Book","location=1,status=1,scrollbars=1,width=640,height=720");
			}
		}
	});
}

function showAR() {

	$("#ar_asof").datepicker({ changeMonth: true, changeYear: true });

	$("#arSummary").dialog({
		title: "Accounts Receivable Summary",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report": function() {
				var txtHTML = "<iframe id='frmGrossProfitSales' frameborder=0 width='100%' height='100%' src='reports/ar_summary.php?cid="+$("#ar_customer").val()+"&asof="+$("#ar_asof").val()+"&sid="+Math.random()+"'></iframe>";
				$("#report2").html(txtHTML);
				$("#report2").dialog({title: "Gross Profit Report", width: 560, height: 620, resizable: true }).dialogExtend({
					"closable" : true,
					"maximizable" : true,
					"minimizable" : true
				});

			}
		}
	});
}

function showGrossProfitSales() {

	$("#gps_dtf").datepicker({ changeMonth: true, changeYear: true });
	$("#gps_dt2").datepicker({ changeMonth: true, changeYear: true });

	$("#grossProfitSales").dialog({
		title: "Gross Profit Sales Report",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report": function() {
				var txtHTML = "<iframe id='frmGrossProfitSales' frameborder=0 width='100%' height='100%' src='reports/grossprofitsales.php?dtf="+$("#gps_dtf").val()+"&dt2="+$("#gps_dt2").val()+"&group="+$("#gps_group").val()+"&sid="+Math.random()+"'></iframe>";
				$("#report2").html(txtHTML);
				$("#report2").dialog({title: "Gross Profit Report", width: 560, height: 620, resizable: true }).dialogExtend({
					"closable" : true,
					"maximizable" : true,
					"minimizable" : true
				});


			 },
			 "Export Report to Excel": function() {
				window.open("export/grossprofitsales.php?group="+$("#gps_group").val()+"&dtf="+$("#gps_dtf").val()+"&dt2="+$("#gps_dt2").val()+"&sid="+Math.random()+"","Gross Profit Sales","location=1,status=1,scrollbars=1,width=640,height=720");
				
			}
		}
	});
}

function showReceiveSummary() {

	$("#grs_dtf").datepicker({ changeMonth: true, changeYear: true });
	$("#grs_dt2").datepicker({ changeMonth: true, changeYear: true });

	$("#goodsReceivedSummary").dialog({
		title: "Summary of Goods Received",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report": function() { }
		}
	});
}

function showWithdrawalReport() {

	$("#gws_dtf").datepicker({ changeMonth: true, changeYear: true });
	$("#gws_dt2").datepicker({ changeMonth: true, changeYear: true });

	$("#goodsWithdrawnSummary").dialog({
		title: "Summary of Goods Withdrawn",
		width: 400,
		resizable: false,
		modal: true,
		buttons: {
			"Generate Report": function() { }
		}
	});
}

function showInventoryStockcard(itemCode) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='stockcard.php?item_code="+itemCode+"'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Item Inventory Stockcard", width: xWidth, height: 540,resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showPettyCashSummary() {
	$("#pcs_dtf").datepicker(); $("#pcs_dt2").datepicker();
	$("#pettyCashSummary").dialog({
		title: "Petty Cash Voucher Summary", 
		width: 400,
		resizable: false,
		modal: false,
		buttons: {
			"Generate Report": function() {
				var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/pettycashsummary.php?dtf="+$("#pcs_dtf").val()+"&dt2="+$("#pcs_dt2").val()+"&payee="+$("#pcs_name").val()+"&type="+$("#pcs_type").val()+"&sid="+Math.random()+"'></iframe>";
				$("#report6").html(txtHTML);
				$("#report6").dialog({title: "Petty Cash Voucher Summary", width: 560, height: 620, resizable: true }).dialogExtend({
					"closable" : true,
					"maximizable" : true,
					"minimizable" : true
				});
			 },
			 "Export Report to Excel": function() {
				window.open("export/pettycashsummary.php?dtf="+$("#pcs_dtf").val()+"&dt2="+$("#pcs_dt2").val()+"&payee="+$("#pcs_name").val()+"&type="+$("#pcs_type").val()+"&sid="+Math.random()+"","PCV Summary","location=1,status=1,scrollbars=1,width=640,height=720");
			}
			
			
		}
	});
}

/* Customer Details */

function showCustProfiles(mod) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='customer.master.php?mod="+mod+"'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Customers List", width: xWidth, height: 540,resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showCustomerInfo(fid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='customer.details.php?fid="+fid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Customers Info", width: 1024, height: 450, resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function addCustomer() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='customer.details.php?mod=1'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Customers Info", width: 1024, height: 520,resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}


/* End Customer Details */

/* Tools & Others Modules */

function showCust(mod) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.master.php?mod="+mod+"'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Suppliers/Vendors", width: xWidth, height: 540,resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function addPayee() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.details.php?mod=1'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Payees & Suppliers", width: 1024, height: 520,resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showPayeeInfo(fid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.details.php?fid="+fid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Payee/Supplier Info", width: 1024, height: 520, resizable: false, show: 'fade', modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showUsers() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.master.php'></iframe>";
	$("#userlist").html(txtHTML);
	$("#userlist").dialog({title: "System Users", width: xWidth, height: 540, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showChangePass() {
	$("#userChangePass").dialog({ title: "Update Password", width: 480, height: 190, resizable: false, modal: true, show: 'fade', buttons: {
					"Update my Password": function() {
						var msg = "";

						if($("#pass1").val() == "" || $("#pass2").val() == "") { msg = msg + "The system cannot accept empty password.<br/>"; }
						if($("#pass1").val() != $("#pass2").val()) { msg = msg + "New Passwords do not match.<br/>"; }
					
						if(msg!="") {
							sendErrorMessage(msg);
						} else {

							$.post("src/sjerp.php", { mod: "changePassword", uid:  $("#myUID").val(), pass: $("#pass1").val(), sid: Math.random() },function() {
								alert("You have successfully updated your password!");
								$("#userChangePass").dialog("close");
							});
						}
					},
					"Continue with the System": function () { $(this).dialog("close"); }
				} }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

/* User Profiles */

function addUser() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.details.php'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 450, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

function viewUserInfo(eid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.update.php?eid="+eid+"'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 380, resizable: false, show: 'fade' }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

function showChangePass() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.changepass.php'></iframe>";
	$("#changepass").html(txtHTML);
	$("#changepass").dialog({title: "Update Password", width: 480, height: 190, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

function showUserDetails(uid) {
	var uname;
	$.post("src/sjerp.php", { mod: "getUinfo", uid: uid, sid: Math.random() }, function(data) {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.rights.php?uid="+uid+"'></iframe>";
		$("#userrights").html(txtHTML);
		$("#userrights").dialog({title: "User Access Rights ("+data+")", width: 560, height: 670, resizable: false}).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true,
		});
	 },"html");
}