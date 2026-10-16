<?php

  include '../handlers/initDB.php';
  $con = new myDB;

  function _returnError($code) {
    switch($code) {
      case "1": echo "Unable to log you in. You may have specified an invalid username or password!"; break;
      case "2": echo "You have been logged out as your session has already expired!"; break;
      case "3": echo "Unable to renew Session ID. Please contact system administrator to correct this problem."; break;
      case "4": echo "Unable to retrieve Session Data. Please contact system administrator to correct this problem."; break;
      case "5": echo "You are not authorized to access the selected branch."; break;
      }
  }

?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>RJZ Fix Hub System Ver. 1.0a</title>
    <link rel="icon" type="image/x-icon" href="../logo-small.ico" />
    <link rel="stylesheet" href="css/reset.css"> 
    <link rel="stylesheet" href="css/login.css">
    <script src="js/prefixfree.min.js"></script>
  </head>
  <body <?php if(isset($_GET[exception])) { ?> onLoad = "alert('<?php _returnError($_GET[exception]); ?>');" <?php } ?>>
    <div class="login">
		  <h1 style="padding-top: 10px;padding-bottom: 10px;">RJZ Fix Hub<br/><span style="font-size:11px">Tayud, Liloan Cebu Philippines, 6002</span></h1>
      <form class="form" method="post" action="../authenticate.php">
        <p class="field">
          <input type="text" name="txtname" placeholder="Username" required/>
          <i><img src="images/personalinfo.png" size=18 height=18></i>
        </p>
        <p class="field">
          <input type="password" name="txtpass" placeholder="Password" required/>
          <i><img src="images/locked.png" size=18 height=18></i>
        </p>
        <p class="field" style="margin-top: 5px;">
                <select name="branch" id="branch" class="custom-select myselect">
                   <?php
                      $bQuery = $con->dbquery("SELECT branch_code, branch_name FROM options_branches ORDER BY branch_code;");
                      while($bRow = $bQuery->fetch_array()) {
                        echo "<option value = '$bRow[0]'>$bRow[1]</option>";
                      }
                   ?>
                </select>
                <i><img src="images/home.png" size=18 height=18></i>
            </p>
        <p class="submit"><input type="submit" name="sent" value="Login"></p>
      </form>
      <p style='margin: 0 50px; text-align: justify; font-size: 11px; color: #4a4a4a;'><font style='font-weight: bold; font-size: 11px;'>NOTICE:</font> Use of this network, its equipment, and resources is monitored at all times and requires explicit permission from the network administrator.</p>
    </div>
</body>
</html>
