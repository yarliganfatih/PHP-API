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
	//CREATE USER
    if(isset($sentdata)){
		$q_username = addslashes($sentdata->username);
		$q_email = addslashes($sentdata->email);
		$q_password = addslashes($sentdata->password);
		$q_name = addslashes($sentdata->name);
		$q_family_name = addslashes($sentdata->family_name);
	}else if($_POST){
		$q_username = addslashes($_POST["username"]);
		$q_email = addslashes($_POST["email"]);
		$q_password = addslashes($_POST["password"]);
		$q_name = addslashes($_POST["name"]);
		$q_family_name = addslashes($_POST["family_name"]);
	}else{
		$_code = 404;
		$jsonArray["Error"] = TRUE;
		$jsonArray["ErrorMsg"] = "Lütfen JSON veya POST verisi giriniz. (id, table)"; 
	}
	
	if($_code!=404){
		$q_ip = $_SERVER["REMOTE_ADDR"];
		$q_date = time();
		
		$isThereUser = $db->query("SELECT * FROM users WHERE username='$q_username' OR email='$q_email'")->num_rows;
		
		if(empty($q_username) || empty($q_email) || empty($q_password) || empty($q_name) || empty($q_family_name)) {
			$_code = 400; 
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Boş Alan Bırakmayınız.";
		}else if(!filter_var($q_email,FILTER_VALIDATE_EMAIL)) {
			$_code = 400;
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Geçersiz E-Posta Adresi";
		}else if($q_username != UserNameFilter($q_username)){
			$_code = 400;
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Geçersiz Kullanıcı Adı"; 
		}else if($isThereUser !=0) {
			$_code = 400;
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Kullanıcı Adı veya E-Posta daha önce alınmış."; 
		}else{
			//Auto Increment ID
			$sql = $db->query("INSERT INTO users (date, last_activity, memory, ip, visible, username, email, password, name, family_name) VALUES ( '$q_date', '$q_date', '', '$q_ip', '1', '$q_username', '$q_email', '$q_password', '$q_name', '$q_family_name')");

			if($sql) {
				$_code = 201;
				$jsonArray["Msg"] = "Başarılı.";
			}else {
				$_code = 400;
				$jsonArray["Error"] = TRUE;
				$jsonArray["ErrorMsg"] = "Sistem Hatası : ".mysqli_error($db);
			}
		}
	}
}else if($_SERVER['REQUEST_METHOD'] == "PUT") {
     $sentdata = json_decode(file_get_contents("php://input"));
     if(isset($sentdata->id) && isset($sentdata->table) && isset($sentdata->data) && isset($sentdata->value)){
		$q_data = $sentdata->data;
		$q_value = addslashes($sentdata->value);
		$q_id = $sentdata->id;
		$q_table = $sentdata->table;
	}else if($_POST){
		//NOT POSSIBLE WITH THIS METHOD
		$q_data = $_POST["data"];
		$q_value = addslashes($_POST["value"]);
		$q_id = $_POST["id"];
		$q_table = $_POST["table"];
	}else{
		$_code = 404;
		$jsonArray["Error"] = TRUE;
		$jsonArray["ErrorMsg"] = "Lütfen JSON verisi giriniz. (id, table)"; 
	}
	
	if($_code!=404){
		$q = $db->query("UPDATE $q_table SET $q_data='$q_value' WHERE id=$q_id");
		
		if($q) {
			$_code = 200;
			$jsonArray["Msg"] = "Güncelleme Başarılı";
		}
		else {
			$_code = 400;
			$jsonArray["Error"] = TRUE;
			$jsonArray["ErrorMsg"] = "Sistemsel Bir Hata Oluştu";
		}
	}
} else if($_SERVER['REQUEST_METHOD'] == "DELETE") {
    $sentdata = json_decode(file_get_contents("php://input"));
    
    if(isset($sentdata->id) && isset($sentdata->table)){
		$q_id = intval($sentdata->id);
		$q_table = $sentdata->table;
	}else if($_POST){
		//NOT POSSIBLE WITH THIS METHOD
		$q_id = intval($_POST["id"]);
		$q_table = $_POST["table"];
	}else{
		$_code = 404;
		$jsonArray["Error"] = TRUE;
		$jsonArray["ErrorMsg"] = "Lütfen JSON verisi giriniz. (id, table)"; 
	}
	
	if($_code!=404){
		$isThereUser = $db->query("select * from $q_table where id='$q_id'")->num_rows;
		if($isThereUser) {
			
			$sil = $db->query("delete from $q_table where id='$q_id'");
			if( $sil ) {
				$_code = 200;
				$jsonArray["Msg"] = "Veri Silindi.";
			}else {
				$_code = 400;
				$jsonArray["Error"] = TRUE;
	 			$jsonArray["ErrorMsg"] = "Sistemsel Bir Hata Oluştu";
			}
		}else {
			$_code = 400; 
			$jsonArray["Error"] = TRUE; 
    		$jsonArray["ErrorMsg"] = "Geçersiz id";
		}
	}
} else if($_SERVER['REQUEST_METHOD'] == "GET") {
    $sentdata = json_decode(file_get_contents("php://input"));

    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
		$q_id = intval($_GET["id"]);
		$q_table = $_GET["table"];
		//$q_table = "users";
	}else if(isset($sentdata)){
		//NOT POSSIBLE WITH THIS METHOD
		$q_id = intval($sentdata->id);
		//$q_table = $sentdata->table;
		$q_table = "users";
	}else if($_POST){
		//NOT POSSIBLE WITH THIS METHOD
		$q_id = intval($_POST["id"]);
		//$q_table = $_POST["table"];
		$q_table = "users";
	}else{
		$_code = 404;
		$jsonArray["Error"] = TRUE;
		$jsonArray["ErrorMsg"] = "Lütfen JSON veya GET verisi giriniz. (id, table)"; 
	}
	
	if($_code!=404){
		$isThereUser = $db->query("select * from $q_table where id='$q_id'")->num_rows;
		if($isThereUser) {
			$bilgiler = $db->query("select * from $q_table where id='$q_id'")->fetch_assoc();
			$jsonArray["Data"] = $bilgiler;
			$_code = 200;
			
		}else {
			$_code = 400;
			$jsonArray["Error"] = TRUE;
    		$jsonArray["ErrorMsg"] = "Veri bulunamadı.";
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