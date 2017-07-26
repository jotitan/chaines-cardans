<?

require_once("Model.php");
require_once("decorator.php");
require_once("UserDao.php");

writeHeaderSecurite("Recherche photos","compte",true); ?>

<script type="text/javascript" src="js/photos.js"></script>
<script type="text/javascript" src="js/search.js"></script>
<script type="text/javascript" src="js/jquery.lightbox-0.5.js"></script>

<link href="css/photos.css" rel="stylesheet">

<h2>Rechercher des photos</h2>

<form onsubmit="newSearch();return false;" style="float:left;position:relative">
<input type="text" id="request" placeholder="Tapez des mots..." value="<? echo $_GET["q"]; ?>" size="30"/>
<input type="submit" value="Rechercher" id="idSearchButton"/>
<img src="img/loader2.gif" style="width:16px;margin-top:3px;margin-left:-130px;position:absolute;display:none" id="idLoader">
</form>

<div id="idPagination" style="float:left;margin-left:10px">
    <span id="idTotal" style="margin-right:10px;"></span>
    <button id="idPrevious">&lt;</button>
    <span id="pages"></span>
    <button id="idNext">&gt;</button>
</div>


<div id="divPhotos" class="gallery" style="clear:both"></div>

<div style="clear:both"></div>

<script language="Javascript">
$(function(){
   if($('#request').val()!=''){
      $('#idSearchButton').click();
   }
   $('#request').focus();
});
</script>

<? writeFooter(); ?>
