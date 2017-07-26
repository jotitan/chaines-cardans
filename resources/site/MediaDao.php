<?

require_once("Model.php");
require_once("Utils.php");
require_once("ConnectionBDD.php");
//require_once("MailService.php");

class MediaDao{
	var $directory = "../v2.0/photos/";
	var $connectService;

	function MediaDao(){
		$this->connectService = new ConnectionBDD();
	}

	/* Renvoie la liste des  */
	function getGroups(){
		$this->connectService->connect();
		$result = mysql_query("select id_group_photo,description_group_photo from group_photos order by 2");

		$groups = array();
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$groups[mysql_result($result,$i,0)] = mysql_result($result,$i,1);
		}

		$this->connectService->close();
		return $groups;
	}

	/* Renvoie la liste des medias pour un element d'un groupe donne */
	function getByGroup($idGroup){
		$data = new Data();
		if($idGroup == null || $idGroup == ""){
			$data->addInfo("photos",$this->getGroupPentecote());
			$data->addInfo("titre","");
			$data->addInfo("folder","PENTECOTES");
			return $data;
		}
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();

		/* Nom du groupe */
		$result = mysql_query("select description_group_photo,folder_photos from group_photos where id_group_photo = " . $idGroup);
		if(mysql_num_rows($result)!=0){
			$data->addInfo("titre",mysql_result($result,0,0));
         $data->addInfo("folder",mysql_result($result,0,1));
		}
		$query =  " SELECT ID_MEDIA, DESCRIPTION, DIR_MEDIA as dir,mp.NB_PHOTO as photos, mpp.NB_PANO as panos,count(mv.NAME_VIDEO) as videos,DATE_MEDIA "
				. " FROM media m "
				. " LEFT JOIN media_photos mp ON mp.FK_ID_MEDIA = m.ID_MEDIA "
				. " LEFT JOIN media_pano mpp ON mpp.FK_ID_MEDIA = m.ID_MEDIA "
				. " LEFT JOIN media_video mv ON mv.FK_ID_MEDIA = m.ID_MEDIA "
				. " WHERE FK_ID_GROUP = " . $idGroup
				. " GROUP BY ID_MEDIA ORDER BY DATE_MEDIA DESC";

		$result = mysql_query($query);
		$tab = array();
		for($i = 0 ; $i < mysql_num_rows($result);$i++){
			$media = new Data();
			$media->addInfo("id",mysql_result($result,$i,"ID_MEDIA"));
			$media->addInfo("name",mysql_result($result,$i,"DESCRIPTION"));
			$media->addInfo("photos",mysql_result($result,$i,"photos"));
			$media->addInfo("date",mysql_result($result,$i,"DATE_MEDIA"));
			if($media->getInfo("photos") == null){
				$media->addInfo("photos",$this->getNbPhotosOfDir($this->directory . mysql_result($result,$i,"dir")));
			}
			$media->addInfo("panos",mysql_result($result,$i,"panos"));
			if($media->getInfo("panos") == null){$media->addInfo("panos",0);}
			$media->addInfo("videos",mysql_result($result,$i,"videos"));
			if($media->getInfo("videos") == null){$media->addInfo("videos",0);}
			$tab[$i] = $media;
		}

		$connectService->close();
		$data->addInfo("photos",$tab);
		return $data;
	}

	function getGroupPentecote(){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$query =  " SELECT * FROM sortie s join  group_photos gp on s.id_group = gp.id_group_photo where s.type_sortie = 1";

		$result = mysql_query($query);
		$tab = array();
		for($i = 0 ; $i < mysql_num_rows($result);$i++){
			$media = new Data();
			/* RG Particuliere : si ID GROUP = 36, ancienne sortie, on renvoie le media pour afficher les photos,
				sinon, on renvoie le group (et on precise que le group peut etre affiche en timeline) */
			$id = mysql_result($result,$i,"ID_GROUP");
			$media->addInfo("id",($id != 36)?$id:mysql_result($result,$i,"ID_MEDIA"));	// Id du media quand il y n'a  pas de sous rubrique
			$media->addInfo("name",mysql_result($result,$i,"TITLE_SORTIE"));
			$media->addInfo("date",mysql_result($result,$i,"DATE_SORTIE"));
			$media->addInfo("timeline",($id==36)?"false":"true");
			$tab[$i] = $media;
		}

		$connectService->close();
		return $tab;

	}

	function getNbPhotosOfDir($nameDir){
		$dir = opendir($nameDir . "/sd");

		$i = 0;
		while( ($photo = readdir($dir))!=false){
			if($photo!=".." && $photo!="."){$i++;}
		}
		return $i;
	}

	/* Renvoie les photos pour un media */
	function getPhotosOfMedia($idMedia){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();

		$result = mysql_query("select * from media_photos, media where FK_ID_MEDIA = ID_MEDIA and ID_MEDIA = " . $idMedia);
		$tab = new Data();
		if(mysql_num_rows($result)==0){
			// on liste le repertoire
			$result = mysql_query("select * from media where ID_MEDIA = " . $idMedia);
			$nb = mysql_num_rows($result);
			if($nb==0){
				$connectService->close();
				return $tab;
			}
			else{
				$tab->addInfo("root",$this->directory . mysql_result($result,0,"DIR_MEDIA"));
				$tab->addInfo("titre",mysql_result($result,0,"DESCRIPTION"));
				$tab->addInfo("group",mysql_result($result,0,"FK_ID_GROUP"));
				$connectService->close();
				$tab->addInfo("photos",$this->getPhotosOfDir($tab->getInfo("root")));
            if($idMedia == 617){
               $tab->addInfo("root","http://chaines.cardans.s3-website-eu-west-1.amazonaws.com/PHOTOS/2012_11_24-FONTAINEBLEAU");
            }
				$tab->addInfo("type","scan");
				return $tab;
			}
		}
	}

	/* Renvoie les photos perso d'un utilisateur */
	function getPhotosOfUser($idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$tab = new Data();
		$r = mysql_query("select login from Utilisateur where id_user = " . $idUser);

		if(mysql_num_rows($r) == 0){
			return $tab;
		}
		$name =  mysql_result($r,0,0);
		$connectService->close();
		if(!is_dir("../v2.0/espace/" . $name . "/photos/sd")){
			return $tab;
		}

		$tab->addInfo("root","../v2.0/espace/" . $name . "/photos");
		$tab->addInfo("photos",$this->getPhotosOfDir($tab->getInfo("root")));
		$tab->addInfo("type","scan");

		return $tab;
	}


	/* Liste les photos d'un repertoire */
	function getPhotosOfDir($dirName){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$r = mysql_query("select PATH_PHOTO,ID_PHOTO_TAG from PhotoTag where PATH_PHOTO like '" . str_replace($this->directory,"",$dirName) . "%'");
		$tags = array();
		for($i = 0 ; $i < mysql_num_rows($r) ; $i++){
			$path = mysql_result($r,$i,"PATH_PHOTO");
			$tags[substr($path,strrpos($path,"/")+1)] = mysql_result($r,$i,"ID_PHOTO_TAG");
		}

		$tab = array();
		$dir = opendir($dirName . "/sd");
		$i = 0;
		/* Parcourt les photos */
		while( ($photo = readdir($dir))!=false){
			if($photo != "." && $photo!=".." && strstr($photo,".jpg") == ".jpg"){
				$data = new Data();
				$data->addInfo("name",str_replace(".jpg","",$photo));
				$size = getimagesize($dirName . "/sd/" . $photo);
				$data->addInfo("width",$size[0]);
            $data->addInfo("height",$size[1]);
				if(isset($tags[str_replace(".jpg","",$photo)]) == true){
					$data->addInfo("tag",$tags[str_replace(".jpg","",$photo)]);
				}
				else{
					$data->addInfo("tag",-1);
				}
				$tab[++$i] = $data;
			}
		}
		/* Trie les photos par nom */
		function abc($a,$b){return strnatcasecmp($a->getInfo("name"),$b->getInfo("name"));}
		usort($tab,"abc");

		$connectService->close();
		return $tab;
	}

	/* Renvoie les informations d'une photo */
	/* @return : renvoie null s'il n'y  a pas de tag */
	function getTagInfos($idPhotoTag){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$photoTag = new Data();

		$query = "select DAYOFMONTH(DATE_MEDIA) as day, MONTH(DATE_MEDIA) as month, YEAR (DATE_MEDIA) as year,TYPE_PHOTO,COMMENTAIRE "
				. " from PhotoTag left join media on FK_ID_PHOTOS = ID_MEDIA where ID_PHOTO_TAG = " . $idPhotoTag;
		$result = mysql_query($query);

		if(mysql_num_rows($result)!=1){return null;}
		if(mysql_result($result,0,"day")!=null){
			$photoTag->addInfo("date",mysql_result($result,0,"day") . " " . getMonth(mysql_result($result,0,"month")) . " " . mysql_result($result,0,"year"));
		}
		else{
			$photoTag->addInfo("date","");
		}
		$photoTag->addInfo("type",mysql_result($result,0,"TYPE_PHOTO"));
		$photoTag->addInfo("commentaire",mysql_result($result,0,"COMMENTAIRE"));

		$result = mysql_query("select * from PhotoTag_a_Personne, Utilisateur where FK_ID_PHOTO_TAG = " . $idPhotoTag . " and FK_ID_UTILISATEUR = id_user");
		$personnes = "";
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$personnes .= mysql_result($result,$i,"nom") . " " . mysql_result($result,$i,"prenom") . ", ";
		}
		$photoTag->addInfo("personnes",$personnes);

		$result = mysql_query("select * from PhotoTag_a_Moto, Motos, Marques where IdMarque = marque and FK_ID_PHOTO_TAG = " . $idPhotoTag . " and FK_ID_MOTO = IdMoto");
		$motos = "";
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$motos = utf8_encode(mysql_result($result,$i,"nommarque") . " " . mysql_result($result,$i,"motocmcube") . " " . mysql_result($result,$i,"nommoto")) . ", ";
			}
		$photoTag->addInfo("motos",$motos);

		$result = mysql_query("select * from PhotoTag_a_MotCle, MotCle where FK_ID_MOT_CLE = ID_MOT_CLE and FK_ID_PHOTO_TAG = " . $idPhotoTag);
		$motsCles = "";
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$motsCles .= mysql_result($result,$i,"LIB_MOT_CLE") . ", ";
		}
		$photoTag->addInfo("motsCles",$motsCles);

		$lieux = "";
		$result = mysql_query("select * from PhotoTag_a_Lieu, Lieu where FK_ID_LIEU = ID_LIEU and FK_ID_PHOTO_TAG = " . $idPhotoTag);
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$lieux .= mysql_result($result,$i,"LIB_LIEU") . ", ";
		}
		$photoTag->addInfo("lieux",$lieux);

		mysql_close();
		return $photoTag;
	}

   function getDemandesPhotosHD(){
      $connectService = new ConnectionBDD();
		$connect = $connectService->connect();
      mysql_query("update DemandePhoto set STATUS = 1,DATE_DERNIERE_MAJ = CURRENT_TIMESTAMP where STATUS = 0");
      $query = "select d.ID_PHOTO_TO_DOWNLOAD,d.PATH_PHOTO, u.login,u.ID_USER from DemandePhoto d "
         . " join Utilisateur u on d.ID_USER = u.id_user and d.STATUS = 1";
      $result = mysql_query($query);
      $users = array();
      $mapUsers = array();
      while(($row = mysql_fetch_row($result))!=null){
         if($mapUsers[$row[2]] == null){
            $user = new User($row[3],$row[2],null,null);
            $users[sizeof($users)] = $user;
            $mapUsers[$row[2]] = $user;
         }
         $mapUsers[$row[2]]->addDemande(new Demande($row[0],$row[1]));
      }
		mysql_close();
      return $users;
   }

   function pushDemandePhotoHD($path,$idUser){
      $connectService = new ConnectionBDD();
	  $connect = $connectService->connect();
      // On verifie si la demande existe deja
      $q = sprintf("select ID_PHOTO_TO_DOWNLOAD from DemandePhoto where PATH_PHOTO ='%s' and ID_USER = %d",$path,$idUser);
      if(mysql_num_rows(mysql_query($q)) > 0){
         throw new Exception("Demande deja effectuee");
      }
      mysql_query(sprintf("insert into DemandePhoto (PATH_PHOTO,ID_USER) values ('%s',%d)",$path,$idUser));
  		mysql_close();
   }

   	/* Mets a jour les demandes utilisateurs */
	function updateStatusDemandes($updates,$idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();

      $rapports = array(0,0,0);
		foreach($updates as $u){
			$rapports[$u->status]++;
			$status = 2;
			if($u->status!=0){
				$status=$u->status * -1;
			}
			$q = "update DemandePhoto set STATUS = " . $status . ", DATE_DERNIERE_MAJ = CURRENT_TIMESTAMP where ID_PHOTO_TO_DOWNLOAD = " . $u->id;
			//mysql_query($q);
		}
		// Construction du rapport
		$nb = sizeof($updates);
		$corps = "Bonjour.<br/><br/>";
		if($nb == $rapports[0]){
			$corps .= "Toutes les demandes de photos HD (" . $nb . ") ont été traitées avec succès.";
		}
		else{
			$corps .= sprintf("%d demandes de photos HD ont été traitées : <ul><li>%d avec succès.</li><li>%d avec des erreurs.</li></ul>",
				$nb,$rapports[0],$rapports[1] + $rapports[2]);
		}
      $corps.="<br/>Vos photos sont disponibles sur le site de <a href=\"http://vbaranzini.free.fr\"/>Chaînes & Cardans</a>.<br/><br/>Bonne journée";
      // Recuperation du mail utiliasteur
      $result = mysql_query("select email from Utilisateur where id_user = " . $idUser . " and email is not null");
      if(mysql_num_rows($result) == 0){
         mysql_close();
         throw new Exception("Mail absent, impossible de notifier par mail.");
      }
      $email = mysql_result($result,0,0);

      // Envoie du mail
      $mail = new MailService();
      $mail->send($email,"[C&C] : Photos envoyees dans votre espace",$corps);
		mysql_close();
   }

   /* Renvoie les demandes d'un utilisateur */
   function getDemandesOfUser($idUser){
      $connectService = new ConnectionBDD();
	  $connect = $connectService->connect();
      $userResult = mysql_query("select login from Utilisateur where id_user = " . $idUser);
      if(mysql_num_rows($userResult) == 0){
         mysql_close();
         throw new Exception("Utilisateur inconnu");
      }
      $userPath = "../v2.0/espace/" . mysql_result($userResult,0,0) . "/uploaded/";
      $q = "select ID_PHOTO_TO_DOWNLOAD,PATH_PHOTO,DATE_DEMANDE, STATUS from DemandePhoto "
         . " where ID_USER = " . $idUser . " order by STATUS,DATE_DEMANDE";
	  $result = mysql_query($q);
	  $demandes = array();
	  while(($row = mysql_fetch_row($result))!=null){
	  	$d = new Demande($row[0],$row[1]);
      $path = $userPath . "/" . substr($row[1],strrpos($row[1],"/")+1);
      $d->setPathHD($path);
	  	$d->setStatus($row[3]);
	  	$demandes[sizeof($demandes)] = $d;
	  }
      mysql_close();
      return $demandes;
   }
   
   /* Recupere certaines demandes d'un utilisateur et vierifie que ce sont bien les siens */
	function getDemandesOfUserByIds($idTags, $idUser){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$userResult = mysql_query("select login from Utilisateur where id_user = " . $idUser);
      if(mysql_num_rows($userResult) == 0){
         mysql_close();
         throw new Exception("Utilisateur inconnu");
      }
      $userPath = "../v2.0/espace/" . mysql_result($userResult,0,0) . "/uploaded/";
		$tagsStr = implode(",",$idTags);
		$q = "select PATH_PHOTO from DemandePhoto where ID_PHOTO_TO_DOWNLOAD is in (" . $tagsStr . ") and ID_USER = " . $idUser;
		$result = mysql_query($q);
		if(mysql_num_rows($result) != sizeof($idTags)){
			mysql_close();
			throw new Exception("Tous les tags n'appartiennent pas a l'utilisateur");
		}
		$tags = array();
		while(($row = mysql_fetch_row($result)) != null){
			$tags[sizeof($tags)] = $userPath . "/" . substr($row[0],strrpos($row[0],"/")+1);
		}
		mysql_close();
		return $tags;		
	}

   /* Supprime les demandes par id */
   function deleteDemandes($idTags){
      $connectService = new ConnectionBDD();
		$connect = $connectService->connect();
      $tagsStr = implode(",",$idTags);
      mysql_query("delete from DemandePhoto where ID_PHOTO_TO_DOWNLOAD is in (" . $tagsStr . ")");
      mysql_close();
   }
}


?>
