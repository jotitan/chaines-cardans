<?

include("GestionTagService.php");
require_once("SecuriteDao.php");
session_start();

header('Content-type: application/json;charset=utf-8');

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

checkSecurity(basename(__FILE__),$act);

$service = new GestionTagService();

switch($act){
  case 1 : break;
  case 2 : $service->getPersonnesByName(utf8_encode($_POST["term"]));break;
  case 3 : $service->getMotosByName(utf8_encode($_POST["term"]));break;
  case 4 : $service->getMotsClesByName(utf8_encode($_POST["term"]));break;
  case 5 : $service->getLieuxByName(utf8_encode($_POST["term"]));break;
  case 6 : $service->createMotCle($_POST["value"]);break;
  case 7 : $service->createLieu($_POST["value"]);break;
  case 8 : $service->getPhotoTag($_POST["nb"],$_POST["idMedia"]);break;
  case 9 : $service->getInfoMedia($_POST["idMedia"]);break;
  case 10 : $service->removePersonneToTag($_POST["idTag"],$_POST['id']);break;
  case 11 : $service->removeMotoToTag($_POST["idTag"],$_POST['id']);break;
  case 12 : $service->removeMotCleToTag($_POST["idTag"],$_POST['id']);break;    
  case 13 : $service->removeLieuToTag($_POST["idTag"],$_POST['id']);break;
  case 14 : $service->addPersonneToTag($_POST["idTag"],$_POST['id']);break;
  case 15 : $service->addMotoToTag($_POST["idTag"],$_POST['id']);break;
  case 16 : $service->addMotCleToTag($_POST["idTag"],$_POST['id']);break;    
  case 17 : $service->addLieuToTag($_POST["idTag"],$_POST['id']);break;    
  case 18 : $service->setTypeTag($_POST["idTag"],$_POST['id']);break;
  case 19 : $service->setCommentaire($_POST["idTag"],$_POST['commentaire']);break;
  case 20 : $service->duplicateTags($_POST["idMedia"],$_POST["nbFrom"],$_POST["idTagTo"]);break;
}

?>
