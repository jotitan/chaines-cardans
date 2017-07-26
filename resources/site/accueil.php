<? require_once("decorator.php"); ?>
<? $connected = writeHeader("accueil","accueil"); ?>


<div style="padding-top:20px;">

<div style="margin:auto;width:90%;border-radius:10px;border:solid 3px #004c62;">

	<div style="margin-left:10px;font-size:12px;clear:both;padding-top:5px;padding-bottom:5px;">
		<img src="http://vbaranzini.free.fr/v2.0/photos/2010_DUNKERQUE/51-RESUME/sd/51-resume33.jpg" 
			style="height:140px;float:left;margin-right:10px;margin-bottom:10px;margin-left:-13px;border-top-left-radius:10px;border-bottom-right-radius:10px;margin-top:-5px;"/>			
		Et au commencement, il y avait… Vincent, et son envie de faire des sorties motos entre amis. 
		Son idée, pas de compétition, pas de recherche de vitesse, juste des balades dans nos belles régions de France. 
		(ndlr: il faut dire qu'il connaît les petites routes comme personne). Et c'est ainsi qu'est née, Chaînes & Cardans.
		<br/><br/>
		La première sortie a été organisée à la pentecôte en 1995, à la Chapelle des Bois avec juste 7 motos et 12 présentes. 
		Avec le temps, l'organisation s'est affutée, les sorties sont maintenant différenciées en deux catégories, les sorties sur journée, 
		planifiées toute au long de l'année en fonction de la météo et le week-end pentecôte, avec une préparation plus pointue 
		qui commence un an à l'avance. Et si certains sont présents au deux, nous avons aussi des amis motards qui ne viennent 
		qu'aux sorties sur journée ou, à l'inverse, qu'au week-end pentecôte. 
		<br/><br/>
		Egalement, avec le temps, le visage de Chaînes & Cardans s'est modifié. Certains motards sont présents depuis le début et 
		d'autres n'arriveront que cette année, pendant que d'autres encore se sont éloignés, parfois après avoir amené des amis, qui, eux, sont restés.
		<br/><br/>
		Le groupe a pris une telle importance, qu'il y a quelques années, Vincent a décidé d'officialiser son association. 
		Et Chaînes & Cardans est devenue une véritable association loi 1901 avec une gestion en famille… Parce qu'après tout, 
		quand on connaît Vincent, on sait que son association ne pouvait être envisagée autrement qu'avec Judith et Jonathan !
		<br/><br/>
		Au final, une seule chose n'a pas changé, c'est l'esprit donné à l'association… des balades, 
		en toute amitié, en toute simplicité et pleines de bonne humeur où tout le monde est le bienvenu… à une seule condition, 
		avoir une bécane (et encore, des membres à part entière de Chaînes & Cardans sont automobilistes).
		<br/><br/>
		Ce site a été créé par Jonathan afin de retracer les sorties de Chaînes & Cardans. 
		Il est mis à jour dès que Jonathan a le temps de traiter les photos, ce qui n'est pas simple, 
		notamment lorsqu'on voit le nombre de photos en question. 
		C'est pourquoi, il faut parfois être patient. Mais nous espérons que vos visites sur la nouvelle version du site vous rappelleront de bons souvenirs….
	</div>
</div>

<div style="margin-top:20px;width:300px;margin-left:20px;border-radius:10px;border:solid 3px #004c62;float:left">
	<div style="margin-left:10px;font-size:12px;clear:both;padding-top:5px;">
		<img src="img/news.png" 
			style="height:88px;float:left;margin-right:10px;margin-left:-13px;border-top-left-radius:10px;border-bottom-left-radius:10px;margin-top:-5px;"/>

		Toutes les dernières infos sur C&C, les sorties, les mises à jour du site, les manifestations et les coups de gueule...<br/> 
		Voir <a href="news.php" style="color:#004c62">les news...</a>
	</div><div style="clear:both"></div>
</div>

<div style="margin-top:20px;width:300px;margin-left:20px;border-radius:10px;border:solid 3px #004c62;float:left">
	<div style="margin-left:10px;font-size:12px;clear:both;padding-top:5px;">
		<img src="img/film_icone.png" 
			style="height:88px;float:left;margin-right:10px;margin-left:-13px;border-top-left-radius:10px;border-bottom-left-radius:10px;margin-top:-5px;"/>

		Toutes les photos des sorties motos, journée et pentecôte.<br/> 
		Voir <a href="photos.php" style="color:#004c62">les photos...</a>
	</div><div style="clear:both"></div>
</div>

<? if($connected == true){ ?>
<div style="margin-top:20px;width:300px;margin-left:20px;border-radius:10px;border:solid 3px #004c62;float:left">
	<div style="margin-left:10px;font-size:12px;clear:both;padding-top:5px;">
		<img src="img/search_photo.png"
			style="height:88px;float:left;margin-right:10px;margin-left:-13px;border-top-left-radius:10px;border-bottom-left-radius:10px;margin-top:-5px;"/>
		Rechercher facilement des photos à travers toute la base de Chaînes & Cardans, plus de
      <span style="font-weight:bold">4000</span>... <br/>
		Voir <a href="searchPhotos.php" style="color:#004c62">la suite...</a>
	</div><div style="clear:both"></div>
</div>
<? } ?>

<div style="margin-top:20px;width:300px;margin-left:20px;border-radius:10px;border:solid 3px #004c62;float:left">
	<div style="margin-left:10px;font-size:12px;clear:both;padding-top:5px;">
		<img src="img/terre_icone.png" 
			style="height:88px;float:left;margin-right:10px;margin-left:-13px;border-top-left-radius:10px;border-bottom-left-radius:10px;margin-top:-5px;"/>
		Toutes les sorties depuis 1996. <br/> 
		Voir <a href="sorties.php" style="color:#004c62">la suite...</a>
	</div><div style="clear:both"></div>
</div>

<div style="clear:both"></div>

</div>

<? writeFooter(); ?>
