<?php
ini_set("display_errors","on");
    session_start();
    require_once("handlers/session.guard.php");
	require_once 'handlers/_generics.php';
	$con = new _init;
    $bid = $_SESSION['branchid'];

    if($_SESSION['authkey']) {
		$con->updateTstamp($_SESSION['authkey']);
	}

function updateAmount($jo_no, $labor = null) {
    global $con, $bid;

    $jo = $con->escapeString($jo_no);
    $branch = (int)$bid;

    // Get the existing labor cost and current status.
    $header = $con->getArray("
        SELECT IFNULL(labor, 0), status
        FROM jo_header
        WHERE jo_no = '$jo'
          AND branch = '$branch'
    ");

    $laborTotal = ($labor !== null)
        ? (float)$con->formatDigit($labor)
        : (float)$header[0];

    // Calculate parts total.
    $parts = $con->getArray("
        SELECT IFNULL(SUM(amount_due), 0)
        FROM jo_details
        WHERE jo_no = '$jo'
    ");

    $partsTotal = (float)$parts[0];
    $transactionTotal = $partsTotal + $laborTotal;

    // Calculate all payments received for this JO and branch.
    $payments = $con->getArray("
        SELECT IFNULL(SUM(amount), 0)
        FROM jo_payments
        WHERE jo_no = '$jo'
          AND branch = '$branch'
    ");

    $totalPaid = (float)$payments[0];

    $remainingBalance = $transactionTotal - $totalPaid;

    // Avoid negative balances caused by rounding or overpayment.
    if ($remainingBalance < 0) {
        $remainingBalance = 0;
    }

    $transactionTotal = round($transactionTotal, 2);
    $totalPaid = round($totalPaid, 2);
    $remainingBalance = round($remainingBalance, 2);

    // Mark the JO Completed when fully paid.
    // Do not overwrite Cancelled status.
    if ($remainingBalance <= 0 && $transactionTotal > 0) {
        $con->dbquery("
            UPDATE jo_header
            SET pamount = '$partsTotal',
                labor = '$laborTotal',
                amount = '$transactionTotal',
                remaining_balance = '0.00',
                status = 'Completed'
            WHERE jo_no = '$jo'
              AND branch = '$branch'
              AND status != 'Cancelled'
        ");
    } else {
        $con->dbquery("
            UPDATE jo_header
            SET pamount = '$partsTotal',
                labor = '$laborTotal',
                amount = '$transactionTotal',
                remaining_balance = '$remainingBalance'
            WHERE jo_no = '$jo'
              AND branch = '$branch'
        ");
    }

    return array(
        'partsTotal' => number_format($partsTotal, 2, '.', ''),
        'laborTotal' => number_format($laborTotal, 2, '.', ''),
        'grandTotal' => number_format($transactionTotal, 2, '.', ''),
        'totalPaid' => number_format($totalPaid, 2, '.', ''),
        'remainingBalance' => number_format($remainingBalance, 2, '.', '')
    );
}




    switch($_REQUEST['mod']) {
        case "saveHeader":

            if($_POST['jo_no'] != '') {
                $queryString = "UPDATE IGNORE jo_header set 
                            jo_date = '".$con->formatDate($_POST['jo_date'])."', 
                            device_type = '$_POST[device_type]',
                            customer_code = '$_POST[customer_code]',
                            customer_name = '".$con->escapeString(htmlentities($_POST['customer_name']))."',
                            customer_address = '".$con->escapeString(htmlentities($_POST['customer_address']))."', 
                            customer_contactno = '$_POST[customer_contactno]', 
                            customer_email = '$_POST[customer_email]', 
                            unit_name = '$_POST[unit_name]', 
                            unit_pdate = '$_POST[unit_pdate]', 
                            unit_lcd = '$_POST[unit_lcd]', 
                            unit_battery = '$_POST[unit_battery]', 
                            unit_model = '$_POST[unit_model]', 
                            unit_warranty = '$_POST[unit_warranty]', 
                            unit_storage = '$_POST[unit_storage]', 
                            unit_charger = '$_POST[unit_charger]', 
                            unit_category = '$_POST[unit_category]', 
                            unit_processor = '$_POST[unit_processor]', 
                            unit_ram = '$_POST[unit_ram]', 
                            unit_motherboard = '$_POST[unit_motherboard]', 
                            visual_lcd = '$_POST[visual_lcd]', 
                            visual_usb = '$_POST[visual_usb]', 
                            visual_bitlocker = '$_POST[visual_bitlocker]', 
                            visual_charger = '$_POST[visual_charger]', 
                            visual_storage = '$_POST[visual_storage]', 
                            visual_screws = '$_POST[visual_screws]', 
                            visual_camera = '$_POST[visual_camera]', 
                            visual_touchpad = '$_POST[visual_touchpad]', 
                            visual_charging = '$_POST[visual_charging]', 
                            visual_speaker = '$_POST[visual_speaker]', 
                            visual_hdmi = '$_POST[visual_hdmi]', 
                            visual_keyboard = '$_POST[visual_keyboard]', 
                            visual_bios = '$_POST[visual_bios]', 
                            visual_battery = '$_POST[visual_battery]', 
                            visual_ram = '$_POST[visual_ram]', 
                            visual_wifi = '$_POST[visual_wifi]', 
                            remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', 
                            action_taken = '".$con->escapeString(htmlentities($_POST['action_taken']))."', 
                            updated_by = '$_SESSION[userid]' , 
                            updated_on = now() 
                            where jo_no = '$_POST[jo_no]';";
                $jono = $_POST['jo_no'];
            } else {
                list($jono) = $con->getArray("select ifnull(max(jo_no),0)+1 from jo_header where branch = '$bid';"); 
                $queryString = "INSERT IGNORE INTO jo_header (
                            jo_no, branch, jo_date, device_type, customer_code, customer_name,
                            customer_address, customer_contactno, customer_email,
                            unit_name, unit_pdate, unit_lcd, unit_battery, unit_model, unit_warranty,
                            unit_storage, unit_charger, unit_category, unit_processor, unit_ram, unit_motherboard,
                            visual_lcd, visual_usb, visual_bitlocker, visual_charger, visual_storage, visual_screws,
                            visual_camera, visual_touchpad, visual_charging, visual_speaker, visual_hdmi, visual_keyboard,
                            visual_bios, visual_battery, visual_ram, visual_wifi, `status`, remarks, action_taken,
                            trace_no,
                            created_by,
                            created_on) VALUES (
                            '$jono', '$bid', '".$con->formatDate($_POST['jo_date'])."', '$_POST[device_type]', '$_POST[customer_code]','".$con->escapeString(htmlentities($_POST['customer_name']))."',
                            '".$con->escapeString(htmlentities($_POST['customer_address']))."','$_POST[customer_contactno]', '".$con->escapeString(htmlentities($_POST['customer_email']))."', 
                            '$_POST[unit_name]', '".$con->formatDate($_POST['unit_pdate'])."', '$_POST[unit_lcd]','$_POST[unit_battery]', '$_POST[unit_model]', '$_POST[unit_warranty]',
                            '$_POST[unit_storage]', '$_POST[unit_charger]','$_POST[unit_category]','$_POST[unit_processor]','$_POST[unit_ram]','$_POST[unit_motherboard]',
                            '$_POST[visual_lcd]', '$_POST[visual_usb]','$_POST[visual_bitlocker]','$_POST[visual_charger]','$_POST[visual_storage]','$_POST[visual_screws]',
                            '$_POST[visual_camera]', '$_POST[visual_touchpad]','$_POST[visual_charging]','$_POST[visual_speaker]','$_POST[visual_hdmi]','$_POST[visual_keyboard]',
                            '$_POST[visual_bios]', '$_POST[visual_battery]','$_POST[visual_ram]','$_POST[visual_wifi]','Active','".$con->escapeString(htmlentities($_POST['remarks']))."', '".$con->escapeString(htmlentities($_POST['action_taken']))."', 
                            '$_POST[trace_no]', 
                            '$_SESSION[userid]', 
                            now());";
            }
            $con->dbquery($queryString);
            echo str_pad($jono,6,'0',STR_PAD_LEFT);
        break;


                
        case "savePayment":

            $jo_no = isset($_POST['jo_no'])
                ? $con->escapeString($_POST['jo_no'])
                : '';

            $branch = (int)$bid;

            $amount = isset($_POST['amount'])
                ? (float)$con->formatDigit($_POST['amount'])
                : 0;

            $payment_type = isset($_POST['payment_type'])
                ? $_POST['payment_type']
                : 'Partial Payment';

            $payment_method = isset($_POST['payment_method'])
                ? $_POST['payment_method']
                : 'Cash';

            $reference_no = isset($_POST['reference_no'])
                ? $con->escapeString($_POST['reference_no'])
                : '';

            $remarks = isset($_POST['remarks'])
                ? $con->escapeString($_POST['remarks'])
                : '';

            $validTypes = array(
                'Downpayment',
                'Partial Payment',
                'Full Payment'
            );

            $validMethods = array(
                'Cash',
                'GCash',
                'Bank Transfer',
                'Debit/Credit Card',
                'Other'
            );

            if ($jo_no == '' || $amount <= 0) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Enter a valid payment amount.'
                ));
                break;
            }

            if (!in_array($payment_type, $validTypes)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid payment type.'
                ));
                break;
            }

            if (!in_array($payment_method, $validMethods)) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid payment method.'
                ));
                break;
            }

            // Get the JO total, trace number, and current status.
            $header = $con->getArray("
                SELECT amount, trace_no, status
                FROM jo_header
                WHERE jo_no = '$jo_no'
                AND branch = '$branch'
            ");

            if (!is_array($header) || !isset($header[0])) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Job Order not found.'
                ));
                break;
            }

            if ($header[2] == 'Cancelled') {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Cannot receive payment for a cancelled JO.'
                ));
                break;
            }

            $transactionTotal = (float)$header[0];

            // Read the current balance from the payment history.
            $paid = $con->getArray("
                SELECT IFNULL(SUM(amount), 0)
                FROM jo_payments
                WHERE jo_no = '$jo_no'
                AND branch = '$branch'
            ");

            $totalPaid = (float)$paid[0];
            $balance = round($transactionTotal - $totalPaid, 2);

            if ($balance <= 0) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'This JO has already been fully paid.'
                ));
                break;
            }

            if ($amount > $balance) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Payment exceeds the remaining balance.',
                    'remainingBalance' => number_format($balance, 2, '.', '')
                ));
                break;
            }

            $trace_no = $con->escapeString($header[1]);
            $received_by = isset($_SESSION['userid'])
                ? (int)$_SESSION['userid']
                : 0;

            $date_created = date('Y-m-d H:i:s');

            // Save the payment record.
            $con->dbquery("
                INSERT INTO jo_payments (
                    jo_no,
                    branch,
                    trace_no,
                    amount,
                    payment_type,
                    payment_method,
                    reference_no,
                    remarks,
                    received_by,
                    date_created
                ) VALUES (
                    '$jo_no',
                    '$branch',
                    '$trace_no',
                    '$amount',
                    '$payment_type',
                    '$payment_method',
                    '$reference_no',
                    '$remarks',
                    '$received_by',
                    '$date_created'
                )
            ");

            // Recalculate totals and automatically complete when fully paid.
            $totals = updateAmount($jo_no);

            echo json_encode(array(
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'totals' => $totals
            ));

        break;


        
        case "getPaymentSummary":

            $jo_no = isset($_POST['jo_no'])
                ? $con->escapeString($_POST['jo_no'])
                : '';

            if ($jo_no == '') {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Missing Job Order number.'
                ));
                break;
            }

            $branch = (int)$bid;

            // Confirm the JO belongs to this branch.
            $header = $con->getArray("
                SELECT amount, trace_no, status
                FROM jo_header
                WHERE jo_no = '$jo_no'
                AND branch = '$branch'
            ");

            if (!is_array($header) || !isset($header[0])) {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Job Order not found.'
                ));
                break;
            }

            $total = (float)$header[0];
            $trace_no = $con->escapeString($header[1]);
            $status = $header[2];

            $paid = $con->getArray("
                SELECT IFNULL(SUM(amount), 0)
                FROM jo_payments
                WHERE jo_no = '$jo_no'
                AND branch = '$branch'
            ");

            $totalPaid = (float)$paid[0];
            $balance = round($total - $totalPaid, 2);

            if ($balance < 0) {
                $balance = 0;
            }

            echo json_encode(array(
                'success' => true,
                'grandTotal' => number_format($total, 2, '.', ''),
                'totalPaid' => number_format($totalPaid, 2, '.', ''),
                'remainingBalance' => number_format($balance, 2, '.', ''),
                'status' => $status
            ));

        break;


        case "addItem":

            $jo_no = $_POST['jo_no'];
            $qty = $con->formatDigit($_POST['qty']);
            $amt = $con->formatDigit($_POST['amount']);
            $amountdue = $con->formatDigit($_POST['amountdue']);

            $description = $con->escapeString(
                htmlentities($_POST['description'])
            );

            $item = $con->escapeString($_POST['item']);
            $brand = $con->escapeString($_POST['brand']);
            $trace_no = $con->escapeString($_POST['trace_no']);

            $con->dbquery("
                INSERT INTO jo_details
                (jo_no, branch, `code`, brand, `description`,
                qty, amount, amount_due, trace_no)
                VALUES (
                    '$jo_no', '$bid', '$item', '$brand',
                    '$description', '$qty', '$amt',
                    '$amountdue', '$trace_no'
                )
            ");

            $labor = isset($_POST['laborTotal']) ? $_POST['laborTotal'] : null;
            $totals = updateAmount($jo_no, $labor);
            echo json_encode($totals);
        break;

        
        case "updateLabor":

            $jo_no = isset($_POST['jo_no']) ? $_POST['jo_no'] : '';
            $labor = isset($_POST['laborTotal']) ? $_POST['laborTotal'] : '0';

            if ($jo_no != '') {
                $totals = updateAmount($jo_no, $labor);
                echo json_encode($totals);
            } else {
                echo json_encode(array(
                    'error' => 'Missing JO number'
                ));
            }

        break;



        case "deleteLine":

            $lid = isset($_POST['lid']) ? intval($_POST['lid']) : 0;
            $jo_no = isset($_POST['jo_no']) ? $_POST['jo_no'] : '';
            $jo = $con->escapeString($jo_no);

            if ($lid > 0 && $jo !== '') {

                $con->dbquery("
                    DELETE FROM jo_details
                    WHERE line_id = '$lid'
                    AND jo_no = '$jo'
                ");

                $labor = isset($_POST['laborTotal']) ? $_POST['laborTotal'] : null;
                $totals = updateAmount($jo_no, $labor);
                echo json_encode($totals);


            } else {
                echo json_encode(array(
                    'error' => 'Invalid Job Order or item selected.'
                ));
            }

        break;



        case "check4print":
			list($a) = $con->getArray("select count(*) from jo_header where jo_no = '$_POST[jo_no]' and branch = '$bid';");
			
            if($a > 0) { echo "noerror"; }
		break;

        case "receivedJO":
            $con->dbquery("update ignore jo_header set `status` = 'Received', updated_by = '$_SESSION[userid]', updated_on = now() where jo_no = '$_POST[jo_no]' and branch = '$bid';");
            updateAmount($_POST['jo_no']);
        break;

        case "reopen":
            $con->dbquery("update jo_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where jo_no = '$_POST[jo_no]' and branch = '$bid';");
        break;
        
        case "cancel":
            $con->dbquery("update ignore jo_header set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where jo_no = '$_POST[jo_no]' and branch = '$bid';");
        break; 

        case "retrieve":
            $data = array();
			$srrd = $con->dbquery("SELECT line_id AS id, `code`, brand, description, qty, amount, amount_due FROM jo_details WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
        break;

        
        case "retrieveJOCommentStatuses":

            $data = array();

            $res = $con->dbquery("SELECT id, stat FROM options_stat WHERE id IN (3, 4) ORDER BY id;");

            while ($row = $res->fetch_array()) {
                $data[] = array(
                    'id' => $row['id'],
                    'stat' => $row['stat']
                );
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);

        break;


       
        case "retrieveJOComments":

            $jo_no = isset($_POST['jo_no'])
                ? $con->escapeString($_POST['jo_no'])
                : '';

            if ($jo_no == '') {
                echo json_encode(array(
                    'error' => 'Missing Job Order number.'
                ));
                break;
            }

            // Confirm that this JO belongs to the current branch
            // and retrieve its trace number.
            $check = $con->dbquery("
                SELECT jo_no, trace_no
                FROM jo_header
                WHERE jo_no = '$jo_no'
                AND branch = '$bid'
            ");

            if ($check->num_rows == 0) {
                echo json_encode(array(
                    'error' => 'Job Order not found.'
                ));
                break;
            }

            $joData = $check->fetch_assoc();
            $trace_no = $con->escapeString($joData['trace_no']);

            $data = array();

            $res = $con->dbquery("
                SELECT
                    c.id,
                    c.branch,
                    c.jo_no,
                    c.trace_no,
                    c.comment,
                    c.stat,
                    s.stat AS status_name,
                    c.userid,
                    u.fullname,
                    DATE_FORMAT(
                        c.date_created,
                        '%W, %M %e, %Y %h:%i %p'
                    ) AS date_created
                FROM jo_comments c
                LEFT JOIN options_stat s
                    ON s.id = c.stat
                LEFT JOIN user_info u
                    ON u.emp_id = c.userid
                WHERE c.jo_no = '$jo_no'
                AND c.branch = '$bid'
                AND c.trace_no = '$trace_no'
                ORDER BY c.date_created ASC, c.id ASC
            ");

            while ($row = $res->fetch_array()) {
                $data[] = array(
                    'id' => $row['id'],
                    'branch' => $row['branch'],
                    'jo_no' => $row['jo_no'],
                    'trace_no' => $row['trace_no'],
                    'comment' => $row['comment'],
                    'stat' => $row['stat'],
                    'status_name' => $row['status_name'],
                    'userid' => $row['userid'],
                    'fullname' => $row['fullname'],
                    'date_created' => $row['date_created']
                );
            }

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(array(
                'comments' => $data
            ));

        break;



        case "saveJOComment":

            $jo_no = isset($_POST['jo_no'])
                ? $con->escapeString($_POST['jo_no'])
                : '';

            $comment = isset($_POST['comment'])
                ? trim($_POST['comment'])
                : '';

            $comment = $con->escapeString($comment);

            $stat = isset($_POST['stat'])
                ? intval($_POST['stat'])
                : 0;

            $userid = isset($_SESSION['userid'])
                ? intval($_SESSION['userid'])
                : 0;

            if ($jo_no == '' || trim($comment) == '' || $stat < 1 || $stat > 6) {
                echo json_encode(array(
                    'error' => 'Please enter a comment and select a valid status.'
                ));
                break;
            }

            if ($userid <= 0) {
                echo json_encode(array(
                    'error' => 'Your session has expired. Please log in again.'
                ));
                break;
            }

            // Confirm that this JO belongs to the current branch.
            $check = $con->dbquery("SELECT jo_no, trace_no FROM jo_header WHERE jo_no = '$jo_no' AND branch = '$bid';");

            if ($check->num_rows == 0) {
                echo json_encode(array(
                    'error' => 'Job Order not found.'
                ));
                break;
            }

            $joData = $check->fetch_assoc();

            $trace_no = $con->escapeString($joData['trace_no']);


            // Save the comment history.
            $con->dbquery("INSERT INTO jo_comments (branch, jo_no, trace_no, comment, stat, userid, date_created) VALUES ('$bid', '$jo_no', '$trace_no', '$comment', '$stat', '$userid', NOW());");

            // Update the current JO status and audit fields.
            $status = $con->getArray("SELECT stat FROM options_stat WHERE id = '$stat';");

            if (isset($status[0]) && $status[0] != '') {

                $statusName = $con->escapeString($status[0]);

                $con->dbquery("UPDATE jo_header SET status = '$statusName',updated_by = '$userid',updated_on = NOW() WHERE jo_no = '$jo_no' AND branch = '$bid';");
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => true,
                'message' => 'Comment saved successfully.'
            ));

        break;


    }



?>