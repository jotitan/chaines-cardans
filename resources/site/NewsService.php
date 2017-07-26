<?


/* Service de gestion des news */
/* Implements les methodes de NewsAction */

include("NewsDao.php");
require_once("GestionImage.php");

class NewsService{
   var $dao;

   function NewsService(){
      $this->dao = new NewsDao();
   }

   /* @case 1 */
   function getNewsById($id){
      $news = $this->dao->getNewsById($id);
      $json = sprintf("{\"id\":\"%d\",\"titre\":\"%s\",\"contenu\":\"%s\",\"urlImage\":\"%s\",\"date\":\"%s\"}",
         $news->id,$news->titre,str_replace("\"","\\\"",$news->contenu),$news->urlImage,$news->date);
      echo $json;
   }
   
   /* @case 2 */
   function saveNews($id,$titre,$contenu,$urlImage){
      // Si un fichier est copié temporairement, on le deplace
      $path = null;
      if($urlImage == "true"){
         // On deplace image temp et on donne url
         $gestion = new GestionImage();
         $path = $gestion->copyTempPhotoToEdito($_SESSION["user"]->login,$id);
      }
		$id = $this->dao->saveNews($id,$titre,$contenu,$path);
      if($id == null || !$id >=1){
			echo "{\"message\":\"0\"}";
		}
   		else{
   			echo "{\"message\":\"" . $id . "\"}";
   		}
   }
   
   /* @case 3 */
   function deleteNews($id){
		$path = $this->dao->deleteNews($id);   
		if($path!=null && $path!=''){
			$gestion = new GestionImage();
			$gestion->deleteImage($path);
		}
		echo "{\"message\":\"1\"}";
   }


}

?>
