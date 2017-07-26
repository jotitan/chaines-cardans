<?
require_once("DateHelper.php");

/* Utilisateur utilise sur le site. Contient les donnees utilisees lors de la navigation */
class User{
	var $id;
	var $login;
	var $statut;
	var $isAdmin;
   var $demandes = array();

	function User($id,$login,$statut,$isAdmin){
		$this->id = $id;
		$this->login = $login;
		$this->statut = $statut;
		$this->isAdmin = $isAdmin;
	}

   function addDemande($demande){
      $this->demandes[sizeof($this->demandes)] = $demande;
   }

   function write(){
      $jb = new JSONBuilder();
      return $jb->set("id",$this->id)->set("login",$this->login)->set("demandes",$this->demandes)->build();
   }
}

/* Map permettant de stocker des donnes cle / valeur */
class Data{
	var $tab = Array();
	
	function Data(){}
	
	function addInfo($key,$value){
		$this->tab[$key] = $value;
	}
	
	function getInfo($key){
		return $this->tab[$key];
	}
}

class DateFormat{
   var $day;
   var $month;
   var $year;

   function DateFormat($day,$month,$year){
      $this->day = $day;
      $this->month = $month;
      $this->year = $year;
   }

   function getShortMonth(){
      return DateHelper::getShortMonth($this->month);
   }

}

/* Represente une news */
class News {
	var $id;
	var $titre;
	var $contenu;
	var $urlImage;
	var $date; // type date
	
	function News($id,$titre,$contenu,$urlImage,$date){
		$this->id = $id;
		$this->titre = $titre;
		$this->contenu = $contenu;
		$this->urlImage = $urlImage;
		$this->date = $date;
	}
}

/* Represente une demande d'un utilisateur */
class Demande {
   var $id;
   var $path;
   var $pathHD;
   var $status;

   function Demande($id,$path){
      $this->id = $id;
      $this->path = $path;
   }

   function setStatus($status){
      $this->status = $status;
   }

	function setPathHD($pathHD){
		$this->pathHD = $pathHD;
	}

   function write(){
      $jb = new JSONBuilder();
      return $jb->set("id",$this->id)->set("path",$this->path)->set("status",$this->status)->build();
   }

}


?>
