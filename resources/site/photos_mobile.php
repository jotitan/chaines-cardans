<? require_once("MediaDao.php"); ?>

<html>

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
   <script type="text/javascript" src="js/jquery/jquery-1.11.3.min.js"></script>
   <script type="text/javascript" src="js/swiper/swiper.jquery.min.js"></script>
   <script type="text/javascript" src="js/jquery/jquery.mobile-1.4.5.min.js"></script>
   <link rel="stylesheet" href="js/jquery/jquery.mobile-1.4.5.min.css"/>
   <link rel="stylesheet" href="css/swiper/swiper.min.css"/>

	<style>
       .close {
           background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAACVBMVEUAAAD///////9zeKVjAAAAAnRSTlMAAHaTzTgAAAFkSURBVHgB7doxasRAEETR+nP/QztSJCwY/6AwlOLtrofxrqTuCeXrAzDAAAMMMMAAAwwwwAADDDDAOYfb675Pvuqk4N3nHuAFRwKs4FwAhED0iKr2+QQvUPXRHWR1fA9XG9/FVcb18XVxnXxVXC9fE9fNV8T185+P6+g/Hd/TaYMR+HyCEfh8ghH4fIIR+HyCEfh8ghH4fIIR+HyCEfh8ghDY/G9Akg+ByJeAJ0nkS8CTJfIl4J0m8ol/3lX5BC8w+QQtUPkEK3D5BCmQ+QQnEPkWgMz3AGS+B+Dz//dfoP0/0P4WtH8H2r+EjXtB4W7Yfx6oPxG1ngkv8r0gLt8L+m9G/XfD/ttxfz7Qn5D0Z0T9KVl/TtiflPZnxf1peX9f0N+Y9HdG/a1Zf2/Y35z2d8eN7Xn//ED/BEX/DEn/FI04R5Qkv/aRADSAPwO4yn+u6z47zjfAAAMMMMAAAwwwwAAD/AD+GUPfGGzXXgAAAABJRU5ErkJggg==);
        	background-size: 48px 48px;
        	background-repeat:no-repeat;
        	position:absolute;
        	right:10px;
        	top:10px;
        	z-index:10;
        	cursor:pointer;
        	width:48px;
        	height:48px;
      }

		ul#idGallerie li {
			list-style: none;
			display: inline;
			margin-left: 5px;
			overflow:hidden;
			float:left;
			width:67px;height:67px;
			border:1px solid #ccc;
		}

    .swiper-container {width:100%;height:100%;}
    .swiper-slide {text-align:center;}

    .swiper-slide img{
    	width:auto;
    	height:auto;
    	max-width:100%;
    	max-height:100%;
    	-ms-transform:translate(-50%,-50%);
    	-webkit-transform:translate(-50%,-50%);
    	-moz-transform:translate(-50%,-50%);
    	transform:translate(-50%,-50%);
    	position:absolute;
    	left:50%;
    	top:50%;
    }
	</style>
</head>

<body>

<div data-role="page" id="idRoot">

	<div data-role="header" data-fullscreen="true" data-position="inline">
		<a href="accueil.php" rel="external" data-icon="home" data-theme="f" data-iconpos="notext">Accueil</a> 
		<h1>Photos</h1>
	</div>
	<div data-role="content">	
		<ul data-role="listview" data-inset="true">
			<li><a href="#photosJournee">Journée</a></li> 
			<li><a href="#photosPentecote">Pentecôte</a></li>
			<li><a href="#photosAutre">Autres</a></li>  
         <li style="display:none"><a href="#photosMariage" id="idMariage">Mariage</a></li>
		</ul> 
	</div>
</div>

<div data-role="page" id="photosMariage">
	<div data-role="header" data-fullscreen="true" data-position="inline">
		<a href="#idRoot" data-icon="back" data-theme="f" data-iconpos="notext">Retour</a>
		<h1>Mariage</h1>
	</div>
	<div data-role="content" data-inset="true">
		<ul data-role="listview">
			<?
				$dao = new MediaDao();
				$groups = $dao->getByGroup(50);
				$photos = $groups->getInfo("photos");
				foreach($photos as $i => $group){
					echo "<li><a rel=\"external\" href=\"photos_mobile.php?id=" . $group->getInfo("id") . "#gallerie\">" . $group->getInfo("name") . "</a></li>";
				}
			?>
		</ul>
	</div>
</div>

<div data-role="page" id="photosJournee">
	<div data-role="header" data-fullscreen="true" data-position="inline">
		<a href="#idRoot" data-icon="back" data-theme="f" data-iconpos="notext">Retour</a>
		<h1>Photos journée</h1>
	</div>
	<div data-role="content" data-inset="true">
		<ul data-role="listview">
			<?
				$dao = new MediaDao();
				$groups = $dao->getByGroup(33);
				$photos = $groups->getInfo("photos");
				foreach($photos as $i => $group){
					echo "<li><a rel=\"external\" href=\"photos_mobile.php?id=" . $group->getInfo("id") . "#gallerie\">" . $group->getInfo("name") . "</a></li>";
				}
			?>
		</ul>
	</div>
</div>

<div data-role="page" id="photosPentecote" >
	<div data-role="header" data-fullscreen="true">
		<a href="#idRoot" data-icon="back" data-theme="f" data-iconpos="notext">Retour</a>
		<h1>Photos pentecôte</h1>
	</div>
	<div data-role="content">
		<ul data-role="listview">
		<?
			$groups = $dao->getByGroup(null);
			$photos = $groups->getInfo("photos");
			for($i = sizeof($photos)-1 ; $i >= 0 ; $i--){
				$group = $photos[$i];
				/* Cas d'une sous liste */
				if($group->getInfo("timeline") == "true"){
					echo "<li><a rel=\"external\" href=\"photos_mobile.php?id=" . $group->getInfo("id") . "#pentecote\">" . $group->getInfo("name") . "</a></li>";
				}
				else{
					echo "<li><a rel=\"external\" href=\"photos_mobile.php?id=" . $group->getInfo("id") . "#gallerie\">" . $group->getInfo("name") . "</a></li>";
				}
			}
		?>
		</ul>	
	</div>
</div>

<div data-role="page" id="photosAutre" data-add-back-btn="true" >
	<div data-role="header" data-fullscreen="true">
		<a href="photos_mobile.php" data-icon="back" data-theme="f" data-iconpos="notext">Retour</a>
		<h1>Photos autres</h1>
	</div>
	<div data-role="content">

	</div>
</div>

<div data-role="page" id="gallerie" data-add-back-btn="true">
	<div data-role="header" data-fullscreen="true" data-position="inline">
		<a href="photos_mobile.php" data-icon="back" data-theme="f" data-iconpos="notext" id="idRetourGallerie" rel="external">Retour</a>
		<h1><span id="idNomGallerie">Gallerie</span></h1>
	</div>
	<div data-role="content">
		<ul style="padding-left:0px" id="idGallerie">

		</ul>
	</div>
</div>

<div data-role="page" id="pentecote" data-add-back-btn="true">
	<div data-role="header" data-fullscreen="true" data-position="inline">
		<a href="photos_mobile.php#photosPentecote" data-icon="back" data-theme="f" data-iconpos="notext" ref="external">Retour</a>
		<h1><span id="idNomGallerieP">Pentecote</span></h1>
	</div>
	<div data-role="content">
		<ul style="padding-left:0px" id="timeline">

		</ul>
	</div>
</div>

<div class="swiper-container" style="display:none;background-color:black;">
	<div class="swiper-wrapper"></div>
	<!--div class="swiper-pagination swiper-pagination-white"></div-->
	<div class="swiper-button-next swiper-button-white"></div>
	<div class="swiper-button-prev swiper-button-white"></div>
	<div class="close swiper-button-white"></div>
</div>


<script language="Javascript">

   $(document).ready(function(){
		$('#timeline').listview();
      if(document.location.href.indexOf('type=10')>-1){
         $('#idMariage').click();
      }
	});

	var instance = null;
	var currentId = null;
	var currentIdPentecote = null;

	$('body').on('pageshow','#gallerie',function(event,ui){
		var id = getIdOfUrl();
		if(currentId == id){
			// On ne recharge pas
			return;
		}
		currentId = id;
		$('#idGallerie').empty();
		loadPhotos(id);

	});

	$('body').on('pageshow','#pentecote',function(event,ui){
		var id = getIdOfUrl();
		if(currentIdPentecote == id){
			// On ne recharge pas car deja charge
			return;
		}
		currentIdPentecote = id;		
		$('#timeline').empty();
		$('#idNomGallerieP').text("");
		$.ajax({url:'MediaAction.php',data:{action:1,idGroup:id},dataType:'json',success:function(data){
			$('#idNomGallerieP').text(data.timeline.title);
			$(data.timeline.date).each(function(){
				$('#timeline').append('<li><a rel="external" href="photos_mobile.php?id=' + this.data + '#gallerie">' + this.headline + '</a></li>');
			});
			$('#timeline').listview('refresh');
		}});
	});

	function getIdOfUrl(){
      return new RegExp(/id=([\d]+)/).exec(location.href)[1];
	}	

	function loadPhotos(id){
		// On detache la gallerie
		//if(instance!=null){
		//	window.Code.PhotoSwipe.detatch(instance);
		//}
		$('#idNomGallerie').text("");
		$.ajax({url:'MediaAction.php?action=2&idMedia=' + id,dataType:'json',success:function(data){
			// On recupere le titre de la serie et le group
			$('#idNomGallerie').text(data.root.titre);
			$('#idRetourGallerie').attr('href','photos_mobile.php#' + ((data.root.group == 33)?"photosJournee":"pentecote?id=" + data.root.group));
         showPhotos(data);
			/*$(data.photos).each(function(i){
				//var urlMobile = data.root.value + "/" + data.root.sdDir + "/" + this.name + ".jpg";
				var urlThumb = data.root.value + "/" + data.root.ldDir + "/" + this.name + ".jpg";
            var urlThumb2 = "cache.php?format=mb&file=" + data.root.value + "/" + data.root.ldDir + "/" + this.name + ".jpg";
				//$('#idGallerie').append('<li><a rel="external" href="cache.php?format=mb&file=' + urlMobile + '">'	+ '<img src="' + urlThumb + '"/></a></li>');
            $('#idGallerie').append('<li><img src="' + urlThumb + '"/></li>');
            $('.swiper-wrapper').append('<div class="swiper-slide"><img data-src="' + urlThumb2 '" class="swiper-lazy"/><div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div></div>');
			});*/
			//instance = $("ul#idGallerie a").photoSwipe({slideshowDelay:5000},"gallerie");
		}});
	}
   var swipper = null;
   function showPhotos(data){
      $('#idGallerie').empty();
      $(data.photos).each(function(i){
				var urlThumb = data.root.value + "/" + data.root.ldDir + "/" + this.name + ".jpg";
            var urlThumb2 = "cache.php?format=mb&file=" + data.root.value + "/" + data.root.sdDir + "/" + this.name + ".jpg";
            $('#idGallerie').append('<li data-pos="' + i + '"><img src="' + urlThumb + '"/></li>');
            $('.swiper-wrapper').append('<div class="swiper-slide"><img data-src="' + urlThumb2 + '" class="swiper-lazy"/><div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div></div>');
			});
         // Open the slider when click on image
         $('.close').unbind('click').bind('click',close);
       $('ul#idGallerie > li').unbind('click').bind('click',function(){
    		$('ul#idGallerie').hide();
    		$('.swiper-container').show();
    		if(swipper == null){
    			swipper = runSwiper($(this).data('pos'));
    		}else{
             swipper.slideTo($(this).data('pos'));
    		}
    	});
   }

   function runSwiper(initialSlide){
		return new Swiper('.swiper-container', {
			initialSlide:initialSlide,
			nextButton: '.swiper-button-next',
			prevButton: '.swiper-button-prev',
			keyboardControl:true,
			preloadImages: false,
			lazyLoading: true,
         lazyLoadingInPrevNext:true,
         lazyLoadingInPrevNextAmount:2
		});
	}

   function close(){
		$('ul#idGallerie').show();
		$('.swiper-container').hide();
	}

</script>

</body>


</html>
