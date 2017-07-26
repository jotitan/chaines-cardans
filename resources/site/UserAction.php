<?
/* Methode de gestion / consultation des utilisateurs */
require_once('Model.php');
session_start();
header('Content-type: application/json;charset=utf-8');

require_once('UserService.php');

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

$service = new UserService();
switch($act){
case 1 : $service->getUser($_POST['idUser']);break;
case 2 : $service->getFriseInfos($_SESSION['user']->id,$_POST['idUser']);break;
case 3 : $service->updateFieldFriseMoto($_SESSION['user']->id,$_POST['id'],$_POST['field'],$_POST['value'],$_SESSION['user']->login);break;
case 4 : $service->searchMotoByName($_POST['term']);break;
case 5 : $service->createPeriode($_SESSION['user']->id);break;
case 6 : $service->removePeriode($_SESSION['user']->id,$_POST['id']);break;
default : return;
}


?>
