<?php

require_once("Model.php");
require_once("MediaDao.php");

session_start();

header("Content-type:image/jpeg");
/* Creation de l'image */
$image = imagecreatetruecolor(850,600) or die ("erreur");

$dao = new MediaDao();

$infoMedia = $dao->getPhotosOfMedia($_GET['id']);
$photos = $infoMedia->getInfo("photos");
$x = 0;
$y = 0;
$start = ($_GET['page']!=null && $_GET['page']>0)?($_GET['page']-1)*100:0;
for($i = $start ; $i < sizeof($photos) && $i < 100 + $start; $i++){
	$temp = imagecreatetruecolor(80,55);
	$name = $infoMedia->getInfo("root"). "/ld/" . $photos[$i]->getInfo("name") . ".jpg";
	$toCopy = imagecreatefromjpeg($name);
	$size = getimagesize($name);
	if($size[0]<100){
		imagecopyresampled ( $temp , $toCopy , 20,0,0,0 , 40 , 55,$size[0],$size[1] );
	}
	else{
		imagecopyresampled ( $temp , $toCopy , 0,0,0,0 , 80 , 55,$size[0],$size[1] );
	}
	if($x+85>850){
		$x = 0;
		if($y+60>600){
			break;
		}
		$y+=60;
	}
	imagecopy($image, $temp,$x , $y, 0, 0, 80, 55);
	$x+=85;
}
//imagejpeg($image,"temp/temp_planche.jpg");
imagejpeg($image);

?>
