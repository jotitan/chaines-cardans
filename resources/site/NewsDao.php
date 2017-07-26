<?

require_once("ConnectionBDD.php");
require_once("Model.php");

/**
* Gere les news
*/
class NewsDao{
	
	var $connection;
	
	function NewsDao(){
		$this->connection = new ConnectionBDD();
	}
	
	/* Recupere les news, par ordre decroissant */
	/* @param nb : nombre de news a recuperer */
	function getNews($nb){
		$this->connection->connect();
		
		$result = mysql_query("select TITRE,CONTENU,URL_IMAGE,date_format(DATE,'%d'),date_format(DATE,'%c'),date_format(DATE,'%Y') from news order by DATE desc limit 0," . $nb);
		$news = array();
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){
         $date = new DateFormat(mysql_result($result,$i,3),mysql_result($result,$i,4),mysql_result($result,$i,5));
			$news[$i] = new News(null,mysql_result($result,$i,0),mysql_result($result,$i,1),mysql_result($result,$i,2),$date);
		}
		$this->connection->close();
		return $news;
	}
	
   function getNewsById($id){
      $this->connection->connect();

		$result = mysql_query("select TITRE,CONTENU,URL_IMAGE,date_format(DATE,'%d/%m/%Y') from news where ID_NEWS = " . $id);
		$news = new News($id,mysql_result($result,$i,0),mysql_result($result,$i,1),mysql_result($result,$i,2),mysql_result($result,$i,3));

      $this->connection->close();
		return $news;
   }
   
	/* Sauve une news */   
    function saveNews($id,$titre,$contenu,$urlImage){
    	$this->connection->connect();
    	if($id == null || $id == '' || $id == 'null'){
    		mysql_query(sprintf("insert into news (TITRE,CONTENU,URL_IMAGE) values ('%s','%s','%s')",$titre,$contenu,$urlImage));
    		$id = mysql_insert_id();
    	}
    	else{
         if($urlImage == null){
    		   mysql_query(sprintf("update news set contenu = '%s',titre = '%s' where ID_NEWS = %d",$contenu,$titre,$id));
         }
         else{
            mysql_query(sprintf("update news set contenu = '%s',titre = '%s',url_image ='%s' where ID_NEWS = %d",$contenu,$titre,$urlImage,$id));
         }
    	}
    	
    	$this->connection->close();
    	return $id;
    }

	/* Renvoie la liste des news */
	function getListNews(){
		$this->connection->connect();
		
		$result = mysql_query("select ID_NEWS,TITRE,date_format(DATE,'%d/%m/%Y') from news order by DATE desc");
		$news = array();
		for($i = 0 ; $i < mysql_num_rows($result) ; $i++){         
			$news[$i] = new News(mysql_result($result,$i,0),mysql_result($result,$i,1),null,null,mysql_result($result,$i,2));
		}
		$this->connection->close();
		return $news;
	}
	
   function deleteNews($id){
   	$this->connection->connect();
	$result = mysql_query("select URL_IMAGE from news where ID_NEWS = " . $id);
	$path = null;
	if(mysql_num_rows($result) > 0){	
	   	$path = mysql_result($result,0,0);
	}
	mysql_query("delete from news where ID_NEWS = " . $id);
   	$this->connection->close();   	
   	return $path;
   }
}


?>
