
<?php
session_start();
$host = "core4.cranecali-ms.com";
$user = "core4_admin";
$password = "core4";
$db = "core4_calicms";
try {

  $con = new PDO("mysql:dbname=$db;port=3306;host=$host", 
  	$user, $password);
  // set the PDO error mode to exception
  $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  //echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: ".
   $e->getMessage();
  echo $e->getTraceAsString();
  exit;
}

//24 minutes default idle time
// if(isset($_SESSION['ABC'])) {
// 	unset($_SESSION['ABC']);
// }

?>