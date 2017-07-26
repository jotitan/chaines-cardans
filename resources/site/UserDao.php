<?

require_once("ConnectionBDD.php");
require_once("Model.php");
require_once("MediaDao.php");
require_once("GestionImage.php");

/**
* Gere les utilisateurs
*/
class UserDao{
	
	var $mappingsPeriode = array();	// Mapping pour la periode entre un nom et le champ qui correspond

	function UserDao(){
		$this->mappingsPeriode["nom"] = "ID_MOTO";
		$this->mappingsPeriode["moisdebut"] = "MOIS_DEBUT";
		$this->mappingsPeriode["anneedebut"] = "ANNEE_DEBUT";
		$this->mappingsPeriode["moisfin"] = "MOIS_FIN";
		$this->mappingsPeriode["anneefin"] = "ANNEE_FIN";
    	$this->mappingsPeriode["color"] = "ID_COULEUR";
		$this->mappingsPeriode["photo"] = "PATH_PHOTO";
		$this->mappingsPeriode["commentaire"] = "COMMENTAIRE";
	}

	/* Renvoie les informations d'un utilisateur */
	function getUser($idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$result = mysql_query("SELECT nom,prenom,path_photo,email,date_format(birthday_user,'%d/%m/%Y') FROM `Utilisateur` WHERE id_user =" . $idUser);
		if(mysql_num_rows($result) == 0){
			$connectService->close();	
			return null;
		}
		else{
			$user = new Data();
			$user->addInfo("nom",mysql_result($result,0,0));
			$user->addInfo("prenom",mysql_result($result,0,1));
			if(mysql_result($result,0,2)!=null){
				$user->addInfo("photo","../v2.0/" . mysql_result($result,0,2));
			}			
			$user->addInfo("email",mysql_result($result,0,3));
			$user->addInfo("anniversaire",mysql_result($result,0,4));
			/* Gestion des sorties */
			$result = mysql_query("SELECT s.id_sortie, s.title_sortie, s.id_media, id_group "
				. " FROM sortie_a_personne sap join sortie s on sap.id_sortie = s.id_sortie where id_personne = " . $idUser
				. " order by date_sortie desc");
			$sorties = array();
			for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
				$sortie = new Data();
				$sortie->addInfo("id",mysql_result($result,$i,0));
				$sortie->addInfo("title",utf8_encode(mysql_result($result,$i,1)));
				$sortie->addInfo("idMedia",mysql_result($result,$i,2));
				$sortie->addInfo("type",mysql_result($result,$i,3)!="33"?1:0);
				$sortie->addInfo("group",mysql_result($result,$i,3));
				
				$sorties[$i] = $sortie;
			}
			$user->addInfo("sorties",$sorties);
			$connectService->close();
			/* Gestion des photos */			
			$dao = new MediaDao();
			$user->addInfo("photos",$dao->getPhotosOfUser($idUser));			
		}

		return $user;
	}

	/* Renvoie les informations de la frise d'un utilisateur */
	function getFriseInfos($idUserDemande,$idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$result = mysql_query("select CONTENT_AREA from temp_area where ID_USER = " . $idUserDemande . " and ID_MEMBER = " . $idUser);
		if(mysql_num_rows($result) == 0){
			$connectService->close();		
			return null;
		}
		$data = mysql_result($result,0,0);
		mysql_query("delete from temp_area where ID_USER = " . $idUserDemande . " and ID_MEMBER = " . $idUser);
		$connectService->close();
		return utf8_encode($data);
	}

	/* Renvoie l'historique moto d'un utilisateur */
	/* @param nullAccepted : Valeurs null acceptes */
	function getMotosOfUser($idUser,$nullAccepted){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$query = "select *, IdMoto as id, nommoto as nom " . 
				 " from HistoriqueMoto hm left join Motos on id_moto = IdMoto left join Marques on IdMarque = marque , HistoriqueCouleur hc where ID_USER = " . $idUser .
				" and hm.ID_COULEUR = hc.ID_COULEUR ";
		if($nullAccepted == false){
			$query.=" and hm.ID_MOTO is not null";
		}
		$query.=" ORDER BY ANNEE_DEBUT,MOIS_DEBUT ";
		$result = mysql_query($query);
		$tab = Array();
		for($i = 0 ; $i<mysql_num_rows($result) ; $i++){
			$data = new Data();
			$cyl = mysql_result($result,$i,"motocmcube");
			$marque = mysql_result($result,$i,"nommarque");
			$nom = mysql_result($result,$i,"nom");
			$data->addInfo("moto",$marque . " " . $cyl . " " . $nom);
			$data->addInfo("id",mysql_result($result,$i,"id"));
			$data->addInfo("idPeriode",mysql_result($result,$i,"ID_PERIODE"));
			$data->addInfo("R",mysql_result($result,$i,"ROUGE"));
			$data->addInfo("V",mysql_result($result,$i,"VERT"));
			$data->addInfo("B",mysql_result($result,$i,"BLEU"));
			$data->addInfo("pathPhoto",mysql_result($result,$i,"PATH_PHOTO"));
			$data->addInfo("commentaire",utf8_decode(mysql_result($result,$i,"COMMENTAIRE")));
		
			$moisDebut = mysql_result($result,$i,"MOIS_DEBUT");
			$anneeDebut = mysql_result($result,$i,"ANNEE_DEBUT");
			$moisFin = mysql_result($result,$i,"MOIS_FIN");
			$anneeFin = mysql_result($result,$i,"ANNEE_FIN");
		
			$data->addInfo("debut",$anneeDebut * 12 + $moisDebut);
			if($moisDebut==null){
        $data->addInfo("dateDebut","");
      }
      else{
        $data->addInfo("dateDebut",(($moisDebut<10)?'0':'') . $moisDebut . "/" . $anneeDebut);
      }
		
			if($moisFin==null ||$anneeFin==null){
				$data->addInfo("fin",date("Y") * 12 + date("m"));
			}
			else{
				$data->addInfo("fin",$anneeFin * 12 + $moisFin);
				$data->addInfo("dateFin",(($moisFin<10)?'0':'') . $moisFin . "/" . $anneeFin);
			}
			$tab[$i] = $data;
		}
		$connectService->close();
		return $tab;
	}

	function getMoto($idMoto){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$result = mysql_query("select mo.nommoto,ma.nommarque,mo.motocmcube from Marques as ma, Motos as mo where mo.marque = ma.IdMarque and mo.IdMoto = " . $idMoto);
		if(mysql_num_rows($result) == 0){return null;}
		$moto = new Data();
		$moto->addInfo("nom",mysql_result($result,0,0));
		$moto->addInfo("marque",mysql_result($result,0,1));
		$moto->addInfo("cylindre",mysql_result($result,0,2));
		$connectService->close();
		return $moto;
	}

	function saveDataFrise($data,$idUserDemande,$idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		mysql_query("insert into temp_area values(null," . $idUserDemande . "," . $idUser . ",'" . $data . "')");
		$connectService->close();
	}

	/* Renvoie tous les utilisateurs actifs (avec une photo) */
	function getUsers(){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$result = mysql_query("select id_user,nom,prenom,path_photo from Utilisateur where path_photo !='' order by nom,prenom");
		$tab = array();
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$user = new Data();
			$user->addInfo("id",mysql_result($result,$i,0));
			$user->addInfo("nom",mysql_result($result,$i,1));
			$user->addInfo("prenom",mysql_result($result,$i,2));
			$user->addInfo("path",mysql_result($result,$i,3));
			$tab[$i] = $user;
		}
		$connectService->close();
		return $tab;
	}

	function updateFieldFriseMoto($idUser,$idPeriode,$field,$value,$loginUser){
		if($field != "debut" && $field != "fin" && $this->mappingsPeriode[$field] == null){return false;}

		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$base = "update HistoriqueMoto set %s = '%s' where ID_USER = %s and ID_PERIODE = %s";
		/* Cas des dates, on decoupe */
		$realValue = true;

		switch($field){
			case "debut" : 
				$mois = substr($value,0,2);
				$annee = substr($value,3,4);
				mysql_query(sprintf($base,$this->mappingsPeriode["mois" . $field],$mois,$idUser,$idPeriode));
				mysql_query(sprintf($base,$this->mappingsPeriode["annee" . $field],$annee,$idUser,$idPeriode));
				break;
			case "fin" : 
				$mois = substr($value,0,2);
				$annee = substr($value,3,4);
				mysql_query(sprintf($base,$this->mappingsPeriode["mois" . $field],$mois,$idUser,$idPeriode));
				mysql_query(sprintf($base,$this->mappingsPeriode["annee" . $field],$annee,$idUser,$idPeriode));
				break;
			case "color" : 
				$idColor = $this->getColor($value);
				mysql_query(sprintf($base,$this->mappingsPeriode[$field],$idColor,$idUser,$idPeriode));
				break;
			case "photo" : 
				// On copie l'image temporaire
				$gestion = new GestionImage();
				$realValue = $gestion->copyTempPhotoToFrise($loginUser);
				mysql_query(sprintf($base,$this->mappingsPeriode[$field],$realValue,$idUser,$idPeriode));
				break;
			default : 
				mysql_query(sprintf($base,$this->mappingsPeriode[$field],$value,$idUser,$idPeriode));
	
		}
/*
		if($field == "debut" || $field == "fin"){
			$mois = substr($value,0,2);
			$annee = substr($value,3,4);
			mysql_query(sprintf($base,$this->mappingsPeriode["mois" . $field],$mois,$idUser,$idPeriode));
			mysql_query(sprintf($base,$this->mappingsPeriode["annee" . $field],$annee,$idUser,$idPeriode));
		}
		else{
			if($field == "color"){
				// On cherche la couleur ou on l'ajoute
				$idColor = $this->getColor($value);
				mysql_query(sprintf($base,$this->mappingsPeriode[$field],$idColor,$idUser,$idPeriode));
			}
			else{
				mysql_query(sprintf($base,$this->mappingsPeriode[$field],$value,$idUser,$idPeriode));
			}
		}*/
		$connectService->close();
    return $realValue;
	}

	/* Renvoie une couleur de la base HistoriqueCouleur a partir d'une chaine de type rgb(xxx,yyy,zzz) 
	*/
	function getColor($colorChaine){
		$tab = split(",",str_replace(")","",str_replace("rgb(","",$colorChaine)));
    $r=mysql_query(sprintf("select ID_COULEUR from HistoriqueCouleur where ROUGE = %s AND VERT = %s AND BLEU = %s",$tab[0],$tab[1],$tab[2]));
    if(mysql_num_rows($r)==0){
      // On l'insere
      mysql_query(sprintf("insert into HistoriqueCouleur (ROUGE,VERT,BLEU) values(%d,%d,%d)",$tab[0],$tab[1],$tab[2]));
      return mysql_insert_id();
    }
    return mysql_result($r,0,0);
	}

	/*
	* Renvoie les marques qui correspondent aux termes dans le tableau
	*/
	function getMarques($marques){
		
		if(sizeof($marques)){return array();}		
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();		
		
    $query = "select * from Marques ";
		for($i = 0 ; $i < sizeof($marques) ; $i++){
			$query+=(($i!=0)?" or":"") . " nommarque like '" . $marques[$i] . "%'";
		}
		$result = mysql_query($query);
    $marques = array();
    for($i = 0 ;$i<mysql_num_rows($result);$i++){
    
    }
    $connectService->close();
		return $marques;
	}
  
  function searchMotoByName($chaine){
    $connectService = new ConnectionBDD();
		$connect = $connectService->connect();		
		
    // On eclate la chaine en morceau
		$tab = explode(" ",strtolower($chaine));
	
		// on recherche les cylindrees
		$tabCyl = preg_grep("`\d{3,}`",$tab);
		$cyl = null;
		if(sizeof($tabCyl)>0){
			$t = array_keys($tabCyl);
			$tab[$t[0]] = null;
			$cyl = $tabCyl[$t[0]];
		}
	         
		// on cherche les textes suceptibles d'etre des marques : taille minimum de 2
		$tabMarques = array_values(preg_grep("`[a-z]{2,}`i",$tab));
		// on cherche pour chaque mot si ca correspond a une marque
		$tabJointureMarques = array();
    $query = "select idmarque,lower(nommarque) from Marques ";
		for($i = 0 ; $i < sizeof($tabMarques) ; $i++){
			$query.=(($i!=0)?" or":" where") . " nommarque like '" . $tabMarques[$i] . "%'";
		}
   	$result = mysql_query($query);
    for($i = 0 ;$i<mysql_num_rows($result);$i++){
    	  $tabJointureMarques[sizeof($tabJointureMarques)] = mysql_result($result,$i,0);
        for($j = 0 ; $j < sizeof($tab);$j++){
          if(strstr(mysql_result($result,$i,1),$tab[$j])!=false){
            $tab[$j] = null;
            break;
          }
        }
    }
   
		// On cree la requete finale : ajout de la cylindree en AND, jointure sur les marques en and (or), et les noms qui restent en and
		$query = "select IdMoto,nommarque,motocmcube,nommoto from Motos, Marques where IdMarque = marque ";
		if($cyl!=null){
			$query.=" and motocmcube = " . $cyl;
		}	
		// prise en compte des marques
		if(sizeof($tabJointureMarques)>0){
			$pos = 0;
			foreach($tabJointureMarques as $key => $value){
				$query.=(($pos++>0)?" or ":" and (") . " marque=" . $value . " ";			
			}		
			$query.=" ) ";
		}
	
		if(sizeof($tab)>0){
			foreach($tab as $key => $value){
				if($value!=null){
					$query.=" and nommoto like '%" . $value . "%' ";
				}
			}
		}
		// on limite le nombre de resultats a 30
		$query.=" limit 0,30";
    $result = mysql_query($query);
	  $motos = array();
    for($i=0;$i<mysql_num_rows($result);$i++){
		  $moto = new Data();
    	$moto->addInfo("id",mysql_result($result,$i,0));
			$moto->addInfo("marque",mysql_result($result,$i,1));
			$moto->addInfo("nom",mysql_result($result,$i,2) . " " . mysql_result($result,$i,3));
			$motos[$i] = $moto;
		}
    
		$connectService->close();
		return $motos;
  }
  
  function createPeriode($idUser){
    $connectService = new ConnectionBDD();
		$connect = $connectService->connect();		
   
    mysql_query(sprintf("insert into HistoriqueMoto (ID_PERIODE,ID_USER,ID_COULEUR) values(NULL,%d,%d)",$idUser,1));
    
    $id = mysql_insert_id();
    $connect = $connectService->close();
	  return $id;
  }

  function removePeriode($idUser,$idPeriode){
	 $connectService = new ConnectionBDD();
	$connect = $connectService->connect();
    mysql_query("delete from HistoriqueMoto where ID_PERIODE = " . $idPeriode . " and ID_USER = " . $idUser);
	$connect = $connectService->close();
  }
}


?>
