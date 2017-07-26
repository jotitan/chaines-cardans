<?

require_once("Model.php");
require_once("decorator.php");
require_once("MediaDao.php");

writeHeaderSecurite("Demandes","compte",true); ?>

<script type="text/javascript" src="js/photos.js"></script>
<script type="text/javascript" src="js/jquery.lightbox-0.5.js"></script>

<link href="css/photos.css" rel="stylesheet">

<h2>Gestion des demandes</h2>



<style>
.divGallery {
    background-color: rgba(0, 0, 0, 0.6);
    background-position: center center;
    background-repeat: no-repeat;
    background-size:22px 22px;
    top:0;
    position:absolute;
}

.error {
   background-image: url("http://cdn1.iconfinder.com/data/icons/realistiK-new/22x22/apps/error.png");
}

.wait {
   background-image: url("http://cdn1.iconfinder.com/data/icons/fatcow/32/hourglass.png");
}

.download {
   background-image:url("http://cdn1.iconfinder.com/data/icons/Once_by_Delacro/Download.png");
}

.bad {
   background-image:url("http://cdn1.iconfinder.com/data/icons/gnomeicontheme/22x22/status/gtk-dialog-error.png");
}

.divGallery:hover {
   background-color: rgba(0, 0, 0, 0.0);
}

.divGallery.download {
   background-color: rgba(0, 0, 0, 0.0);
}

.divGallery :checkbox {
   position:absolute;
   right:20px;
}

.gallery span {
	width:auto;
   margin-bottom:25px;
}

.gallery span > input[type="checkbox"] {
	position:absolute;
	right:20px;
}

.gallery div.select_photo {
	position:absolute;
	text-align:left;
	height:20px;
	margin-top:0px;
}
</style>

<script language="Javascript">

function addImage(id,pathHD,pathLD,type){
   var message = "";
   var divClass = "divGallery ";
   switch(type){
      case -2 : message="Erreur upload";divClass+="error";break;
      case -1 : message="Photo n&rsquo;existe pas";divClass+="bad";break;
      case 2 : message="Télécharger";divClass+="download";break;
      default : message="En attente";divClass+="wait";break;
      
   }
   $('#divPhotos').append("<span style='position:relative'><img src='" + pathLD + "'><div style='height:100px;' class='" + divClass + "' title='" + message + "'></div><label for=\"chk_" + id + "\"><div class=\"select_photo\"><input value=\"" + id + "\" type=\"checkbox\" id=\"chk_" + id + "\" name=\"choix_demande\"/>Choix</div></label></span>");
	$('#divPhotos > span:last > div').width($('#divPhotos > span:last > img').width());
	if(type == 2){
		$('#divPhotos > span:last > div.divGallery').wrap('<a href="' + pathHD + '" target="_blank"></a>');
	}
}


/* Supprime les photos */
function deleteDemandes(){
	var values = new Array();
	$(':checkbox[name="choix_demande"]:checked').each(function(){
		values.push($(this).val());
	});
	console.log(values);
	
	$.ajax({
		type:'POST',
		url:'MediaAction.php?action=10',
		data:JSON.stringify({ids:values}),
		success:function(data){
		
		}
	});
}


$(function(){
	$('#selectOptions > option[data-fct]').click(function(){
		window[$(this).data('fct')].apply();
	});

<?
	$photoPath = "../v2.0/photos/";
	$dao = new MediaDao();
	$demandes = $dao->getDemandesOfUser(1);
	foreach($demandes as $d){
		echo "addImage(" . $d->id . ",\"" . $d->pathHD . "\",\"" . str_replace("/hd/","/ld/",$photoPath.$d->path) . "\"," . $d->status . ");\n";
	}
?>

});

</script>

<select id="selectOptions"><option style="font-style:italic">...Choisir...</option><option>Télécharger en .zip</option>
	<option data-fct="deleteDemandes">Supprimer</option></select>
<div id="divPhotos" class="gallery" style="clear:both">

</div>

<div style="clear:both"></div>


<? writeFooter(); ?>
