<?

include("GestionChatService.php");
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

$service = new GestionChatService();

switch($act){
  case 1 : $service->getMessages($_POST['lastId'],$_POST['nbMessages']);break;
  case 2 : $service->getUsers();break;
  case 3 : $service->sendMessage(utf8_encode($_POST["message"]),$_SESSION['user_chat'],$_SESSION['id_chat']);break;
  case 4 : $service->logUserOnChat($_POST['login']);break;
  case 5 : $service->logoutChat();break;
  case 6 : $service->changeNickname($_POST['login']);break;
  case 7 : $service->getUserInfo($_POST['login']);break;
}

?>
