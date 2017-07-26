<?

require_once("ConnectionBDD.php");

class GestionChatDao{

   var $connection;

   function GestionChatDao(){
      $this->connection = new ConnectionBDD();
   }

/** Renvoie les messages postes.
 * @param nbMessages : nombre de message a recuperer si aucun id n'est specifie
 */
function getMessages($id,$nbMessages){
	$this->connection->connect();
    $query = "select ID_MESSAGE,USER,MESSAGE,DATE_FORMAT(DATE,'%H:%i') from chat_message "
    . (($id!=null && $id!='' && $id!='null')?" where ID_MESSAGE >'" . $id . "' ":"")
    . " order by DATE " . (($id == null || $id == 'null' || $id == '')?" desc limit 0,"

    . (($nbMessages == null || $nbMessages == 'null')?"1":$nbMessages) :"");
	$result = mysql_query($query);
	$messages = array();
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$messages[$i] = new Message(mysql_result($result,$i,0),mysql_result($result,$i,1),mysql_result($result,$i,2),mysql_result($result,$i,3));
	}
	$this->connection->close();
		
	return $messages;
}

/* Remet a jour la date de derniere activite du chatteur */
function updateStatus($id){
   $this->connection->connect();
   mysql_query("update chat_user set IS_ONLINE = 1, LAST_ACTIVITY = CURRENT_TIMESTAMP where ID_USER = " . $id);
   $this->connection->close();
}

/* Supprime les utilisateurs inactifs */
function deleteInactiveUsers(){
   $this->connection->connect();
   // On supprime les utilisateurs qui ne sont pas connectes depuis 5 min.
   mysql_query("delete from chat_user where LAST_ACTIVITY < DATE_SUB(CURRENT_TIMESTAMP ,INTERVAL 1800 SECOND)");
   // On cache les autres
   mysql_query("update chat_user set IS_ONLINE = 0 where LAST_ACTIVITY < DATE_SUB(CURRENT_TIMESTAMP ,INTERVAL 30 SECOND)");
   $this->connection->close();
}

function logoutChat($idUser){
   $this->connection->connect();
   mysql_query("delete from chat_user where ID_USER = " . $idUser);
   $this->connection->close();
}

function getUsers(){
	$this->connection->connect();
   $result = mysql_query("select ID_USER,USER from chat_user where IS_ONLINE = 1");
	for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
		$users[$i] = new UserChat(mysql_result($result,$i,0),mysql_result($result,$i,1));
	}
	$this->connection->close();
		
	return $users;
}

/**
* @param idUser : identifiant de l'utilisateur (permet la recherche de message par utilisateur
*/
function sendMessage($message,$user,$idUser){
	$this->connection->connect();
	mysql_query(sprintf("insert into chat_message (USER,ID_USER,MESSAGE,DATE) values('%s',%d,'%s',CURRENT_TIMESTAMP)",$user,$idUser,$message));
	$id = mysql_insert_id();
	$this->connection->close();
	return $id;
}

/* Retourne les informations sur un utilisateur : nom, prenom si utilisateur de cc, rien sinon */
function getUserInfo($login){
	$this->connection->connect();
	$result = mysql_query("select ID_USER from chat_user where USER = '" . $login . "'");
	if(mysql_num_rows($result) == 0){
		$this->connection->close();
		throw new Exception("Ce login n'existe pas");
	}
	$id = mysql_result($result,0,0);
	$result = mysql_query("select prenom,nom from Utilisateur u join chat_user c on c.ID_UTILISATEUR = u.id_user where c.USER = '" . $login . "'");
   $user[0] = null;
   if(mysql_num_rows($result)>0){
      $user = array();
      $user[0] = mysql_result($result,0,0) . " " . mysql_result($result,0,1);      
	}
	// On recupere la date de dernier message
	  $result = mysql_query("select DATE_FORMAT(DATE,'%d/%c/%Y %H:%i') from chat_message where ID_USER = " . $id . " order by DATE desc limit 0,1");
	  if(mysql_num_rows($result)>0){
	  	$user[1] = mysql_result($result,0,0);
	  }      
	$this->connection->close();
   return $user;
}

/* L'utilisateur peut se connecter, ou se reconnecter */
/* @param idUtilisateur : idUtilisateur c&c */
function logUserOnChat($login,$idUser,$idUtilisateur){
   $this->connection->connect();
   /* On verifie si le login existe */
   $result = mysql_query("select ID_USER from chat_user where lower(USER) = '" . strtolower($login) . "'");
   if(mysql_num_rows($result)>0){
      if($idUser == null || $idUser != mysql_result($result,0,0)){
         $this->connection->close();
         throw new Exception("Ce login existe deja");
      }
      else{
         $this->connection->close();
         $this->updateStatus($idUser);
         return $idUser;
      }
   }
   // on ajoute l'id de l'utilisateur s'il est connecte
   mysql_query(sprintf("insert into chat_user (USER,ID_UTILISATEUR) values ('%s',%s)",$login,($idUtilisateur!=null)?"'".$idUtilisateur."'":"null"));
   $id = mysql_insert_id();
   $this->connection->close();
	return $id;
}

function changeNickname($login,$idUser){
   $this->connection->connect();
   $result = mysql_query("select ID_USER from chat_user where lower(USER) = '" . strtolower($login) . "'");
   if(mysql_num_rows($result)>0){
      if($idUser == null || $idUser != mysql_result($result,0,0)){
         $this->connection->close();
         throw new Exception("Ce login existe deja");
      }
    }
   mysql_query("update chat_user set USER = '" . $login . "' where ID_USER = " . $idUser);
    $this->connection->close();
}

}

class Userchat{
   var $id;
   var $login;

   function Userchat($id,$login){
      $this->id = $id;
      $this->login = $login;
   }
}

class Message{
   var $id;
   var $user;
	var $message;
   var $date;

	function Message($id,$user,$message,$date){
      $this->id = $id;
      $this->user = $user;
		$this->message = $message;
      $this->date = $date;
	}
}

?>
