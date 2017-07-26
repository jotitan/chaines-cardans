<?
 /* Gestion des photos envoyees */
 class GestionImage{
    var $LITTLE_HEIGHT_ = 100;
    var $BIG_HEIGHT = 600;
    var $directory = "/v2.0/espace/";
	
    /* Enregistre temporairement une photo pour l'utilisateur*/
    function saveTempPhoto($file,$user,$resizeH,$resizeW){
      // On verifie le poids de la photo
      if($file['type']!="image/jpeg"){
        throw new Exception("Seul le format jpeg est accepté"); 
      }
      if($file['size']>5100000){
        throw new Exception("Le poids de l'image est limité à 500Ko"); 
      }
      $tempPath = "temp/temp_" . $user . ".jpg";
      
      if($resizeH!=''){
        $size = getImageSize($file['tmp_name']);
      $largeur = $resizeW;
      if($largeur == ''){
         $largeur = ($resizeH / $size[1]) * $size[0];
      }
		$img = imagecreatetruecolor($largeur, $resizeH);
		$source = imagecreatefromjpeg($file['tmp_name']);
		imagecopyresampled($img, $source, 0, 0, 0, 0, $largeur, $resizeH, $size[0],$size[1]);
		imagejpeg($img,$tempPath);
		}
	else{
		move_uploaded_file($file["tmp_name"],$tempPath);
	}
      return $tempPath;
    }

  	/* Copie la photo temporaire dans le repertoire de frise */
	function copyTempPhotoToFrise($user){
		$path = $this->directory . $user . "/frise/" . date("YmdHis") . ".jpg";
		$this->renamePhoto("temp/temp_" . $user . ".jpg",$_SERVER['DOCUMENT_ROOT'] .$path);
		return $path;
	}

   /* Copie la photo temporaire dans le repertoire de l'edito. Genere le chemin a partir de la date */
	function copyTempPhotoToEdito($user){
      $date = date("YmdHis");
      $path = "image_news/news_" . $date . ".jpg";
		$this->renamePhoto("temp/temp_" . $user . ".jpg",dirname(__FILE__) . "/" .$path);
		return $path;
	}
    
    function renamePhoto($pathImg,$name){
	  rename($pathImg,$name);
    }
    
    function deleteImage($pathImg){
    	unlink($pathImage);
    }
 
 }
 
?>
