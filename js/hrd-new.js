function save_record() {
	if(confirm("Are you sure you want to save changes to this record?") == true) {
		var msg = "";
		
		//Catch Required Fields
		if ($("#emp_idno").val() == "") { msg = msg + "Error: ID No. information is required<br/>"; }
		if ($("#emp_fname").val() == "") { msg = msg + "Error: First Name information is required<br/>"; }
		if($("#emp_mname").val() == "") { msg = msg + "Error: Middle Name information is required<br/>"; }
		if($("#emp_sex").val() == "") { msg = msg + "Error: Gender information is required<br/>"; }
		if($("#emp_cstat").val() == "") { msg = msg + "Error: Civil Status information is required<br/>"; }
		
		if($("#emp_desg").val() == "") { msg = msg + "Error: Designation information is required<br/>"; }
		if($("#emp_dept").val() == "") { msg = msg + "Error: Department information is required<br/>"; }
		if($("#emp_ptype").val() == "") { msg = msg + "Error: Payroll Type information is required<br/>"; }
		if($("#emp_stat").val() == "") { msg = msg + "Error: Employment Status information is required<br/>"; }
		if($("#emp_atmbank").val() != '0') { if($("#emp_bank").val() == '') { msg = msg + "Error: Account No. is Required when chosing salary thru ATM.<br/>"; }}
		
		if($("#emp_rate").val() == "") { msg = msg + "Error: Basic Rate information is required<br/>"; }

		
				
		if(msg != "") {
			parent.sendErrorMessage(msg);
		} else {
			
			var xurl = $("#empdetails").serialize();
			
			$.ajax({
				type: "POST",
				url: "hrd.new_ee_record.php",
				data: xurl,
				success: function (echoes) {
					switch (echoes) {
					case "1":
							alert("Employee Record Successfully Saved!");
							parent.closeDialog("#e_details"); parent.showEmployees();
							break;
						case "2":
							parent.sendErrorMessage("Error: A similar Employee Record already existed on the masterfile!");
							break;
						case "3":
							parent.sendErrorMessage("Error: A similar ID No. already existed on the masterfile!");
							break;
						case "4":
							parent.sendErrorMessage("Error: ID No. already in-use by another employee!");
							break;
						default:
							alert("Employee Record Successfully Updated!");
							break;
					}
				}
			});
		}
	}
}

function delete_record(rid) {
	if(confirm("Are you sure you want to archive this file to prevent it from being used in succeeding transactions?") == true) {
		$.post("misc-data.php", { mod: "deleteEmployee", rid: rid, sid: Math.random() }, function() {
			alert("Record successfully marked as \"Deleted\"!");
			parent.closeDialog("#e_details");
			parent.showEmployees();
		});
	}
}