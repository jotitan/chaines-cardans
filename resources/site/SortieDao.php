<?

require_once("ConnectionBDD.php");
require_once("Model.php");
require_once("MediaDao.php");

class SortieDao{
var $directory = "../v2.0/";	
	

function getSortiesJournees(){
	return $this->getSortiesByType(0);
}

function getSortiesPentecote(){
	return $this->getSortiesByType(1);
}

/* Renvoie la liste des sorties d'une journee */
function getSortiesByType($type){
	$connectService = new ConnectionBDD();
	$connect = $connectService->connect();
	$query =  " SELECT id_sortie,id_name,title_sortie,positionX,positionY,YEAR(date_sortie) as annee FROM sortie where type_sortie = " . $type . " order by date_sortie ";
	$result = mysql_query($query);
	$sorties = array();
	for($i = 0 ; $i < mysql_num_rows($result);$i++){
		$sortie = new Data();
		$sortie->addInfo("id",mysql_result($result,$i,"id_sortie"));
		$sortie->addInfo("code",mysql_result($result,$i,"id_name"));
		$sortie->addInfo("titre",utf8_encode(mysql_result($result,$i,"title_sortie")));
		$sortie->addInfo("posX",mysql_result($result,$i,"positionX"));
		$sortie->addInfo("posY",mysql_result($result,$i,"positionY"));
		$sortie->addInfo("annee",mysql_result($result,$i,"annee"));
		$sorties[$i] = $sortie;
	}
	$connectService->close();	
	return $sorties;
}

function getSortieById($id){
	$query = "select * from sortie where id_sortie=" . $id;
	return $this->getInfosSortie($query);
}

function getSortie($nomSortie){
	$query = "select * from sortie where id_name='" . $nomSortie. "'";
	return $this->getInfosSortie($query);
}

/* Renvoie les informations d'une sortie : titre, description, personnes presentes... */
function getInfosSortie($query){
	$connectService = new ConnectionBDD();
	$connect = $connectService->connect();
	//$query = "select * from sortie where id_name='" . $nomSortie. "'";
	$result = mysql_query($query);
	
	if(mysql_num_rows($result)==0)
		return null;
	
	$id_sortie = mysql_result($result,0,"id_sortie");
	
	$sortie = new Data();
	$sortie->addInfo("title",utf8_encode(mysql_result($result,0,"title_sortie")));
	$sortie->addInfo("desc",utf8_encode(mysql_result($result,0,"desc_sortie")));
	$sortie->addInfo("type",mysql_result($result,0,"type_sortie"));
	$sortie->addInfo("group",mysql_result($result,0,"id_group"));
	$id_media = mysql_result($result,0,"id_media");
	$sortie->addInfo("media",$id_media);

	// On recupere les personnes presentes
	$query = "select * from sortie_a_personne s, Utilisateur u where s.id_personne = u.id_user and s.id_sortie='" . $id_sortie . "'";
	$result = mysql_query($query);
	$personnes = array();
	for($i = 0;$i<mysql_num_rows($result);$i++){
		if(!isset($_SESSION['user']) && $_SESSION['user']!="deconnect")
			$id = null;
		else
			$id = mysql_result($result,$i,"u.id_user");
		$personne = new Data();
		$personne->addInfo("nom",mysql_result($result,$i,"u.nom"));
		$personne->addInfo("prenom",mysql_result($result,$i,"u.prenom"));
		if(mysql_result($result,$i,"u.path_photo")!=null){
			$personne->addInfo("photo",$this->directory . mysql_result($result,$i,"u.path_photo"));
		}
		else{
			$personne->addInfo("photo",$this->directory . "photos/trombino/default");
		}
		$personne->addInfo("id",$id);
		$personnes[$i] = $personne;
	}
	$sortie->addInfo("personnes",$personnes);
	$connectService->close();	

	// on recupere la version resume des photos
	$gestion = new MediaDao();
	$images = $gestion->getPhotosOfMedia($id_media);
	$sortie->addInfo("images",$images);
	
	return $sortie;
}


}

?>
