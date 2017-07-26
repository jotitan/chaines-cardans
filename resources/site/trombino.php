<? require_once("decorator.php"); ?>
<? require_once("SortieDao.php"); ?>
<? require_once("UserDao.php"); ?>
<? writeHeaderSecurite("Trombinoscope","compte",true); ?>



<script language="Javascript" src="js/table_sorter.js"></script>
<script language="Javascript" src="js/jquery/jquery.mousewheel.js"></script>
<script type="text/javascript" src="js/jquery/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/skin.css" />
<link rel="stylesheet" type="text/css" href="css/trombino.css" />

<div id="idUsers" style="margin-top: -10px;">

<div style="float:right;margin-right:10px;">
	<span class="label">Filtrer</span> : <input type="text" id="idFiltre"/>
	<span style="display:inline-block;cursor:pointer;margin-left:5px;" title="Effacer" class="ui-icon ui-icon-circle-close" onclick="$('#idFiltre').val('').trigger('keyup');"></span>
</div>
<div style="clear:both"></div>

<? 
$dao = new UserDao();
$users = $dao->getUsers();
foreach($users as $i => $u){
?>
<div class="thumb-trombi" data-info="<? echo strtolower($u->getInfo("prenom")) . " " . strtolower($u->getInfo("nom")); ?>">
	<div style="float:left;margin-right:5px;">
		<img style="height:100px;padding-left:0px;border-radius:10px;cursor:pointer" 
			src="../v2.0/<? echo $u->getInfo("path"); ?>___.jpg"
			onclick="loadUser(<? echo $u->getInfo("id"); ?>)"/>
	</div>
	<div style="padding-top:30px;">
		<span style="cursor:pointer;text-decoration:underline" onclick="loadUser(<? echo $u->getInfo("id"); ?>)"><? echo $u->getInfo("prenom") . "<br/>" . $u->getInfo("nom"); ?></span>
	</div>
</div>
<?}
?>

<div style="clear:both"></div>

</div>

<div id="idDetailUser" style="display:none">
<div style="float:right;margin-right:10px;">
	<button onclick="$('#idUsers').show();$('#idDetailUser').hide();">Fermer</button>
</div>

<div>
	<div style="float:left;width:48%" id="infos">
		<div style="float:left">
			<img src="" style="padding-left:0px;margin:10px;height:150px;border-radius:10px;border:solid 2px #007DA1" name="photo"/>
		</div>
		<div style="float:left;padding-top:20px;padding-left:10px;font-size:12px;">
			<p style="font-weight:bold;font-size:14px;"><span name="prenom"></span> <span name="nom"></span></p>
			<p><span name="email"></span></p>
			<p>Né le <span name="anniversaire"></span></p>
			<p><span id="idNbSorties"></span> sorties dont <span id="idNbPentecote"></span> we pentecôte</p>
			<p><span id="idNbPhotos"></span> photo(s)</p>
         <p>Voir ses <a id="idLinkPhotos">photos</a></p>
		</div>
	</div>


	<div id="idSorties" style="float:left;width:48%;border-left:dotted 1px black;padding-left:20px;padding-bottom:5px;">

	</div>

	<div style="clear:both"></div>
</div>

<fieldset style="padding-top:10px;padding-bottom:10px;border-top:dotted 1px black;border-bottom:dotted 1px black;border-left:none;border-right:none">
	<legend style="font-size:12px;font-weight:bold">Historique moto</legend>
	<div style="float:left;width:80%;max-width:835px">
		<img src="" id="idTimeline" style="width:100%" usemap="#mapFrise"/>
		<div style="width:100%;text-align:right;">
			<span style="margin-right:5px;font-size:11px;" id="idCommentaireMoto"></span>
			<span style="margin-right:10px;font-size:12px;font-weight:bold" id="idTitreMoto"></span>
		</div>
	</div>
	<map name="mapFrise" id="idMapFrise"></map>
	<div style="float:left;width:18%;height:135px;margin-left:5px;">
		<img src="" style="height:135px;max-width:170px;display:none;border-radius:10px;border:solid 2px #007DA1;padding-left:0px;" id="idPreviewPhoto"/>
	</div>
	<div style="clear:both"></div>
</fieldset>

<div id="idCaroussel" style="width:80%;margin:auto;margin-top:10px;">
	<ul class="jcarousel-skin-tango"></ul>
</div>

<script language="Javascript">

var userToLoad = '<? echo $_GET['idUser']; ?>';
var nbTryLoadTimeline = 0;// Permet de limiter le nombre d'essai


$('#idFiltre').bind('keyup',filterUsers);

function filterUsers(){
	var value = $('#idFiltre').val().toLowerCase();
	if(value == ''){
		$('div.thumb-trombi','#idUsers').show();
	}
	else{
		$('div.thumb-trombi[data-info*="' + value + '"]','#idUsers').fadeIn(200);
		$('div.thumb-trombi:not([data-info*="' + value + '"])','#idUsers').fadeOut(200);
	}
}



function loadUser(idUser){
	$('#idUsers').hide();
	$('#idDetailUser').show();

	$.ajax({url:'UserAction.php',data:{action:1,idUser:idUser},dataType:'json',success:function(data){
		$('#idSorties').empty().append('<table class="table"><tr><th>Titre</th><th style="width:25px;"></th><th style="width:25px;"></th></tr></table>');
		var nbPentecote = 0;
		$(data.sorties).each(function(){
			nbPentecote+=parseInt(this.type);
			var idPhotos = (this.type == 0)?this.media:this.group;
			$('#idSorties > table').append('<tr><td>' + this.title + '</td>'
				+ '<td><a href="sorties.php?idSortie=' + this.id + '"><img src="img/sortie.gif"/></a></td>'
				+ '<td><a href="photos.php?type=' + this.type + '&idPhotos=' + idPhotos + '"><img src="img/img_icone.gif"/></a></td></tr>');
		});
		if(data.photos == undefined || data.photos.photos.length == 0){
			$('#idCaroussel').hide();
			$('#idNbPhotos').text(0);
		}
		else{
			$('#idNbPhotos').text(data.photos.photos.length);
			var root = data .photos.root;
			$('#idCaroussel').show();
			$(data.photos.photos).each(function(){
				var url = root.value + "/" + root.ldDir + "/" + this.name + ".jpg";
				$('#idCaroussel>ul').append('<li><img src="' + url + '"</li>');
			});

			/* On active l'autoroll s'il y a assez d'elements */
			var width = 0;
			$('#idCaroussel > ul > li > img').each(function(i){width+=$(this).width();});
			$('#idCaroussel>ul').jcarousel({wrap: 'circular',auto:(width + 100 < $('#idCaroussel').width())?0:4,scroll:5,easing:'easeInOutCirc',animation:'slow'});

		}
	
		$('span[name]','#infos').each(function(i){
			$(this).text(data[$(this).attr('name')]);
		});
      $('#idLinkPhotos').attr('href','searchPhotos.php?q=' + data.nom + " " + data.prenom);
		$('#idNbSorties').text(data.sorties.length);
		$('#idNbPentecote').text(nbPentecote);

		$('img[name]','#infos').each(function(i){
			$(this).attr('src',data[$(this).attr('name')]);
		});
		$('#idSorties > table').sorter({scroll:true,height:150,width:$('#idSorties').width()*0.8,classHeader:'table'});
		// Chargement de la frise
		$('#idTimeline').attr('src','friseUser2.php?idUser=' + idUser);

		// On charge les infos de la timeline
		nbTryLoadTimeline = 0;	
		loadTimelineData(idUser);
	
	}});
}


function loadTimelineData(idUser){
	$.ajax({url:'UserAction.php',data:{action:2,idUser:idUser},dataType:'json',success:function(data){
		if(data.message!=null){
			if(nbTryLoadTimeline++ >=3){return;}
			setTimeout(function(){loadTimelineData(idUser);},1000);
			return;
		}
		$('#idMapFrise').empty();
		$('#idPreviewPhoto').hide();
		// Calcul du rapport de l'image
		var ratio = ($('#idTimeline').width() / 835);
		$(data.areas).each(function(){
			var info = this;
			// Si l'image est retaillee (resolution ecran faible), on recalcule les coordonnees avec le ratio
			if(ratio!=1){
				var coords = this.coords.split(",");
				info.coords = '';
				$(coords).each(function(i){info.coords+=((i>0)?",":"") + (parseFloat(this)*ratio);});
			}
			var area = $('<area shape="rect" coords="' + this.coords + '"/>');
			area.bind('mouseover',function(){
				if(info.path!=''){
					$('#idPreviewPhoto').attr('src',info.path).show();
				}
				else{
					$('#idPreviewPhoto').hide();
				}
				$('#idTitreMoto').text(info.name);
				$('#idCommentaireMoto').text(info.commentaire + ((info.commentaire!='')?' : ':''));
			});
			$('#idMapFrise').append(area);
		});
	}});
}

if(userToLoad!=''){
	loadUser(userToLoad);
}

</script>
</div>

<? writeFooter(); ?>
