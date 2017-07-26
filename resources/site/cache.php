<?

/* 
* Gestionnaire de cache pour les images visualisees sur un mobile.
* Pour retrouver les photos, le nom n'est pas utilisable (car non unique), on * * utilise un md5 du chemin complet qui est unique
*/

$filename = $_GET["file"];
$format = $_GET["format"];

header("Content-type:image/png");
$image = imagecreatefromjpeg(getFile($filename,$format));
ImageJpeg($image);

function getFile($file,$format){
	$f = defineFilename($file,$format);
	if(!isPhotoInCache($f)){
		resizeImage($file,$f);
	}
	return $f;
}

/* Redimensionne l'image au bon format */
function resizeImage($fileIn,$fileOut){
	// On met le plus long cote a 480px
	$source = imagecreatefromjpeg($fileIn);
	$size = getImageSize($fileIn);
	if($size[0] > $size[1]){	// Largeur plus grande		
		$width = 480;
		$height = ($width / $size[0]) * $size[1];
	}
	else{
		$height = 480;
		$width = ($height / $size[1]) * $size[0];
	}
	$img = imagecreatetruecolor($width,$height);
	imagecopyresampled($img, $source, 0, 0, 0, 0, $width, $height, $size[0],$size[1]);
	imagejpeg($img,$fileOut);	// Ecrit le fichier
}

/* Verifie si l'image existe pour un format donne */
function isPhotoInCache($file){
	return is_file($file);
}

function defineFilename($file,$format){
	$root = "cache/";
  $md5 = md5($format . "_" . $file);
  $info = pathinfo($file);
	return $root . $md5 . "." . $info["extension"];
  //return $root . $format . "_" . substr(str_replace(dirname($file),"",$file),1);
}

?>
