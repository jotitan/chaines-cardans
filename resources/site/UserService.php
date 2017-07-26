<? // On renvoie les journees

require_once("UserDao.php");
require_once("Model.php");
require_once("MediaService.php");

class UserService{
	var $dao = null;

	function UserService(){
		$this->dao = new UserDao();
	}

	/* Renvoie les informations d'un utilisateur : 
	* Nom, prenom, mail, liste des sorties, listes des photos
	*/
	function getUser($idUser){
		$user = $this->dao->getUser($idUser);
		
		if($user == null){
			echo "{\"message\":\"Aucun utilisateur\"}";
			return;
		}
		$json=sprintf("{\"nom\":\"%s\",\"prenom\":\"%s\",\"email\":\"%s\",\"anniversaire\":\"%s\"",
			$user->getInfo("nom"),$user->getInfo("prenom"),$user->getInfo("email"),$user->getInfo("anniversaire"));
		if($user->getInfo("photo")!=null){
			$json.=",\"photo\":\"" . $user->getInfo("photo") . "_.jpg\"";
		}
		/* Gestion des sorties */
		if($user->getInfo("sorties")!=null){
			$json.=",\"sorties\":[";
			foreach($user->getInfo("sorties") as $i => $sortie){
				if($i!=0){$json.=",";}
				$json.=sprintf("{\"id\":\"%s\",\"title\":\"%s\",\"media\":\"%s\",\"type\":\"%d\",\"group\":\"%s\"}",
					$sortie->getInfo("id"),$sortie->getInfo("title"),
					$sortie->getInfo("idMedia"),$sortie->getInfo("type"),$sortie->getInfo("group"));
			}
			$json.="]";
		}
		/* Gestion des photos */
		if($user->getInfo("photos")!=null && $user->getInfo("photos")->getInfo("photos")!=null){
			$service = new MediaService();
			$json.=",\"photos\":" . $service->writePhotosJSON($user->getInfo("photos"));
		}
		$json.="}";
		echo $json;
	}
	
	function getFriseInfos($idDemandeUser,$idUser){
		$data = $this->dao->getFriseInfos($idDemandeUser,$idUser);
		if($data == null){
			echo "{\"message\":\"Pas d'infos\"}";
		}
		else{
			echo $data;
		}
	}
	
	/* Met a jour une information de la frise de l'utilisateur */
	function updateFieldFriseMoto($idUser,$idPeriode,$field,$value,$loginUser){
		$retour = $this->dao->updateFieldFriseMoto($idUser,$idPeriode,$field,$value,$loginUser);
    echo "{\"message\":\"" . (($retour == null)?"ko":"ok") . "\",\"value\":\"" . $retour . "\"}";

	}

	/* Cherche une photo dans la base de donnees */
	function searchMotoByName($chaine){
		
	  $motos = $this->dao->searchMotoByName($chaine); 
	  $json = "[";
    $pos = 0;
    foreach($motos as $moto){
      $json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%s\",\"label\":\"%s %s\"}",$moto->getInfo("id"),$moto->getInfo("marque"),$moto->getInfo("nom"));
    }
		$json.="]";
		echo $json;
	}
  
  /* Cree une periode vide pour un utilisateur */
  function createPeriode($idUser){
    $id = $this->dao->createPeriode($idUser);
    echo "{\"id\":\"" . $id . "\"}";
  }

  /* Supprime une periode */
  function removePeriode($idUser,$idPeriode){
	$this->dao->removePeriode($idUser,$idPeriode);
	echo "{\"message\":\"ok\"}";
  }
}
?>
