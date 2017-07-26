<?
session_start();
header('Content-type: application/json;charset=utf-8');
require_once('SecuriteService.php');


if(!isset($_POST)){
	if(!isset($_GET)){return;}
	$act = $_GET['action'];
}
else{
	$act = $_POST['type'];
	if(!isset($_POST['type'])){$act = $_POST['action'];}
	if($act==null){
		$act = $_GET['action'];
		$_POST = $_GET;
	}
}

$service = new SecuriteService();
switch($act){
case 1 : $service->login($_POST['login'],$_POST['mdp']);break;
default : return;
}


?>
