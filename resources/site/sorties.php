<? require_once("decorator.php"); ?>
<? require_once("SortieDao.php"); ?>
<? require_once("SecuriteDao.php"); ?>
<? writeHeader("Sorties","sortie"); ?>


<script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCf0e7TC34X6EzkpsvtKyuqebjiosrzwak&sensor=false" type="text/javascript"></script>


<script type="text/javascript" src="js/jquery/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/skin.css" />	<!-- utilisé par le caroussel -->
<script language="Javascript" src="js/sorties.js">;</script>
<script language="Javascript" src="js/photos.js">;</script>
<script type="text/javascript" src="js/jquery.lightbox-0.5.js"></script>
<link href="css/photos.css" rel="stylesheet"/>
<link href="css/sorties.css" rel="stylesheet"/>

<div style="float:right">
	<button onclick="showJournee();">Journée</button>
	<button onclick="showPentecote();">Pentecôte</button>
	<button onclick="hideShowBloc($(this));" id="idHideButton">Cacher</button>
</div>
<div style="clear:both"></div>
<div id="idGmap">
	<table>
		<tr>
			<td>
				<div id="map_canvas" style="width:400px; height: 400px;border:3px solid gray"></div>
			</td>
			<td style="padding-left:10px;display:" id="idJournee">
				<table>
					<tr>
						<td id="idControles" style="width:80px"></td>
						<td>
							<div style="text-align:center">
								<img src="img/chevron_haut.gif" style="cursor:pointer;margin:auto" title="Remonter" id="monte"/>
							</div>
							<span style="cursor:pointer;color:black" id="idSortie_vaires">Vaires</span><br/>
							<div style="height:300px;position:relative;overflow:hidden;width:400px;">
								<div style="position:absolute" id="idDivSorties">
									<?
										$dao = new SortieDao();
										$sortiesJ = $dao->getSortiesJournees();
										$sortiesJ = array_reverse($sortiesJ);
										$divCree = array();
										foreach($sortiesJ as $sortie){
											if(!isset($divCree[$sortie->getInfo("annee")])){
												if(sizeof($divCree)>0){
													echo "</div>";
												}
												echo "<div title=\"" . $sortie->getInfo("annee") . "\">";
												echo "<span style=\"float:left;padding-left:10px;padding-right:5px;font-size:12px;font-weight:bold;\">"
													. $sortie->getInfo("annee") . "</span><hr style=\"float:left;width:300px;\"/><br/>";
												$divCree[$sortie->getInfo("annee")] = 1;
											}
											echo "<span  style=\"cursor:pointer;font-size:14px;\" id=\"idSortie_" . $sortie->getInfo("id") . "\">"
												. $sortie->getInfo("titre") . "</span><br/>";
										}
										echo "</div>";
									?>
								</div>
							</div>
							<div style="text-align:center">
								<img src="img/chevron_bas.gif" style="cursor:pointer;margin-top:5px;" title="Descendre" id="descends"/>
							</div>
						</td>
					</tr>
				</table>
			</td>
			<td style="padding-left:50px;display:none" id="idPentecote">
				<table>
					<tr>
						<td>
							<span style="cursor:pointer;color:black" id="idSortie_vaires">Vaires</span><br/>
								<?
									$dao = new SortieDao();
									$sortiesP = $dao->getSortiesPentecote();
									foreach($sortiesP as $sortie){
										echo "<span style=\"cursor:pointer;font-size:14px;\" id=\"idSortie_"
											. $sortie->getInfo("id") . "\">" . $sortie->getInfo("titre") . "</span><br/>";
									}

								?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>

<div style="width:100%">
	<div class="gallery" style="margin-top:20px;float:right;width:360px;overflow:auto;display:none" id="idPhotos"></div>

	<div id="sortie" style="display:none;" >
		<div id="title_sortie" style="padding-left:50px;padding-top:20px;font-size:20px;font-weight:bold;color:black"></div>
		<div id="desc_sortie" style="padding:20px;font-size:13px;"></div>
		<div style="padding-left:20px;font-size:13px;font-weight:bold">
			Retrouvez les photos <a href="" id="idLinkPhotos">ici</a>
		</div>
		<div id="idCaroussel" style="margin-top:20px;width:500px;"></div>
	</div>
	<div style="clear:both"></div>
</div>


<script language="Javascript">

	$('#idCaroussel>ul').jcarousel({});
	<?
	$securiteDao = new SecuriteDao();
	if($securiteDao->isConnected()){
		echo "PointObject.bindMethod=function(id){document.location.href = 'trombino.php?idUser=' + id;};";
	}?>
	initMaps();
	<?
	foreach($sortiesJ as $sortie){
		echo "addJourneePoint(new PointObject('" . $sortie->getInfo("id") . "','" . $sortie->getInfo("code") . "','" . $sortie->getInfo("posX") . "','" . $sortie->getInfo("posY") . "'));\n";
	}
	foreach($sortiesP as $sortie){
		echo "addPentecotePoint(new PointObject('" . $sortie->getInfo("id") . "','" . $sortie->getInfo("code") . "','" . $sortie->getInfo("posX") . "','" . $sortie->getInfo("posY") . "'));\n";
	}

	?>
	map.showJournee();
   map.buildInfoMarkers();
   <?
      if($_GET['idSortie']!=''){
         $id = $_GET['idSortie'];
         ?>
         if($('span[id*="dSortie_<? echo $id;?>"]','#idJournee').length==0){
            showPentecote();
         }
         $('span[id^=\"idSortie_<? echo $id;?>\"]').click();
      <?}?>
      

</script>

<? writeFooter(); ?>
