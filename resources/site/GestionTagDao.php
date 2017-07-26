<?

require_once("Model.php");
require_once("ConnectionBDD.php");

class GestionTagDao{

   var $connection;

   function GestionTagDao(){
      $this->connection = new ConnectionBDD();
   }

function getGroupes(){
	$this->connection->connect();
    
	$result = mysql_query("select * from group_photos order by description_group_photo");
	$tab = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$tab[mysql_result($result,$i,"id_group_photo")] = mysql_result($result,$i,"description_group_photo");
	}
	$this->connection->close();
		
	return $tab;
}

function getPhotosOfGroupe($idGroupe){
	$this->connection->connect();
  
	$result = mysql_query("select * from media m where m.ID_MEDIA and m.FK_ID_GROUP = " . $idGroupe . " order by m.DATE_MEDIA");
	$photos = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$photos[mysql_result($result,$i,"m.ID_MEDIA")] = mysql_result($result,$i,"m.DESCRIPTION");
	}
	
	$this->connection->close();
	return $photos;
}

function getStats(){
	$this->connection->connect();

  	$tab = array();
	$idUser = $_SESSION["id_user"];
	$result = mysql_query("select sum(NB_MODIF) as modif, sum(NB_FINISH) as finish from PhotoTag_Stats where FK_ID_USER = " . $idUser . " and DATE_STAT in (select CURRENT_DATE)");
	$tab["modif"] = mysql_result($result,0,"modif");
	$tab["finish"] = mysql_result($result,0,"finish");
	
	$result = mysql_query("select sum(NB_MODIF) as modif, sum(NB_FINISH) as finish from PhotoTag_Stats where FK_ID_USER = " . $idUser);
	$tab["modifTotal"] = mysql_result($result,0,"modif");
	$tab["finishTotal"] = mysql_result($result,0,"finish");
	
	$this->connection->close();
	return $tab;
}

function getPersonnesByName($name){
   $this->connection->connect();

  $name=utf8_decode($name);
	$result = mysql_query("select * from Utilisateur where nom like '" . $name . "%' or prenom like '" . $name . "%' order by nom,prenom");
	//echo "select * from Utilisateur where nom like '" . $name . "%' or prenom like '" . $name . "%' order by nom,prenom";
	$personnes = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$personnes[mysql_result($result,$i,"id_user")] = mysql_result($result,$i,"nom") . " " . mysql_result($result,$i,"prenom");
	}
	
	$this->connection->close();
	return $personnes;
}

function getMotosByName($chaine){
	$tab = explode(" ",$chaine);
	
	// prise en compte cylindree
	$tabCyl = preg_grep("`\d{3,}`",$tab);
	$cyl = null;
	if(sizeof($tabCyl)>0){
		$t = array_keys($tabCyl);
		$tab[$t[0]] = null;
		$cyl = $tabCyl[$t[0]];
	}
	
	// on cherche les textes suceptibles d'etre des marques : taille minimum de 2
	
	$tabMarques = preg_grep("`[a-z]{2,}`i",$tab);
	$tabMarquesIndex = array_keys($tabMarques);	// stocke les index des marques du tableau d'origine

	// on cherche pour chaque mot si ca correspond a une marque
	$tabJointureMarques = array();
	$this->connection->connect();

	if(sizeof($tabMarques)>0){
		foreach($tabMarques as $key => $marque){
			$query = "select * from Marques where nommarque like '" . $marque . "%'";
			$result = mysql_query($query);
			// La marque est trouvée, on la supprime du tableau de base et on rajoute son identifiant pour la future jointure (de type OR)
			if(mysql_num_rows($result)>0){
				$tabJointureMarques[sizeof($tabJointureMarques)] = mysql_result($result,0,Idmarque);
				$tab[$key] = null;
			}
		}
	}
	
	// On cree la requete finale : ajout de la cylindree en AND, jointure sur les marques en and (or), et les noms qui restent en and
	$query = "select * from Motos, Marques where IdMarque = marque ";
	if($cyl!=null){
		$query.=" and motocmcube = " . $cyl;
	}
	
	
	// prise en compte des marques
	if(sizeof($tabJointureMarques)>0){
		$query.=" and (";
		
		$first = true;
		
		foreach($tabJointureMarques as $key => $value){
			if($first==false){
				$query.=" or ";
			}
			
			$query.=" marque=" . $value . " ";
			
			$first = false;
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
	
	$query.=" limit 0,50";

	$result = mysql_query($query);
	$motos = array();
	
	for($i=0;$i<mysql_num_rows($result);$i++){
		$motos[mysql_result($result,$i,"IdMoto")] = utf8_encode(mysql_result($result,$i,"nommarque") 
			. " " . mysql_result($result,$i,"motocmcube") . " " . mysql_result($result,$i,"nommoto"));
	}
	
	$this->connection->close();

	return $motos;
}

function getMotsClesByName($name){
	$this->connection->connect();
	$name=utf8_decode($name);
	$result = mysql_query("select ID_MOT_CLE,LIB_MOT_CLE from MotCle where LIB_MOT_CLE like '" . $name . "%' order by LIB_MOT_CLE");
	$motsCles = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$motsCles[mysql_result($result,$i,0)] = mysql_result($result,$i,1);
	}
	
	$this->connection->close();
	return $motsCles;
}

/* Gestion des lieux */ 
function getLieuxByName($name){
	$this->connection->connect();
	$name=utf8_decode($name);
	$result = mysql_query("select ID_LIEU,LIB_LIEU from Lieu where LIB_LIEU like '" . $name . "%' order by LIB_LIEU");
	$lieux = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$lieux[mysql_result($result,$i,0)] = mysql_result($result,$i,1);
	}
	
	$this->connection->close();
	return $lieux;
}

function createMotCle($value){
	$this->connection->connect();
	mysql_query("insert into MotCle values(null,'" . $value . "')");
	$id = mysql_insert_id();	
	// On analysele lieu et l'enregistre dans l'index
	$terms = $this->analyseWord($value);
	foreach($terms as $t){
      mysql_query(sprintf("insert into motcle_index (ID_MOT_CLE,LABEL) values (%d,'%s')",$id,$t));      
    }
	$this->connection->close();	
	return $id;
}

function createLieu($value){
	$this->connection->connect();
	mysql_query("insert into Lieu values(null,'" . $value . "')");
	$id = mysql_insert_id();	
	// On analyse le lieu et l'enregistre dans l'index 
	$terms = $this->analyseWord($value);
	foreach($terms as $t){
      mysql_query(sprintf("insert into lieu_index (ID_LIEU,LABEL) values (%d,'%s')",$id,$t));      
    }
	$this->connection->close();	
	return $id;
}

/* Permet de charger un tag par ID (ou de en recuperer un) */
function getPhotoTagById($idTag,$idPhotos,$path){
	$connect = connect();
	$gestionTag = new GestionTag();
	$photoTag = new PhotoTag();
	$temp = "photos/" . str_replace("/ld/","/sd/",$path);
	$path = str_replace(".jpg","",$path);
	$path = str_replace("/ld/","/",$path);
	$query = "select * from PhotoTag where ID_PHOTO_TAG = " . $idTag;
	$photoTag = $gestionTag->addInfosToDataTag($query,$photoTag,$idPhotos,$path);	
	$photoTag->path = $temp;

	return $photoTag;
}

function addInfosToDataTag($query,$photoTag,$idMedia,$path){
	$result = mysql_query($query);
	$photoTag->idMedia = $idMedia;
	$photoTag->nb = $nb;
	
	if(mysql_num_rows($result) == 0){
		mysql_query("insert into PhotoTag values(null," . $idMedia . ",'" . $path . "',null,CURRENT_TIMESTAMP,-1,0,'')");

		// on le cree et on renvoie objet gun vide
		$photoTag->id = mysql_insert_id();
	}
	else{
		// on renvoie l'objet en question
		$photoTag->id = mysql_result($result,0,"ID_PHOTO_TAG");
		$photoTag->date = mysql_result($result,0,"DATE_PHOTO");
		$photoTag->tagFinish = mysql_result($result,0,"IS_TAG_FINISH");
		$photoTag->type = mysql_result($result,0,"TYPE_PHOTO");
		$photoTag->commentaire = mysql_result($result,0,"COMMENTAIRE");
		$result = mysql_query("select FK_ID_UTILISATEUR, nom, prenom from PhotoTag_a_Personne, Utilisateur where FK_ID_PHOTO_TAG = " . $photoTag->id . " and FK_ID_UTILISATEUR = id_user");
		
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$photoTag->personnes[$i] = new LabelValue(mysql_result($result,$i,0),mysql_result($result,$i,1) . " " . mysql_result($result,$i,2));
		}
		$result = mysql_query("select IdMoto, nommarque, motocmcube, nommoto from PhotoTag_a_Moto, Motos, Marques where IdMarque = marque and FK_ID_PHOTO_TAG = " . $photoTag->id . " and FK_ID_MOTO = IdMoto");
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$photoTag->motos[$i] = new LabelValue(mysql_result($result,$i,0),
								utf8_encode(mysql_result($result,$i,1) . " " . mysql_result($result,$i,2) . " " . mysql_result($result,$i,3)));
		}
		$result = mysql_query("select ID_MOT_CLE,LIB_MOT_CLE from PhotoTag_a_MotCle, MotCle where FK_ID_MOT_CLE = ID_MOT_CLE and FK_ID_PHOTO_TAG = " . $photoTag->id);
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$photoTag->motsCles[$i] = new LabelValue(mysql_result($result,$i,0),mysql_result($result,$i,1));
		}
		$result = mysql_query("select ID_LIEU,LIB_LIEU from PhotoTag_a_Lieu, Lieu where FK_ID_LIEU = ID_LIEU and FK_ID_PHOTO_TAG = " . $photoTag->id);
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
			$photoTag->lieux[$i] = new LabelValue(mysql_result($result,$i,0),mysql_result($result,$i,1));
		}
	}
	
	return $photoTag;
}


/* Renvoie l'id d'un tag a partir de son nombre */
function getIdPhotoTag($nb,$idMedia){
	$this->connection->connect();
	$id = 0;
	$path = $this->getPathNbiemePhoto($nb,$idMedia);
	// On verifie la photo	
	if(@fopen($path,"r")==false){
		$id = -1;
	}
	else{
       $formatPath = str_replace("/sd","",str_replace("../v2.0/photos/","",str_replace(".jpg","",$path)));

    	$result=mysql_query("select ID_PHOTO_TAG from PhotoTag where FK_ID_PHOTOS = " . $idMedia . " and PATH_PHOTO = '" . $formatPath . "'");
    	if(mysql_num_rows($result) == 0){
    		$id = -1;
    	}
    	else{
    		$id = mysql_result($result,0,0);
    	}
	}
	$this->connection->close();
	return $id;
}

function getPathNbiemePhoto($nb,$idMedia){
	$result = mysql_query("select DIR_MEDIA from media m where ID_MEDIA = " . $idMedia);
	if(mysql_num_rows($result)!=1){return null;}
	
	// On cherche dans le repertoire la nb-eme photo
	$dirname = "../v2.0/photos/" . mysql_result($result,0,0) . "/sd"; 
	$tab = array_merge(array_diff(scandir($dirname),array(".","..")));


	function abc($a,$b){return strnatcasecmp($a,$b);}
	usort($tab,"abc");
	if(sizeof($tab) < $nb){return null;}
	
	return $dirname . "/" . $tab[$nb];
}

/* Renvoie la photo tag qui correspond : le chemin, les différents tags associes (dans le cas ou existe deja), sinon, un nouveau tag */
function getPhotoTag($nb, $idMedia){
	$this->connection->connect();

	$path = $this->getPathNbiemePhoto($nb,$idMedia);
	$photoTag = new PhotoTag();
	// On verifie la photo	
	if(@fopen($path,"r")==false){
		$photoTag->path = $path;
		return $photoTag;
	}
	$formatPath = str_replace("/sd","",str_replace("../v2.0/photos/","",str_replace(".jpg","",$path)));
	
	$query="select * from PhotoTag where FK_ID_PHOTOS = " . $idMedia . " and PATH_PHOTO = '" . $formatPath . "'";
	$photoTag = $this->addInfosToDataTag($query,$photoTag,$idMedia,$formatPath);
	$photoTag->path = $path;
	
	$this->connection->close();
	return $photoTag;
}

function removePersonneToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("delete from PhotoTag_a_Personne where FK_ID_PHOTO_TAG = %d and FK_ID_UTILISATEUR = %d",$idTag,$id));	
	$this->connection->close();
}

function removeMotoToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("delete from PhotoTag_a_Moto where FK_ID_PHOTO_TAG = %d and FK_ID_MOTO = %d",$idTag,$id));	
	$this->connection->close();
}

function removeMotCleToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("delete from PhotoTag_a_MotCle where FK_ID_PHOTO_TAG = %d and FK_ID_MOT_CLE = %d",$idTag,$id));	
	$this->connection->close();
}

function removeLieuToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("delete from PhotoTag_a_Lieu where FK_ID_PHOTO_TAG = %d and FK_ID_Lieu = %d",$idTag,$id));	
	$this->connection->close();
}

function addPersonneToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("insert into PhotoTag_a_Personne values(%d,%d)",$idTag,$id));	
	$this->connection->close();
}

function addMotoToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("insert into PhotoTag_a_Moto values(%d,%d)",$idTag,$id));	
	$this->connection->close();
}

function addMotCleToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("insert into PhotoTag_a_MotCle values(%d,%d)",$idTag,$id));	
	$this->connection->close();
}

function addLieuToTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("insert into PhotoTag_a_Lieu values(%d,%d)",$idTag,$id));	
	$this->connection->close();
}

function setTypeTag($idTag,$id){
	$this->connection->connect();	
	mysql_query(sprintf("update PhotoTag set TYPE_PHOTO=%d where ID_PHOTO_TAG=%d",$id,$idTag));	
	$this->connection->close();
}

function setCommentaire($idTag,$commentaire){
	$this->connection->connect();	
	mysql_query(sprintf("update PhotoTag set COMMENTAIRE='%s' where ID_PHOTO_TAG=%d",$commentaire,$idTag));	
	$this->connection->close();
}


function parseDateKeywords($keywords){
   $ret = array();
   $tabMois = array("janvier","février","mars","avril","mai","juin","juillet","aout","septembre","octobre","novembre","décembre");
   $mois = array_merge(array_intersect($keywords,$tabMois));
   if(sizeof($mois)> 0){
      for($i = 0 ; $i < sizeof($tabMois) ; $i++){
         if($tabMois[$i] == $mois[0]){
            $mois = (($i < 10)?"0":"") . ($i+1);
            break;
         }
      }
      $keywords = array_diff($keywords,$tabMois);
   }
   else{
      $mois = null;
   }
   $annees = preg_grep("/[12][0-9]{3}/",$keywords);
   $beginDate = null;
   $endDate = null;
   if(sizeof($annees) > 0){
      foreach($annees as $a){
         if(intval($a) > 1900 && intval($a) < 2300){
            // On calcule la date
            $beginDate = "'" . $a . "-";
            $endDate = "'" . $a . "-";
            if($mois!=null){
               $beginDate .= $mois . "-01'";
               $endDate .= $mois . "-31'";
            }
            else{
               $beginDate .= "01-01'";
               $endDate .= "12-31'";
            }
            break;
         }
      }
   }
   $ret[0] = $beginDate;
   $ret[1] = $endDate;
   $ret[2] = $keywords;
   return $ret;
}

/* Analyse un mot en le normalisant (lower, accent...) et en le découpant. Renvoie un tableau des termes requetables.*/
function analyseWord($str){
    $str = strtolower(htmlentities($str, ENT_NOQUOTES, 'utf-8'));
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    $str = preg_replace('#(&[^;]+;)|([ldscmntj]\')#', '', $str);
    $str = preg_replace('#([-,_])#', ' ', $str);
    $tab = array();
    $stopwords = array("de","sur","dans");
    $values = split(" ",$str);
    foreach($values as $v){
      if(strlen($v)>1 && !in_array($v,$stopwords)){	// Mot superieur a 1 lettre et pas un stopword
         $tab[sizeof($tab)] = $v;
      }
    }
    return $tab;
}

/* Parse la request pour trouver le type */
function parseTypeKeywords($keywords){
   // Mots : groupe, portrait, motos, moto, visite, paysage
   $types = array("groupe","portrait","paysage","moto","motos","visite");
   $matchTypes = array_merge(array_intersect($keywords,$types));
   $keywords = array_diff($keywords,$matchTypes);
   if(sizeof($matchTypes) > 0){
      $matchTypes = $matchTypes[0];
      for($i = 0 ; $i < sizeof($types) ; $i++){
         if($matchTypes == $types[$i]){
            $matchTypes = $i;
            break;
         }
      }
   }
   else{
      $matchTypes = null;
   }
   return array($matchTypes,$keywords);
}


/* Moteur de recherche de photo */
/* On ne fait pas de like, mais que du = (plus puissant) */
/* On recherche tous les mots cles dans les 4 tables en union*/
/* On renvoie l'id et le libelle, ainsi que le type */
/* @from : pagination, nombre de depart. Si = 0, on renvoie le nombre total de resultats */
/* @size : nombre d'elements a renvoyer */
function search($request,$fromLimit,$size){
   // On separe les mots et on vire les stopwords
   $keywords = $this->analyseWord($request);
   
   $dates = $this->parseDateKeywords($keywords);
   $beginDate = $dates[0];
   $endDate = $dates[1];
   $keywords = $dates[2];

   $types = $this->parseTypeKeywords($keywords);
   $type = $types[0];
   $keywords = $types[1];
   $tempTypes = array();

    if($beginDate == null && !isset($type) && sizeof($keywords) == 0){return null;}
         	
    $this->connection->connect();
          	
	$types = array();
         $joinTypes = array("K"=>"PhotoTag_a_MotCle","M"=>"PhotoTag_a_Moto","L"=>"PhotoTag_a_Lieu","P"=>"PhotoTag_a_Personne");
         $mappingTypes = array("K"=>"FK_ID_MOT_CLE","M"=>"FK_ID_MOTO","L"=>"FK_ID_LIEU","P"=>"FK_ID_UTILISATEUR");
         $from = " from PhotoTag pt join media m on m.ID_MEDIA = pt.FK_ID_PHOTOS ";
         $where =  "where pt.ID_PHOTO_TAG is not null ";

         if($beginDate!=null){
            $where.= " and m.DATE_MEDIA >=" . $beginDate . " and m.DATE_MEDIA <= " . $endDate;
         }
         if($type!=null){
            $where.= " and pt.TYPE_PHOTO = " . $type;
         }         	
         	
         	
	/* Recherche des mots cles */
   
   if(sizeof($keywords) > 0){
      $listKeywords = "('" . implode("','",$keywords) . "')";
   /*    
   	Requete sur les tables d'index motcle_index et lieu_index. On calcule le nombre de mot de listkeywords qui correspondent a une meme expression. 
   	Plus un mot cle (découpé en plusieurs mots) matchs un nombre importants de mot recherché, plus le score est important*/
   	/* Creer un index moto */
	  $query= 	
	  	" select * from ("
	  . " select \"K\" as type, ki.ID_MOT_CLE as id, count(ID_INDEX) as nb,mc.LIB_MOT_CLE as label "
     . " from motcle_index ki join MotCle mc on ki.ID_MOT_CLE = mc.ID_MOT_CLE "
     . " where lower(ki.LABEL) in " . $listKeywords . " group by ki.ID_MOT_CLE,mc.LIB_MOT_CLE "
      . " union "
   	  . " select \"L\" as type, li.ID_LIEU as id, count(li.ID_INDEX) as nb,"
        . " lower(l.LIB_LIEU) as label from lieu_index li join Lieu l on l.ID_LIEU=li.ID_LIEU"
        . " where lower(li.LABEL) in " . $listKeywords . " group by li.ID_LIEU,l.LIB_LIEU "
   	  . " union "
        . " select \"M\" as type, idMoto as id,count(idMoto),\"\" as label from ( "
          . " select idMoto,'A' as lab from Motos m join Marques ma on m.marque = ma.idMarque where lower(nommarque) in " . $listKeywords
          . " union "
          . " select ID_MOTO as idMoto, 'B' as lab from moto_index where LABEL in " . $listKeywords
      . ") as sub group by idMoto"
      . " union "
   	. " select \"P\" as type,id,count(label) as nb,full as label from ( "
	  . "	select id_user as id, concat('B',nom) as label,concat(concat(prenom,' '),nom) as full from Utilisateur where lower(nom) in " . $listKeywords
	  . "		union "
	  .	"	select id_user as id, concat('A',nom) as label,concat(concat(prenom,' '),nom) as full from Utilisateur where lower(prenom) in " . $listKeywords
	  .	" ) as tmp group by id "
	  . ") as sub order by sub.type,sub.id";
     
     //echo $query . "\n";
      /*
cas des mots cles
Si pas de max
On recupere les chaines des mots cles. On split.
pour chaque mots, on verifie si dans d autres mots cles. si non, cas unique avec and
sinon un or entre les deux elements
map[mot cle] =-> liste elements

Pour chaque elements mots cles

Algo :
// Utilise une map avec Type => Liste des clés and or
// Plusieurs sous type quand il faut forcer le and
1) Si max, il est le seul pour le type (K,M,P ou L). On le positionne pour ce type (map pour empecher d'en ajouter)
2) Si mot cle, on ajoute a une liste pour gerer manuellement
   2a) Pour chaque mot cle, on calcule les couples a ajouter dans la boucle. Le mot cle ne doit pas etre dans un element max, on le supprime
*/


/*

Coucher de soleil kart

Coucher de soleil est un vrai mot cle, il est en AND (join)
si pls mot pour kart (ou un seul), il sont regroupes ensemble pour faire un OR dessus
pour chaque element de dataToQuery, on cree un join avec la liste des cles en OR
Chaque mot cle identifie est en AND (nouvel element)

*/

   	  $result = mysql_query($query);
   	  if($beginDate == null && !isset($type) && mysql_num_rows($result) == 0){
   		$this->connection->connect();  
   	 	return null;
   	  }

      $idKeywords = array();
      $resultsKeywords = array();   // map[key] => id. Cree un nouvel element a chaque fois

      $dataToQuery = array();    // map avec type => Liste des cles and or. Liste d'object avec type et id comme parametre (stocké dans une liste)
      $linkIdTypeKey = array();  // Map : lien entre la cle de l'element modifié de l'element et le type de donnee
      if(mysql_num_rows($result) > 0){
         for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
				// On cherche le max pour déterminer si une moto, une personne ou un lieu a été défini précisément.
            // Si max trouve, c'est cet element qui sera utilisé directement, sinon on fera un ou sur tous les elements
         		$nb = mysql_result($result,$i,2);
               $type = mysql_result($result,$i,0);
               $keyId = mysql_result($result,$i,1);
               $label = mysql_result($result,$i,3);
         		if($nb > 1){
         		   $linkIdTypeKey[$keyId] = $type;
                  
                  // Si mot cle et deja dans la liste et mots en commun, on fait un ou dessus (meme liste), sinon, append
                  if($type == "K"){
                     // Cherche un K
                     foreach($dataToQuery as $list){
                        if(!$done && $list[0][0] == "K"){
                           // Parcours les elements
                           foreach($list as $data){
                              if(sizeof(array_intersect(split(" ",$data[2]),split(" ",$label))) >0){
                                 // OR
                                 array_push($list,array($type,$keyId,$label));
                                 $done = true;
                                 break;
                              }
                           }
                        }
                     }
                     if(!$done){
                        array_push($dataToQuery,array(array($type,$keyId,$label)));
                     }
                  }else{
                     array_push($dataToQuery,array(array($type,$keyId,$label)));
                  }
                  // Sur chaque element on doit faire un AND car les deux elements sont importants. On incremente la cle
         		}else{
               // faire pour tous ensuite, mais il faut recuperer les labels
                  // Pour chaque mot cle, stocke la chaine complete
                     array_push($idKeywords,array($type,$keyId,$label));
               }
         	}
        }

        // On recupere les mots cles restants, on verifie si pas present dans les max
         if(sizeof($idKeywords) > 0){
            // Pour chaque mot cle, on verifie s'il apparait deja dans la liste max. Si non, on l'ajoute
            foreach($idKeywords as $elem){
               
               $exist = false;
               foreach($dataToQuery as $list){
                  // Compare les types
                  // Attention, si le type existe deja, il faudra faire un OU. Le rajouter dans la meme liste pour assurer le OU
                  if(!$exist && $list[0][0] == $elem[0]){
                       // Parcours les elements
                       foreach($list as $data){
                          if(sizeof(array_intersect(split(" ",$data[2]),split(" ",$elem[2]))) >0){
                             $exist = true;
                             break;
                          }
                       }
                    }else{
                        // Recherche si le mot n'est pas utilisé dans un autre type (cas du mot cle chapelle present dans chapelle la reine aussi)
                    }
                 }
                  // N'exist pas, on le rajoute. Prendre en compte le cas ou plusieurs peuvent correspondent a un mot ? Pour faire un ou dessus
                  if(!$exist){
                     array_push($dataToQuery,array($elem));
                  }
            }
         }

      // Construit la requete avec dataToQuery
      $counter = 0;
      foreach($dataToQuery as $list){
         $aliasJoin = "tag" . $list[0][0] . $counter++;
         $from.=" join " . $joinTypes[$list[0][0]] . " " . $aliasJoin . " on pt.ID_PHOTO_TAG = " . $aliasJoin . ".FK_ID_PHOTO_TAG ";
         $where .=" and (";
         $pos = 0;
         foreach($list as $e){
            if($pos++ > 0){
               $where.=" or ";
            }
            $where.=" " . $aliasJoin . "." . $mappingTypes[$e[0]] . " = " . $e[1] . " ";
         }
         $where .= ") ";
      }
   }

          $select = "select m.DIR_MEDIA, pt.PATH_PHOTO, pt.ID_PHOTO_TAG ";
            $limit = "";
            if($size!=null && $fromLimit!=null){
               $limit=" limit " . $fromLimit . ", " .$size;
            }
         $nb = null;
         
         if($fromLimit == 0){
            $result = mysql_query("select count(*) " .$from . $where);
            $nb = mysql_result($result,0,0);
         }

          $query = $select . $from . $where . $limit;
          //echo $query;
         $result = mysql_query($query);

         $tab = array();
        	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
        		$p = new Data();
        		$p->addInfo("tag",mysql_result($result,$i,2));
        		$n = mysql_result($result,$i,1);
        		$p->addInfo("root","../v2.0/photos/" . mysql_result($result,$i,0));
            $p->addInfo("name",substr($n,strrpos($n,'/')));
        		$tab[$i] = $p;
        	}
         $ret = array($nb,$tab);
          $this->connection->close();
         return $ret;

}

function getInfoMedia($idMedia){
	$this->connection->connect();
	
	$result = mysql_query("select DIR_MEDIA from media m where ID_MEDIA = " . $idMedia);
	if(mysql_num_rows($result)!=1){return null;}
	
	// On cherche dans le repertoire la nb-eme photo
	$dirname = "../v2.0/photos/" . mysql_result($result,0,0) . "/sd"; 
	$nb = sizeof(array_merge(array_diff(scandir($dirname),array(".",".."))));
	// Recherche du nombre de photo taggee
	$query = "select count(distinct p.ID_PHOTO_TAG) "
		. " from PhotoTag p "
		. " left join PhotoTag_a_Lieu pl on pl.FK_ID_PHOTO_TAG = p.ID_PHOTO_TAG "
		. " left join PhotoTag_a_Personne pp on pp.FK_ID_PHOTO_TAG = p.ID_PHOTO_TAG "
		. " left join PhotoTag_a_Moto pm on pm.FK_ID_PHOTO_TAG = p.ID_PHOTO_TAG "
		. " left join PhotoTag_a_MotCle pmo on pmo.FK_ID_PHOTO_TAG = p.ID_PHOTO_TAG "
		. " where FK_ID_PHOTOS = " . $idMedia
		. " and (pp.FK_ID_UTILISATEUR is not null or pl.FK_ID_LIEU is not null or pm.FK_ID_MOTO is not null or pmo.FK_ID_MOT_CLE is not null)";
	$result = mysql_query($query);
	$nbTags = mysql_result($result,0,0);

	$this->connection->close();
	return array($nb,$nbTags);
}



/* Duplique les informations d'une photo sur une autre */
function duplicateTags($idFrom,$idTo){
	$this->connection->connect();
	mysql_query("delete from PhotoTag_a_MotCle where FK_ID_PHOTO_TAG = " . $idTo);
	mysql_query("delete from PhotoTag_a_Lieu where FK_ID_PHOTO_TAG = " . $idTo);
	mysql_query("delete from PhotoTag_a_Personne where FK_ID_PHOTO_TAG = " . $idTo);
	mysql_query("delete from PhotoTag_a_Moto where FK_ID_PHOTO_TAG = " . $idTo);
	$nb = 0;
	$result = mysql_query("select FK_ID_MOT_CLE from PhotoTag_a_MotCle where FK_ID_PHOTO_TAG = " . $idFrom);
	for($i = 0 ; $i < mysql_num_rows($result); $i++){
		mysql_query(sprintf("insert into PhotoTag_a_MotCle (FK_ID_PHOTO_TAG,FK_ID_MOT_CLE) values (%d,%d)",$idTo,mysql_result($result,$i,0)));
      $nb++;
	}	
	$result = mysql_query("select FK_ID_LIEU from PhotoTag_a_Lieu where FK_ID_PHOTO_TAG = " . $idFrom);
	for($i = 0 ; $i < mysql_num_rows($result); $i++){
		mysql_query(sprintf("insert into PhotoTag_a_Lieu (FK_ID_PHOTO_TAG,FK_ID_LIEU) values (%d,%d)",$idTo,mysql_result($result,$i,0)));
	   $nb++;
	}
	$result = mysql_query("select FK_ID_MOTO from PhotoTag_a_Moto where FK_ID_PHOTO_TAG = " . $idFrom);
	for($i = 0 ; $i < mysql_num_rows($result); $i++){
		mysql_query(sprintf("insert into PhotoTag_a_Moto (FK_ID_PHOTO_TAG,FK_ID_MOTO) values (%d,%d)",$idTo,mysql_result($result,$i,0)));
	   $nb++;
	}
	$result = mysql_query("select FK_ID_UTILISATEUR from PhotoTag_a_Personne where FK_ID_PHOTO_TAG = " . $idFrom);
	for($i = 0 ; $i < mysql_num_rows($result); $i++){
		mysql_query(sprintf("insert into PhotoTag_a_Personne (FK_ID_PHOTO_TAG,FK_ID_UTILISATEUR) values (%d,%d)",$idTo,mysql_result($result,$i,0)));
	   $nb++;
	}
   mysql_query("update PhotoTag pt1 join PhotoTag pt2 on pt1.ID_PHOTO_TAG = " . $idTo
      . " and pt2.ID_PHOTO_TAG = " . $idFrom . " set pt1.TYPE_PHOTO  = pt2.TYPE_PHOTO");
	$this->connection->close();
   return $nb;
}


}

class PhotoTag{
	var $id = null;
	var $idMedia;
	var $type=-1;
	var $tagFinish =0;
	var $commentaire;
	var $nb;
	var $date;
	var $personnes = array();
	var $motos = array();
	var $motsCles = array();
	var $lieux = array();
	
	function PhotoTag(){}
}

/* Stocke les objets de type couple id / valeur */
class LabelValue{
	var $value;
	var $label;
	
	function LabelValue($value, $label){
		$this->value = $value;
		$this->label = $label;
	}
}

?>
