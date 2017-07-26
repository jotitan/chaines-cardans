<? // On renvoie les journees

require_once("SecuriteDao.php");
require_once("Model.php");

class SecuriteService{


	/* Connecte un utilisateur */
	/* @param mdp : mot de passe hashe avec md5 */
	function login($login,$mdp){
		$dao = new SecuriteDao();
		$user = $dao->login($login,$mdp);
		echo sprintf("{\"message\":\"%s\"}",($user!=null)?"ok":"ko");
	}
	
	

}
?>
