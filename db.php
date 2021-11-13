<?php
$host = "localhost";
$user = "yarligan";
$password = "123456";
$database = "yarliganDB";

$db = mysqli_connect($host, $user, $password, $database);
if (!$db) {
  die("Connection failed: " . mysqli_connect_error());
}else{
	
}
?>