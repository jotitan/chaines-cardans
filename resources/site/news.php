<? require_once("decorator.php"); ?>
<? require_once("NewsDao.php"); ?>
<? require_once("SecuriteDao.php"); ?>

<? writeHeader("News","news"); ?>


<style>

.news {
   padding-top:-20px;
}

div.news h3 {
	color:#004C62;
	font-weight:bold;
	margin-left:110px;
	font-size:26px;
}

div.news div.content {
	padding-right:20px;
	padding-left:20px;	
	text-align:justify;
}

div.news img {
	float:left;
	width:210px;
	height:140px;
	margin-left:20px;
   margin-right:10px;
   padding-left:0px;
   border:dashed 2px #004c62;
}

hr {
	margin-left:20px;
	width:60%;
	border-top:6px dashed #004C62;
	border-bottom:0px;
	margin-bottom:20px;
	margin-top:20px;
	text-align:left;
}

.date {
	float:left;
	color:white;
	margin-left:-215px;
	margin-top:90px;
	position:relative;
	font-size:16px
}

.date span {
	font-size:26px;
	font-weight:bold;
}

.dateBG {
   background-color:black;
   float:left;
   margin-left:-220px;
   margin-top:90px;
   opacity:0.7;
   width:130px;
   font-size:24px
}



</style>

<div style="padding-top:1px;">
<?
   $dao = new NewsDao();
	$news = $dao->getNews(5);
	foreach($news as $n){
?>
<div class="news">
	<img src="<? echo $n->urlImage; ?>"/>
	<div class="dateBG">&nbsp;</div>
	<div class="date"><span><? echo $n->date->day; ?></span> <? echo $n->date->getShortMonth() . " " . $n->date->year; ?></div>
	<div style="font-size:14px;">
		<h3><? echo $n->titre; ?></h3>
		<div class="content"><? echo $n->contenu; ?></div>
	</div>
	<div style="clear:both"></div>
</div>
<hr/>

<? } ?>
</div>
<? writeFooter(); ?>
