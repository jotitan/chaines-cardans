<?


/* Service de gestion des tags des photos */
/* Implements les methodes de GestionTagAction */

include("GestionTagDao.php");

class GestionTagService{
   var $dao;

   function GestionTagService(){
      $this->dao = new GestionTagDao();
   }

  /* Renvoie la liste des personnes dont le nom commence par $name */
  /* @Case 2 */
  function getPersonnesByName($name){
  	$personnes = $this->dao->getPersonnesByName($name);
  	
    $json = "[";	
  	$pos = 0;
    foreach($personnes as $id => $personne){
  		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"label\":\"%s\"}",$id,$personne);
  	}
  	$json.="]";
    echo $json;
  }


   /*  */
   /* @case 3 */
   function getMotosByName($name){
       $personnes = $this->dao->getMotosByName($name);

        $json = "[";
      	$pos = 0;
        foreach($personnes as $id => $personne){
      		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"label\":\"%s\"}",$id,$personne);
      	}
      	$json.="]";
        echo $json;
   }

  /* Renvoie les mots cles a partir du nom */
  /* @case 4 */
  function getMotsClesByName($name){
	$motsCles = $this->dao->getMotsClesByName($name);
	$json = "[";
      	$pos = 0;
        foreach($motsCles as $id => $motcle){
      		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"label\":\"%s\"}",$id,$motcle);
      	}



      	$json.="]";
        echo $json;
  }
  
  /* @case 5 */
  function getLieuxByName($name){
	$lieux = $this->dao->getLieuxByName($name);
	$json = "[";
      	$pos = 0;
        foreach($lieux as $id => $lieu){
      		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"label\":\"%s\"}",$id,$lieu);
      	}
      	$json.="]";
        echo $json;
  }

  /* @case 6 */
  function createMotCle($value){
	$id = $this->dao->createMotCle($value);
	echo "{\"id\":\"" . $id . "\"}";
  }

  /* @case 7 */
  function createLieu($value){
	$id = $this->dao->createLieu($value);
	echo "{\"id\":\"" . $id . "\"}";
  }

  /* @case 8 */
  function getPhotoTag($nb,$idMedia){
    $tag = $this->dao->getPhotoTag($nb,$idMedia);
    if($tag == null){
      echo "{\"erreur\":\"Aucune photo ne correspond\"}";
    }
    $json = $this->formatPhotoTag($tag);
    echo $json;
  }

	function formatPhotoTag($tag){
		if($tag == null){return "\"\"";}
		$json = sprintf("{\"id\":\"%d\",\"idMedia\":\"%d\",\"path\":\"%s\",\"date\":\"%s\",\"type\":\"%d\",\"commentaire\":\"%s\"",
		  $tag->id,$tag->idMedia,$tag->path,$tag->date,$tag->type,$tag->commentaire);
		$json.=$this->writeListe("personnes",$tag->personnes);
		$json.=$this->writeListe("lieux",$tag->lieux);
		$json.=$this->writeListe("motsCles",$tag->motsCles);
	    $json.=$this->writeListe("motos",$tag->motos);

	    $json.="}";
		return $json;
	}

	/** Renvoie les informations d'un media ainsi que les infos de la premiere image */
	/* @case 9 */
	function getInfoMedia($idMedia){
		$infos = $this->dao->getInfoMedia($idMedia);
		$tag = $this->dao->getPhotoTag(0,$idMedia);
   
		$json = "{\"nb\":\"" . $infos[0] . "\",\"nbTags\":\"" . $infos[1] . "\",\"tag\":" . $this->formatPhotoTag($tag) . "}";

		echo $json;
	}

	/* Supprime une personne du tag photo */
	/* @case 10 */
	function removePersonneToTag($idTag,$id){
		$this->dao->removePersonneToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 11 */
	function removeMotoToTag($idTag,$id){
		$this->dao->removeMotoToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 12 */
	function removeMotCleToTag($idTag,$id){
		$this->dao->removeMotCleToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 13 */
	function removeLieuToTag($idTag,$id){
		$this->dao->removeLieuToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 14 */
	function addPersonneToTag($idTag,$id){
		$this->dao->addPersonneToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 15 */
	function addMotoToTag($idTag,$id){
		$this->dao->addMotoToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 16 */
	function addMotCleToTag($idTag,$id){
		$this->dao->addMotCleToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Supprime une personne du tag photo */
	/* @case 17 */
	function addLieuToTag($idTag,$id){
		$this->dao->addLieuToTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}

	/* Met a jour le type du tag */
	/* @case 18 */
	function setTypeTag($idTag,$id){
		$this->dao->setTypeTag($idTag,$id);
		echo "{\"message\":\"ok\"}";
	}

	/* Met a jour le commentaire du tag */
	/* @case 18 */
	function setCommentaire($idTag,$commentaire){
		$this->dao->setCommentaire($idTag,$commentaire);
		echo "{\"message\":\"ok\"}";
	}
	
	/* Duplique un tag a partir d'un autre */
	/* @case 20*/
   function duplicateTags($idMedia,$nbFrom,$idTagTo){
      $id = $this->dao->getIdPhotoTag($nbFrom,$idMedia);
      if($id<=0){
         echo "{\"erreur\":\"Impossible de dupliquer\"}";
      }
      else{
         $nbInfos = $this->dao->duplicateTags($id,$idTagTo);
         echo "{\"nb\":\"" . $nbInfos . "\"}";
      }
   }



  function writeListe($name,$liste){
    if($liste == null || sizeof($liste) == 0){return "";}
    $json = ",\"" . $name . "\":[";
    $pos = 0;
    foreach($liste as $obj){
      if($pos++ >0){$json.=",";}
      $json .= sprintf("{\"id\":\"%d\",\"label\":\"%s\"}",$obj->value,$obj->label);
    }
    $json.="]";
    return $json;
  }
}

?>
