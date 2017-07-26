<? // On renvoie les journees

require_once("MediaDao.php");
require_once("SortieDao.php");
require_once("GestionTagDao.php");
require_once("JSONBuilder.php");

class MediaService{
   var $dao;

   function MediaService(){
      $this->dao = new MediaDao();
   }

	/* Renvoie la liste des medias pour un groupe donne */	
	function getMedias($idGroup){
		$data = $this->dao->getByGroup($idGroup);
		$photos = $data->getInfo("photos");

		$dates = "";
		$startDate = null;
		$first = true;
		$lgDate = 10;	// On renvoie les dates au format heure
		if($idGroup!=null && $idGroup!=33){
			$lgDate = 19;
		}
		foreach($photos as $p){
			if($startDate == null || $this->compareDate($startDate,$p->getInfo("date"))){
				$startDate = $p->getInfo("date");
			}
			$dates.=(($first!=true)?",":"") . sprintf("{\"startDate\":\"%s\",\"endDate\":\"%s\",\"headline\":\"%s\",\"data\":\"%s\",\"isTimeline\":\"%s\"}",
				$this->formatTMDate($p->getInfo("date"),$lgDate),$this->formatTMDate($p->getInfo("date"),$lgDate),$p->getInfo("name"),$p->getInfo("id"),
				($p->getInfo("timeline")!=null)?$p->getInfo("timeline"):"false");
			$first = false;
		}
		
		$json = sprintf("{\"timeline\":{\"type\":\"default\",\"title\":\"%s\",\"folder\":\"%s\",\"headline\":\" \",\"text\":\"\",\"startDate\":\"%s\",\"date\":[%s]}}",
			$data->getInfo("titre"),$data->getInfo("folder"),$this->formatTMDate($startDate,$lgDate),$dates);
		echo $json;
	}
	
	function formatTMDate($date,$length){
		return substr(str_replace("-",",",str_replace(":",",",str_replace(" ",",",$date))),0,$length);
	}
	
	function compareDate($data1,$date2){
		return $date1 < $date2;
	}

	/* Renvoie la liste des photos pour un media donne */
	function getPhotosOfMedia($idMedia){
		$tab = $this->dao->getPhotosOfMedia($idMedia);
		if($tab == null){
			echo "{\"message\":\"Pas de données\"}";
			return;
		}
		$json = $this->writePhotosJSON($tab);
		echo $json;
	}

   /* Recherche les photos par criteres (champ libre) */
   function searchPhotos($request,$from,$size){
	  $gestionTagDao = new GestionTagDao();
      $tab = $gestionTagDao->search($request,$from,$size);

      $photos = $tab[1];
      $nb = $tab[0];
      
      if(sizeof($photos) == 0){
         echo "{\"message\":\"Pas de données\"}";
			return;
      }
      $tab = new Data();
      $tab->addInfo("photos",$photos);
      $tab->addInfo("nb",$nb);
      $json = $this->writePhotosJSON($tab);
      echo $json;
   }

	function writePhotosJSON($tab){
		$json = "{";
      /* Cas d'un scan de repertoire, cas du parcours d'un repertoire */
      if($tab->getInfo("type") == "scan"){
			/* On ajoute le titre de la serie ainsi que le group */
			$json.=sprintf("\"root\":{\"value\":\"%s\",\"ldDir\":\"ld\",\"sdDir\":\"sd\"",$tab->getInfo("root"));
			if($tab->getInfo("titre")!=null){
				$json.=",\"titre\":\"" . $tab->getInfo("titre") . "\",\"group\":\"" . $tab->getInfo("group") . "\"";
			}
			$json.="},";
		}
      else{
         /* Chaque image a le chemin complet (exemple de la recherche) */
         if($tab->getInfo("nb")!=null){
            $json.="\"nb\":\"" .$tab->getInfo("nb") . "\",";
         }
      }
		$photos = $tab->getInfo("photos");
      $json.="\"photos\":[";
		for($i = 0 ; $i < sizeof($photos);$i++){
			if($photos[$i]!=null){
            if($tab->getInfo("type") == "scan"){
               $json.=(($i>0)?",":"") . sprintf("{\"name\":\"%s\",\"width\":\"%s\",\"height\":\"%s\",\"tag\":\"%s\"}",$photos[$i]->getInfo("name"),
                  $photos[$i]->getInfo("width"),$photos[$i]->getInfo("height"),$photos[$i]->getInfo("tag"));
            }
            else{
               /* On precise le root*/
               $json.=(($i>0)?",":"") . sprintf("{\"root\":\"%s\",\"name\":\"%s\",\"width\":\"%s\",\"tag\":\"%s\"}",
                  $photos[$i]->getInfo("root"),$photos[$i]->getInfo("name"),$photos[$i]->getInfo("width"),$photos[$i]->getInfo("tag"));
            }
			}
		}
		$json.="]}";
		return $json;
	}
	
	/* Renvoie les infos d'une photo (tag) */
	function getTagInfos($idPhotoTag){
		$infos = $this->dao->getTagInfos($idPhotoTag);
		if($infos == null){
			echo "{error:\"Pas d'info\"}";
		}
		$json=sprintf("{\"date\":\"%s\",\"type\":\"%s\",\"commentaire\":\"%s\",\"personnes\":\"%s\",\"motos\":\"%s\",\"motsCles\":\"%s\",\"lieux\":\"%s\"",
			$infos->getInfo("date"),$infos->getInfo("type"),$infos->getInfo("commentaire"),
			$infos->getInfo("personnes"),$infos->getInfo("motos"),$infos->getInfo("motsCles"),
			$infos->getInfo("lieux"));
		$json.="}";
		echo $json;
	}
	
	/* Renvoie une sortie a partir de son id */
	function getSortieById($id){
		$dao = new SortieDao();
		$sortie = $dao->getSortieById($id);
		$this->writeInfosSortie($sortie);	
	}
	
	/* Renvoie une sortie a partir de son nom */
	function getSortie($nomSortie){
		$dao = new SortieDao();
		$sortie = $dao->getSortie($nomSortie);
		$this->writeInfosSortie($sortie);		
	}
	
	function writeInfosSortie($sortie){
		if($sortie == null){
			echo "{\"message\":\"ko\"}";
			return;
		}
		
		$json=sprintf("{\"sortie\":{\"value\":\"%s\",\"type\":\"%s\"},\"desc\":\"%s\",\"group\":{\"value\":\"%s\",\"media\":\"%s\"}",
			$sortie->getInfo("title"),$sortie->getInfo("type"),preg_replace("/(\r\n|\n|\r)/","",$sortie->getInfo("desc")),$sortie->getInfo("group"),$sortie->getInfo("media"));
		$images = $sortie->getInfo("images");
		if($images != null){
			$json.=sprintf(",\"root\":{\"value\":\"%s\",\"ldDir\":\"ld\",\"sdDir\":\"sd\"}",$images->getInfo("root"));
			$photos = $images->getInfo("photos");
			$pas = sizeof($photos) / 40;
			$json.=",\"images\":[";
			for($i = 0 ; $i < sizeof($photos);$i++){
				if($photos[$i]!=null && $i%$pas == 0){
					$json.=sprintf((($i>0)?",":"")."{\"name\":\"%s\",\"width\":\"%s\",\"tag\":\"%s\"}",$photos[$i]->getInfo("name"),$photos[$i]->getInfo("width"),$photos[$i]->getInfo("tag"));
				}
			}
			$json.="]";
		}
		$json.=",\"personnes\":[";
		$personnes = $sortie->getInfo("personnes");
		for($i = 0 ; $i < sizeof($personnes) ; $i++){
			$json.=sprintf((($i>0)?",":"")."{\"name\":\"%s\",\"surname\":\"%s\",\"photo\":\"%s\",\"id\":\"%s\"}",$personnes[$i]->getInfo("nom"),$personnes[$i]->getInfo("prenom"),
				$personnes[$i]->getInfo("photo"),$personnes[$i]->getInfo("id"));
		}

		$json.="]}";
		echo $json;
	}

   /* Renvoie les demandes de photos HD */
   function getDemandesPhotosHD(){
      $users = $this->dao->getDemandesPhotosHD();
      $jsonBuilder = new JSONBuilder();
      echo $jsonBuilder->set("users",$users)->build();
   }

   /* Enregistre la demande d'une photo HD */
   /* On verifie que la demande n'existe pas deja */
   function pushDemandePhotoHD($path,$idUser){
      // On format le chemin
      $path = str_replace("/sd/","/hd/",substr($path,strrpos($path,"/photos/") + strlen("/photos/")));
      try{
         $this->dao->pushDemandePhotoHD($path,$idUser);
         echo "{\"message\":\"ok\"}";
      }catch(Exception $e){
         echo "{\"error\":\"" . $e->getMessage() . "\"}";
      }

   }

   /* Renvoie les demandes d'un jour */
   function getDemandesOfUser($idUser){
      $demandes = $this->dao->getDemandesOfUser($idUser);
      $jsonBuilder = new JSONBuilder();
      echo $jsonBuilder->set("demandes",$demandes)->build();
   }
   
   function deleteDemandes($idTags, $idUser){
     // On recupere les infos de tag (image) et on verifie si les tags appartiennent au user
     

   }
}
?>
