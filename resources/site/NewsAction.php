<?

include("NewsService.php");
require_once("SecuriteDao.php");
session_start();


header('Content-type: application/json;charset=utf-8');

$secu = new SecuriteDao();
if($secu->isAdmin() == false){
	echo "{\"erreur\":\"Acces interdit\"}";
	return;
}

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

$service = new NewsService();

switch($act){
  case 1 : $service->getNewsById($_POST["id"]);break;
  case 2 : $service->saveNews($_POST["id"],$_POST["titre"],$_POST["contenu"],$_POST["urlImage"]);break;
  case 3 : $service->deleteNews($_POST["id"]);break;
}

?>
