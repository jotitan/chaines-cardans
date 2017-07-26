<?php

require_once("S3.php");

//include 'aws.phar';

?>

Test sur hmac <br/><br/>

<?

$s3 = new S3("AKIAJMEISU7R7OPCZYVQ", "M9uplQP9aBX0VGwuczg9M5OA9BIzeByZNkGO3SLo");

var_dump($s3->listBuckets());
//var_dump($s3->getBucket("chaines.cardans"));

/*$r = "GET\n" .
	date("D, d M Y h:i:s O") . "\n" .
	"/chaines.cardans";

$time = time() + 3600;

$r2 ="GET\n" .
	$time . "\n" .
	"/chaines.cardans";




$sign = base64_encode(hash_hmac("sha1",$r,"M9uplQP9aBX0VGwuczg9M5OA9BIzeByZNkGO3SLo"));
echo "AWS " . "AKIAJMEISU7R7OPCZYVQ:" . $sign;
echo "<br/><br/>";
echo $time;
*/
?>

<br/>