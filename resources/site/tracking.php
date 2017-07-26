<?

require_once("Model.php");
require_once("ConnectionBDD.php");

// Gestion du tracking : log l'acces a chaque image HD dans la table TRACKING

if(isset($_GET['name'])){
	$c = new ConnectionBDD();
	$c->connect();
	mysql_query("insert into TRACKING (TAG_NAME,NAME_INFO) values('HD_PICTURE','" . $_GET['name'] . "')");
	$c->close();
}


?>
