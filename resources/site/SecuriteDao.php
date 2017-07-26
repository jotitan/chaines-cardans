<?

require_once("ConnectionBDD.php");
require_once("Model.php");

/**
* Gere la securite de l'application (login, authentification...)
*/
class SecuriteDao{
   var $actions = array();
   // Definition de toutes les methodes securisees : pour chaque ActionClass, plusieurs action securisee
   function SecuriteDao(){
      $this->actions["GestionTagAction.php"] = new SecuriteAction("GestionTagAction",true);
      $media = new SecuriteAction("MediaAction",false);
      $media->add(7)->add(8);
      $this->actions["MediaAction.php"] = $media;
   }

	function login($login,$mdp){
		$connectService = new ConnectionBDD();
		$connect = $connectService->connect();
		$result = mysql_query("select pass,statut,id_user,is_admin from Utilisateur where login='" . $login ."'");
		$user = null;		
		if(mysql_num_rows($result) != 0){
			$right_pass = mysql_result($result,0,0);
			if($right_pass==$mdp && mysql_result($result,0,1)!=0){	// bon mot de passe
				$user = new User(mysql_result($result,0,2),$login,mysql_result($result,0,1),mysql_result($result,0,3));	// On cree un objet		
				mysql_query("update Utilisateur set nb_visites = nb_visites+1,dern_visite = CURRENT_TIMESTAMP where login = '" . $login . "'");
			}
		}
		$connectService->close();	
		$_SESSION['user'] = $user;
		return $user;
	}
	
	/* Verifie que l'utilisateur est connecte */
	function isConnected(){
		return isset($_SESSION['user']);
	}
	
   /* Verifie si l'utilisateur peut acceder a cete fonctionalite */
   function isAutorized($page,$action){
      if(array_key_exists($page,$this->actions)){
         $a = $this->actions[$page];
         if(($a->all == true || $a->contains($action)) && !$this->isConnected()){
            return false;
         }
      }
      return true;
   }

   function getUser(){
      return $_SESSION['user'];
   }

	/* Verifie que l'utilisateur est admin */
	function isAdmin(){
		return isset($_SESSION['user']) && $_SESSION['user']->isAdmin == 1;
	}

	/* Deconnecte l'utilisateur */
	function deconnect(){
		unset($_SESSION['user']);
	}
}


class SecuriteAction{
   var $name;
   var $restrictedActions = array();
   var $all = false; // Toutes les pages sont securisees
   function SecuriteAction($name,$all){
      $this->name = $name;
      $this->all = $all;
   }

   function add($action){
      $this->restrictedActions[$action] = 1;
      return $this;
   }

   function contains($action){
      return array_key_exists($action,$this->restrictedActions);
   }


   function setAll(){
      $this->all = true;
   }
}

function checkSecurity($page,$action){
   $dao = new SecuriteDao();
   if(!$dao->isAutorized($page,$action)){
      header('Location: error.php');
   }
}

?>
