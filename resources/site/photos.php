<?
include_once("decorator.php");

require_once("Mobile_Detect.php");
require_once("SecuriteDao.php");


$detect = new Mobile_Detect();
if($detect->isMobile()){
   $params = "";
   foreach($_GET as $key => $value){
      $params.= ($params == "" ? "?":"&") .  $key . "=" . $value;
   }
    header('Location: photos_mobile.php' . $params);
}
writeHeader("Photos","photo");
$securiteDao = new SecuriteDao();
$connected = $securiteDao->isConnected();
?>

<script type="text/javascript" src="js/timeline.js"></script>
<script type="text/javascript" src="js/photos.js"></script>
<script type="text/javascript" src="js/jquery.lightbox-0.5.js"></script>
<script type="text/javascript" src="js/tracking.js"></script>

<link href="css/timeline.css" rel="stylesheet"/>
<link href="css/photos.css" rel="stylesheet"/>

<div id="body" class="body">
	<div id="idOptions" style="padding:5px;">
		<div style="float:right">
			<button onclick="loadTimelineJournee()">Journée</button>
			<button onclick="loadTimelinePentecote()">Pentecôte</button>
			<button onclick="loadTimelineAutres()">Divers</button>
		</div>
		<div style="float:left">
			<span style="font-size:13px;font-weight:bold"><span id="idChemin"/></span></span>
		</div>
	</div>
	<div id="timeline" style="height:200px;"></div>
	<div id="divPhotos" class="gallery"></div>
   <div id="divPhotosFlickr" class="galleryFlickr" style="display:none"></div>
	<div style="clear:both"></div>
</div>

<script language="Javascript">

   initTimelinePhotos();
</script>

<?
/* On charge le type (journee:0,pentecote:1,autres:2) */
echo "<script language='Javascript'>";
if(isset($_GET["type"])){
	$idPhotos = (isset($_GET["idPhotos"]))?$_GET["idPhotos"]:"null";
	if($_GET["type"] == 0){
		echo "loadTimelineJournee(" . $idPhotos . ")";
	}
	if($_GET["type"] == 1){
		echo "loadTimelinePentecote(" . $idPhotos . ")";
	}
	if($_GET["type"] == 2){
		echo "loadTimelineAutres(" . $idPhotos . ")";
	}
   if($_GET["type"] == 10){
		echo "loadTimeline(50,'Mariage'," . $idPhotos . ")";
	}
}
else{
	echo "loadTimelineJournee();";
}
echo "</script>";
?>

<? writeFooter(); ?>
