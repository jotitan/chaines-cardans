<?php

require_once("Model.php");

session_start();

unset($_SESSION["idFriseUser"]);

header("Content-type:image/png");

include("UserDao.php");

/* Creation de l'image */
unset($_SESSION["mapSession"]);

$image = ImageCreate(835,120) or die ("erreur");

/* Definition des couleurs utilisees dans la frise */
$blanc = ImageColorAllocate($image,255,255,255);
$noir = ImageColorAllocate($image,0,0,0);

$haut = 10;

/* Definition de la frise */

ImageLine($image,20,$haut,800,$haut,$noir);
ImageLine($image,20,$haut+60,800,$haut+60,$noir);
ImageLine($image,790,$haut-10,830,$haut+30,$noir);
ImageLine($image,790,$haut+70,830,$haut+30,$noir);


/* 1) Recuperation des periodes */
$iduser = $_GET["idUser"];
$dao = new UserDao();
$tab = $dao->getMotosOfUser($iduser);

/* 2) Determination de la longueur de chaque periode */


/* Traitement seulement s'il y a des periodes */
if(sizeof($tab)>0){
	/* 3) Definition du pas (longueur de chaque annee) */
	$min = floor($tab[0]->getInfo("debut") / 12);	// correspond a la date de debut qu'on deduit (pour commencer a la date)
	$inter = date("Y") - $min + 1;
	/* Correspond a la longueur d'une annee */
	/* La taille minimum est de 50px, en dessous il faut lancer l'algo pour recuperer de la place */
	$pas = 780/$inter;
	
	/* 4) on calcule les periodes et cherche les chevauchements */
	$pasMois = $pas/12;
	for($i=0;$i<sizeof($tab);$i++){
		$debut = $tab[$i]->getInfo("debut");
		$fin = $tab[$i]->getInfo("fin");
		/* On defini la longueur de la periode*/
		$lg = ($fin-$debut)*$pasMois;
		$tab[$i]->addInfo("longueur",$lg);
		$tab[$i]->addInfo("vertical",0);
		$tab[$i]->addInfo("epaisseur",1);
		// on determine la couleur
		$color = ImageColorAllocate($image,$tab[$i]->getInfo("R"),$tab[$i]->getInfo("V"),$tab[$i]->getInfo("B"));
		$tab[$i]->addInfo("couleur",$color);
		
		/* On defini la position de debut de la periode */
		$tab[$i]->addInfo("position",($debut - $min*12)*$pasMois);
	}
	
	$tableaux = array();
	$maxLigne = 0;

	for($i=0;$i<sizeof($tab);$i++){
		$elem = $tab[$i];
		// On verifie la place sur la ligne
		$ligne = 0;
		$find = false;
		while($ligne < 3 && !$find){
			// On verifie la place pour la ligne J
			$elemFind = false;
			for($k = 0 ; $k < sizeof($tableaux) && !$elemFind; $k++){
				$e = $tableaux[$k];
				if($e->getInfo("ligne") == $ligne 
					&& 
					(
						(
							$elem->getInfo("position") > $e->getInfo("position")	
								&& $elem->getInfo("position") < ($e->getInfo("position") + $e->getInfo("longueur"))
						)
						|| 
						(
							($elem->getInfo("position")+$elem->getInfo("longueur")) > $e->getInfo("position")	
								&& ($elem->getInfo("position")+$elem->getInfo("longueur")) < ($e->getInfo("position") + $e->getInfo("longueur"))
						)
					)
				){
					$elemFind = true;
				}
			}
			if($elemFind == false){
				$find = true;
			}
			else{
				$ligne++;
			}
		}
		$maxLigne = max($maxLigne,$ligne);
		$elem->addInfo("ligne",$ligne);
		$tableaux[sizeof($tableaux)] = $elem;
	}

	/* Affichage des dates */	
	for($i=0;$i<$inter;$i++){
		ImageLine($image,$i*$pas+20,$haut+55,$i*$pas+20,$haut+65,$noir);
		$annee = strval($min+$i);
		ImageString($image,1,$i*$pas+30,$haut + 70,$annee[0],$noir);
		ImageString($image,1,$i*$pas+30,$haut + 78,$annee[1],$noir);
		ImageString($image,1,$i*$pas+30,$haut + 86,$annee[2],$noir);
		ImageString($image,1,$i*$pas+30,$haut + 94,$annee[3],$noir);
	}
	
	/* On serialise la reponse dans la base */	
	$areaResponse = "";
	$areaResponse.="{\"areas\":[";
	$first = true;
	for($i=0;$i<sizeof($tableaux);$i++){
	
		if($tableaux[$i]->getInfo("debut")!=null){
			$pos = $tableaux[$i]->getInfo("position");
			//imagefttext($image,8,45,20 + $pos,$haut-5,$noir,"police/arial.ttf",$tableaux[$i]->getInfo("moto"));
			$epaisseur = 58 / ($maxLigne+1);
			$posVertical = $haut + 1 + $tableaux[$i]->getInfo("ligne") * $epaisseur;
			$col = $tableaux[$i]->getInfo("couleur");
			ImageFilledRectangle($image,15 + $pos,$posVertical,15 + $pos + $tableaux[$i]->getInfo("longueur"),$posVertical + $epaisseur-1,$col);
			/* On enregistre ces coordonnes dans la session */
			$moto = $dao->getMoto($tab[$i]->getInfo("id"));
			if($moto!=null){
				$rvb =  imagecolorsforindex($image,$col);
				$color = "rgb(" . $rvb['red'] . "," . $rvb['green'] . "," . $rvb['blue'] . ")";
				$name = $moto->getInfo("marque") . " " . $moto->getInfo("cylindre") . " " . $moto->getInfo("nom");
				$coords = (15 + $pos) . "," . $posVertical . "," . (15 + $pos + $tab[$i]->getInfo("longueur")) . ","  . ($posVertical + $epaisseur);
				$path = $tab[$i]->getInfo("pathPhoto");
				$areaResponse.= ((!$first)?",":"") . sprintf("{\"name\":\"%s\",\"color\":\"%s\",\"coords\":\"%s\",\"path\":\"%s\"}",
					$name,$color,$coords,$path);
				$first = false;
			}			
		}
	}
	$areaResponse.="]}";
	$dao->saveDataFrise($areaResponse,$_SESSION["user"]->id,$iduser);
}
ImagePng($image);

?>
