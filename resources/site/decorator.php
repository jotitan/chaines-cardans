<?
require_once("SecuriteDao.php");

session_start();


function writeHeader($title,$onglet){
	return writeHeaderSecurite($title,$onglet,false);
}

/* Ecrit le header de la page */
/* @param connected : indique que l'utilisateur est connecte */
/* @param onglet : indique l'onglet actif */
/* @mustConnected : Indique que l'utilisateur doit etre loggue pour acceder a la page */
function writeHeaderSecurite($title,$onglet,$mustConnected){

// On verifie si l'utilisateur est connecte
$securiteDao = new SecuriteDao();
$connected = $securiteDao->isConnected();
if($mustConnected == true && $connected == false){
	header('Location:accueil.php');
}

if($_GET['logout'] == 'true'){
	$securiteDao->deconnect();
	$connected = false;
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?  echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<? writeScripts(); ?>

</head>
<body>

<div style="float:left;min-width:20px;width:5%;background-color:#99DDFF;height:70px;z-index:1"></div>
<div style="float:left;width:90%;background-color:#99DDFF;height:70px;z-index:10;">
	<a href="accueil.php" style="float:left">
      <img src="img/little_logo.png" title="Accueil" style="border:none;margin-right:20px;height:60px"/>
   </a>
	<!--<div style="float:right;width:250px;cursor:pointer;" title="Décompte avant la prochaine sortie" id="idCountDown"></div>-->
   <? if($connected == true){ ?>
   <div style="float:right;z-index:2">
      <input type="text" placeholder="Rechercher des photos..." size="30" id="idFieldSearch"
         style="margin-top:17px;border-radius:10px;font-size:13px;text-align:right;padding-right:8px"/>
      <script language="Javascript">
         $(function(){
            $('#idFieldSearch').keypress(function(e){
               if(e.keyCode == 13){
                  location.href="searchPhotos.php?q=" + $(this).val();
               }
            })
         });
      </script>
   </div>
   <? } ?>
</div>
<div style="float:left;clear:right;min-width:20px;width:5%;background-color:#99DDFF;height:70px;z-index:1"></div>

<div style="float:left;min-width:20px;width:5%;background-color:#99DDFF;height:100%;">&nbsp;</div>
<!-- Bloc principal -->
<div style="float:left;width:90%;min-width:700px;z-index:10;position:relative">
<!-- Barre de menu -->
	<? writeMenu($connected,$onglet); ?>
	<div style="box-shadow: 8px 13px 12px #555;background-color:white;" id="idBody">
	<? 
	if($connected == true){
		writeMenuCompte();
	}
   return $connected;
}

function writeScripts(){
?>
<link rel="stylesheet" type="text/css" href="css/jquery/jquery.lightbox-0.5.css" media="screen" />
<!--<link rel="stylesheet" type="text/css" href="css/jquery/jquery-ui-1.7.2.custom.css" />-->
<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.8.21/themes/base/jquery-ui.css"/>

<script language="Javascript" src="js/jquery/jquery-1.7.2.min.js">;</script>
<!--<script language="Javascript" src="js/jquery/jquery-ui-1.7.2.custom.min.js">;</script>-->
<script language="Javascript" src="http://code.jquery.com/ui/1.8.21/jquery-ui.min.js">;</script>
<!--<script type="text/javascript" src="js/jquery/jquery.lightbox-0.5.js"></script>-->

<!-- Compte a rebours -->
<script language="Javascript" src="js/jquery/jquery.countdown.js">;</script>
<script language="Javascript" src="js/jquery/jquery.countdown-fr.js">;</script>
<link rel="stylesheet" type="text/css" href="css/jquery/jquery.countdown.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />

<!-- Own scripts -->
<script language="javascript" src="js/securite.js" ></script>
<script language="javascript" src="js/md5.min.js" ></script>
<script language="javascript" src="js/animation.js" ></script>
<script language="javascript" src="js/chat.js" ></script>

<link rel="stylesheet" type="text/css" href="css/decorator.css"/>
<?
}

/* Ecrit le menu */
function writeMenu($connected,$onglet){
?>
	<div style="width:100%;height:27px;background-color:#99DDFF" class="menu_title">
		<? if($connected){ ?>
			<div<? echo ($onglet=="compte")?" class=\"active\"":"";?> onclick="showMenuBox()"><img src="img/menu/compte.png" style="width:20px;"/> Compte</div>
		<? }else{ ?>
			<div onclick="showLoginBox()"><img src="img/menu/cle.png" style="width:20px;" alt=""/> Connexion</div>
		<? } ?>
		<div<? echo ($onglet=="sortie")?" class=\"active\"":"";?>>
			<a href="sorties.php"><img src="img/menu/terre.png" style="width:20px;" alt=""/> Sorties</a>
		</div>
		<div<? echo ($onglet=="photo")?" class=\"active\"":"";?>>
			<a href="photos.php"><img src="img/menu/camera.png" style="width:20px;" alt=""/> Photos</a>
		</div>
		<div<? echo ($onglet=="news")?" class=\"active\"":"";?>>
			<a href="news.php"><img src="img/menu/editorial.png" style="width:20px;" alt=""/> News</a>
		</div>
		<div class="title_accueil"><a href="accueil.php">Chaînes & Cardans</a></div>
	</div>
<?
}

/* Ecrit le menu de l'espace perso */
function writeMenuCompte(){
?>
<div style="position:absolute;width:100%;height:1px;z-index:1000">
	<div id="idMenuCompte" class="menu-box" style="display:none;">
		<div><a href="trombino.php">Trombinoscope</a><span class="icon icon_trombi">&nbsp;</span></div>
		<div><a href="manageFrise.php">Historique moto</a><span class="icon icon_histo">&nbsp;</span></div>
      <div><a href="searchPhotos.php">Recherche photos</a><span class="icon icon_search">&nbsp;</span></div>
      <div><a href="tagPhotos.php">Tagger photos</a><span class="icon icon_tag">&nbsp;</span></div>
      <?
         $secu = new SecuriteDao();
         if($secu->isAdmin()){
      ?>
      <div><a href="editNews.php">Gestion des news</a><span class="icon icon_edito">&nbsp;</span></div>
      <? } ?>
      <div><a href="accueil.php?logout=true">Déconnecter</a><span class="icon icon_tag">&nbsp;</span></div>
		<div style="float:right;cursor:pointer;"><span onclick="$('#idMenuCompte').slideUp();" style="width:50px;">Fermer</span></div>
	</div>
</div>
<?
}

/* Ecrit le footer */
function writeFooter(){
?>

<div id="idFooter" class="footer">
	Chaînes & Cardans <? echo date('Y'); ?>
</div>
</div>
</div>
<div style="float:left;clear:right;min-width:20px;width:5%;background-color:#99DDFF;height:100%"></div>
<div style="position:absolute;width:99%;height:100%;z-index:1;overflow:hidden;display:none" id="idDivMotos"></div>
<script language="Javascript">
	$(function(){
		//new AnimeBike('img/anim_moto_right.png','img/anim_moto_left.png',300);
		//$('#idCountDown').countdown({until:new Date(2012,4,26,8,0,0)}); // Pentecote

      // On regarde si l'utilisateur est connecte au chat.
      <? 
	  if($_SESSION['user_chat']!=null){?>
	     GestionChat.login = '<? echo $_SESSION['user_chat']; ?>';
	  <? }
	  else{
	     $securiteDao = new SecuriteDao();
	     if($securiteDao->isConnected()){
	        ?>
	           GestionChat.login = '<? echo $_SESSION['user']->login; ?>';
	        <?
	     }
	  }
	  ?>
	  GestionChat.showButton();
	  <?		  
	  if($_SESSION['user_chat']!=null){echo "GestionChat.show();";}
	   ?>

	});
</script>

</body>
</html>

<?
}



?>
