<?
require_once('Model.php');
session_start();
header('Content-type: application/json;charset=utf-8');
require_once('GestionImage.php');

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
$gestion = new GestionImage();

if($act == 'tmpfile'){
  try{
    $path = $gestion->saveTempPhoto($_FILES["photo"],$_SESSION["user"]->login,$_POST['resizeH'],$_POST['resizeW']);
    echo "{\"chemin\":\"" . $path . "\"}";
  }catch(Exception $e){
    echo "{\"error\":\"" . $e->getMessage() . "\"}";
  }
}
else{

}

?>
