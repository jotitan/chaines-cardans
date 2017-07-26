<?


/* Service de gestion des tags des photos */
/* Implements les methodes de GestionTagAction */

include("GestionChatDao.php");
require_once("Model.php");
class GestionChatService{
   var $dao;

   function GestionChatService(){
      $this->dao = new GestionChatDao();
   }

  /* Renvoie la liste des messages a partir d'un certain id (le plus grand est le plus recent) */
  /* @Case 1 */
  function getMessages($lastId,$nbMessages){
  	$this->dao->updateStatus($_SESSION['id_chat']);
  	$messages = $this->dao->getMessages($lastId,$nbMessages);
    $json = "{\"messages\":[";	
  	$pos = 0;
    foreach($messages as $message){
  		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"user\":\"%s\",\"message\":\"%s\",\"date\":\"%s\"}",$message->id,$message->user,utf8_decode($message->message),$message->date);
  	}
  	$json.="]}";
    echo $json;
  }


   /* Renvoie la liste des utilisateurs connectes */
   /* @case 2 */
   function getUsers(){
	  $this->dao->updateStatus($_SESSION['id_chat']);	// On met a jour l'utilisateur courant
      $this->dao->deleteInactiveUsers();  // On supprime les anciens utilisateurs
       $users = $this->dao->getUsers();

        $json = "{\"users\":[";
         if($users != null && sizeof($users) > 0){
            $pos = 0;
            foreach($users as $user){
          		$json.=(($pos++>0)?",":"") . sprintf("{\"id\":\"%d\",\"user\":\"%s\"}",$user->id,$user->login);
          	}
         }
      	$json.="]}";
        echo $json;
   }
   
   /* Envoie un message a tout le monde */
   /* @case 3 */
   /* @param user : utilisateur qui envoie le message */
   function sendMessage($message,$user,$idUser){
       $idMessage = $this->dao->sendMessage($message,$user,$idUser);
		if($idMessage == null || !$idMessage>0){
			echo "{\"error\":\"Erreur d'envoie du message\"}";
		}
		else{
			echo "{\"id\":\"" . $idMessage . "\"}";
		}
   }
   /* Connecte l'utilisateur au chat */
   /* @case 4 */
   /* @param login : login de l'utilisateur. On verifie qu'il n'est pas present avant de l'ajouter */
    function logUserOnChat($login){
      $this->dao->deleteInactiveUsers();  // On supprime les anciens utilisateurs
      try{
      	 $id = $this->dao->logUserOnChat($login,$_SESSION['id_chat'],($_SESSION['user']!=null)?$_SESSION['user']->id:null);
		 $_SESSION['user_chat'] = $login;
         $_SESSION['id_chat'] = $id;
         echo "{\"id\":\"" . $id . "\"}";
      }catch(Exception $e){
         // Erreur, on renvoie le message
         echo "{\"error\":\"" . $e->getMessage() . "\"}";
      }
    }

   /* Deconnecte l'utilisateur */
   /* @case 5 */
   function logoutChat(){
   	$this->dao->logoutChat($_SESSION['id_chat']);
      unset($_SESSION['id_chat']);
      unset($_SESSION['user_chat']);
   	echo "{\"message\":\"ok\"}";
   }

   /* @case 6 */
   /* Change le login de l'utilisateur */
   function changeNickname($login){
      try{
         $this->dao->changeNickname($login,$_SESSION['id_chat']);
         $_SESSION['user_chat'] = $login;
         echo "{\"login\":\"" . $login . "\"}";
      }catch(Exception $e){
            echo "{\"error\":\"" . $e->getMessage() . "\"}";
      }
   }

   /* @case 7 */
   /* Renvoie les infos d'un utilisateur */
   function getUserInfo($login){
      try{
		  $user = $this->dao->getUserInfo($login);	
		  $info = "Pas d'information sur cet utilisateur. ";
		  $message = "";
		  if($user[0] != null){
		  	$info = "Cet utilisateur est " . $user[0] . ". ";
		  }
		  if($user[1] != null){
		  	$message = "Dernier message : " . $user[1];
		  }
		  echo "{\"info\":\"" . $info . "\",\"lastMessage\":\"" . $message . "\"}";
      }catch(Exception $e){
      	echo "{\"info\":\"" . $e->getMessage() . "\",\"lastMessage\":\"\"}";
      }
   }
}

?>
