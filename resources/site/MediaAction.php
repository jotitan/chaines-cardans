<?

require_once("Model.php");
session_start();
header('Content-type: application/json;charset=utf-8');
require_once('MediaService.php');
require_once("SecuriteDao.php");
require_once("lib/JSON.php");



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

$service = new MediaService();
$jsonService = new Services_JSON();

switch($act){
case 1 : $service->getMedias($_POST['idGroup']);break;
case 2 : $service->getPhotosOfMedia($_POST["idMedia"]);break;
case 3 : $service->getTagInfos($_POST["idPhotoTag"]);break;
case 4 : $service->getSortie($_POST["nomSortie"]);break;
case 5 : $service->searchPhotos($_POST["request"],$_POST["from"],$_POST["size"]);break;
case 6 : $service->getSortieById($_POST["id"]);break;
case 7 : $service->getDemandesPhotosHD();break;
case 8 : $service->pushDemandePhotoHD($_POST['path'],$_SESSION['user']->id);break;
case 9 : $data = $jsonService->decode(file_get_contents('php://input', 1000000));
   $service->updateStatusDemandes($data,$_POST['idUser']);
   break;
case 10 : $data = $jsonService->decode(file_get_contents('php://input', 1000000));
	var_dump($data);
	//$service->deleteDemandes($data,$_SESSION['user']->id);
	break;
default : return;
}


?>
