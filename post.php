<?php
//Display Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "db.php";
include "function.php";

$jsonArray = array();
$jsonArray["Error"] = FALSE;

$_code = 200;

$q = $db->query("SET NAMES UTF8");

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $sentdata = json_decode(file_get_contents("php://input"));
    if(isset($sentdata)){
		$q_table = addslashes($sentdata->table);
		$q_keys = addslashes($sentdata->keys);
		$q_values = addslashes($sentdata->values);
	}else if($_POST){
		$q_table = addslashes($_POST["table"]);
		$q_keys = addslashes($_POST["keys"]);
		$q_values = addslashes($_POST["values"]);
	}else{
		$_code = 404;
		$jsonArray["Error"] = TRUE;
		$jsonArray["ErrorMsg"] = "There is no data! required JSON or POST data."; 
	}
	
	if($_code!=404){
		
		$q_values2 = str_replace(",", "', '", $q_values);
		$sql = $db->query("INSERT INTO $q_table ($q_keys) VALUES ( '$q_values2' )");
		if($sql) {
			$_code = 201;
			$jsonArray["Msg"] = "Başarılı.";
		}else {
			$_code = 400;
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Sistem Hatası : ".mysqli_error($db);
		}
	}
}else {
	$_code = 406;
	$jsonArray["Error"] = TRUE;
 	$jsonArray["ErrorMsg"] = "Geçersiz method!";
}


SetHeader($_code);
$jsonArray[$_code] = HttpStatus($_code);
echo json_encode($jsonArray);
?>