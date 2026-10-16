

	function showEmployees(){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.list.php'></iframe>";
		$("#emplist").html(txtHTML);
		$("#emplist").dialog({title: "List of Employees", width: dWidth, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}

	function viewEmp(eid){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.details.php?eid="+eid+"'></iframe>";
		$("#empdetails").html(txtHTML);
		$("#empdetails").dialog({title: "Employee Details", width: 920, height: 590, resizable: false }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}

	function viewPeriodList(){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/payroll.periods.php?'></iframe>";
		$("#payperiods").html(txtHTML);
		$("#payperiods").dialog({title: "Payroll Periods", width: 640, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}

	function showEmpDTR() {
		$("#edtr").dialog({title: "Manage Employee DTR", width: 400, resizable: false, buttons: {
				"Manage Employee DTR": function() { manageEDTR(); }
			} 
		});
	}

	function manageEDTR() {
		$.post("hrd/payroll.datacontrol.php", { mod: "getEmpName", bio_id: $("#e_eid").val(), sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.dtr.php?id_no="+$("#e_eid").val()+"&period_id="+$("#e_eperiod").val()+"&proj_site="+$("#dtr_site").val()+"&sid="+Math.random()+"'></iframe>";
			$("#manageempdtr").html(txtHTML);
			$("#manageempdtr").dialog({title: "View & Manage Employee DTR ("+data+")", width: 1280, height: 570, resizable: false }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		},"html");
	}

	function uploadBio() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='e_uploaddtr.php'></iframe>";
		$("#changepass").html(txtHTML);
		$("#changepass").dialog({title: "Upload Data From Logbox", width: 480, height: 210, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true,
		});
	}

	function showPayAdjust(){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/payroll.adjustments.php'></iframe>";
		$("#payrollAdjustment").html(txtHTML);
		$("#payrollAdjustment").dialog({title: "Salary Adjustment", width: 800, height: 550, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showLoans(){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.loanlist.php'></iframe>";
		$("#emploanlist").html(txtHTML);
		$("#emploanlist").dialog({title: "Employee Loans & Advances", width: 1280, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}

	function showHolidays(){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/payroll.holidays.php'></iframe>";
		$("#manageHolidays").html(txtHTML);
		$("#manageHolidays").dialog({title: "Holidays & Breaks", width: 640, height: 570, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function viewHoliday(fid){
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/holiday.details.php?fid="+fid+"'></iframe>";
		$("#holidayDetails").html(txtHTML);
		$("#holidayDetails").dialog({title: "Holidays & Breaks", width: 400, height: 280, resizable: false}).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
			
		});
	}

	function showPaySum() {
	
					var buttonz ={};
						//buttonz["Generate Excel (Prev)"] = function(){ window.open("export/ppayroll_summaryOOP.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"","Pay Slip","location=1,status=1,scrollbars=1,width=640,height=720"); };
						//buttonz["Generate PDF (Prev)"] = function(){ window.open("print/ppayroll_summaryOOP.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"","Pay Slip","location=1,status=1,scrollbars=1,width=640,height=720");		};
						buttonz["Process"] = function(){		
																		var post_loan;
																		if($('#finalizePayroll').is(':checked')){
																			post_loan = 'Y';
																		} else {
																			post_loan = 'N';
																		}
																		window.open("/hrd_reports/payroll_summary.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"&post_loan="+post_loan+"","Pay Slip","location=1,status=1,scrollbars=1,width=640,height=720");
																		//window.open("export/payroll_summaryOOP.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"&post_loan="+post_loan+"","Pay Slip","location=1,status=1,scrollbars=1,width=640,height=720");
																	};
						/*
							buttonz["Generate PDF"] = function(){
												var post_loan;
												if($('#finalizePayroll').is(':checked')){
													post_loan = 'Y';
												} else {
													post_loan = 'N';
												}
												window.open("print/payroll_summaryOOP.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"&post_loan="+post_loan+"","Pay Slip","location=1,status=1,scrollbars=1,width=640,height=720");

											};
						

						
						if(data['userid']=='1' ||data['userid']=='4'){

							buttonz["Unpost Payroll"] = function(){	 window.open("print/pay_unpost.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"","Unposting","location=1,status=1,scrollbars=1,width=640,height=720");	};
			
						}else{

						}										
						*/

						$("#paySummary").dialog({title: "Generate Pay Summary", width: 800, resizable: false, buttons: buttonz 
						
						});
	}

	function prevPayroll(){
		$("#paySummaryprev").dialog({
			title : "Previous Payroll",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Generate": function() { $(this).dialog("close"); }
			}
		});

	}

	function processPayroll(){
		$("#paySummary2").dialog({
			title : "Process Payroll",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Process": function() { 
					$(this).dialog("close");
					window.open("/hrd_reports/payroll_summary.php?period_id="+$("#paySummary_cutoff2").val()+"&proj_site="+$("#paySummary_site").val()+"","Process","location=1,status=1,scrollbars=1,width=640,height=640");
				}
			}
		});

	}

	function genExcelPayroll(){
		$("#paySummary3").dialog({
			title : "Payroll Summary (Excel)",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Generate": function() { $(this).dialog("close"); 		
				window.open("/hrd_reports/payroll_summaryex.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"","Process","location=1,status=1,scrollbars=1,width=640,height=640");
				}
			}
		});

	}

	function genPayslipPayroll(){
		$("#paySummary3").dialog({
			title : "Payslip",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Generate": function() { $(this).dialog("close"); 
				window.open("/hrd_reports/payslip.php?period_id="+$("#paySummary_cutoff").val()+"&proj_site="+$("#paySummary_site").val()+"","Process","location=1,status=1,scrollbars=1,width=640,height=700");
				}
			}
		});

	}

	function genTransmittal(){
		$("#paySummary3").dialog({
			title : "Transmittal",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Generate": function() { $(this).dialog("close"); 
				window.open("/hrd_reports/transmittal_cb.php?period_id="+$("#paySummary_cutoff").val()+"","Process","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			}
		});

	}

	function postPayroll(){
		$("#paySummary2").dialog({
			title : "Post Payroll",
			width: 400,
			resizable: false,
			modal: true,
			buttons: {
				"Process": function() { $(this).dialog("close");
				window.open("/hrd_reports/close_payroll.php?period_id="+$("#paySummary_cutoff2").val()+"","Process","location=1,status=1,scrollbars=1,width=640,height=720");
				 }
			}
		});

	}


	/*
function showPayAdjust() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='payroll.adjustments.php'></iframe>";
		$("#payrollAdjustment").html(txtHTML);
		$("#payrollAdjustment").dialog({title: "Salary Adjustment", width: 800, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
}

	function showLoans()	
	{
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.loanlist.php'></iframe>";
		$("#emploanlist").html(txtHTML);
		$("#emploanlist").dialog({title: "Employee Loans & Advances", width: 1280, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}

		function showHolidays() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='payroll.holidays.php'></iframe>";
		$("#manageHolidays").html(txtHTML);
		$("#manageHolidays").dialog({title: "Holidays & Breaks", width: 640, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}


	*/



