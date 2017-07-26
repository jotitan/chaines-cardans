var pas = 300;
var latX = 48.874798;
var latY = 2.639873;

function buildYearButton(){
	$('#idDivSorties > div').each(function(){
		$('#idControles').append("<button onclick=\"moveTo(this)\">" + this.title + "</button><br/>");
	});
}
 
function moveToByYearDiv(div){
	var pos = div.get(0).offsetTop;
	$('#idDivSorties').stop(true).animate({'top':'-' + pos}, 100, 'linear');
}

function moveTo(button){
   moveToByYearDiv($('#idDivSorties > div[title=' + $(button).text() + ']'));
}

function initActions(){

	$('#monte').click(function(){
		var pos = $('#idDivSorties').get(0).offsetTop + pas;
		if(pos > 0){
			pos = 0;
		}
		$('#idDivSorties').stop(true).animate({'top':pos}, 100, 'linear');
	});
	
	$('#descends').click(function(){
		var pos = $('#idDivSorties').get(0).offsetTop - pas;
		if(pos < parseInt("-" + parseInt($('#idDivSorties').innerHeight()-(pas - 20)))){
			pos = parseInt("-" + (parseInt($('#idDivSorties').innerHeight()) - (pas - 20)));
		}
		$('#idDivSorties').stop(true).animate({'top':pos}, 100, 'linear');
	});
}

var photosManager = null;//new PhotosManagerDiv2("photos_sortie");

var currentObject = null;

function PointObject(id,code,positionX,positionY){
	this.idSpan = "idSortie_" + id;
	this.id = id;
	this.span = $('#' + this.idSpan);
   this.span.attr("data-code",code);
   this.span.attr("data-id",id);
   this.span.data("point",this);
	this.positionX = positionX;
	this.positionY = positionY;
	this.id = id;
	this.code = code;
	this.map = null;
	this.marker = null;
   this.window = null;

	this.setMap = function(map){
		this.map = map;
	}
	
	var current = this;
	
   this.init = function(){
      this.marker = new google.maps.Marker({position:new google.maps.LatLng(this.positionX,this.positionY)});
	   this.window = new google.maps.InfoWindow({content:current.span.text()});
   }

	this.getMarker = function(){
		return this.marker;
	}

	this.span.bind('click',function(){
		if(currentObject!=null && currentObject.id == current.id){
			currentObject.span.css({color:'black',fontWeight:'normal'});
			currentObject = null;
			current.map.center();
			return;
		}
		if(currentObject!=null){
			currentObject.span.css({color:'black',fontWeight:'normal'});
			currentObject.window.close();
		}
		currentObject = current;
		currentObject.span.css({color:'#004C62',fontWeight:'bold'});
      current.window.open(current.map.map,current.marker);

		if(current.id!=null){
			getSortieInformations(current.id,PointObject.bindMethod);
		}
	});
	
	this.span.bind('mouseover',function(){
		if(currentObject != null){return;}
      current.window.open(current.map.map,current.marker);
		$(this).css({color:'#007DA1',fontWeight:'normal'});
	});
	
	this.span.bind('mouseout',function(){
		if(currentObject != null){return;}
      current.window.close();
		$(this).css({color:'black',fontWeight:'normal'});
	});

   this.init();
}

/* Window info actuellement affiche */
var currentInfo = null;

function MyMap(idMap,idJournee,idPentecote){
	this.pentecotePoints = new Array();
	this.journeePoints = new Array();
	this.map = null;
	this.idMap = idMap;
	this.journee = $('#' + idJournee);
	this.pentecote = $('#' + idPentecote);

   var current = this;

	this.init = function(){
		this.map = new google.maps.Map($('#' + this.idMap).get(0),{
         center:new google.maps.LatLng(49.05587, 3.42327),
         zoom:7,
         mapTypeId: google.maps.MapTypeId.ROADMAP});
   }

	this.center = function(){
		//this.map.panTo(new GLatLng(49.05587, 3.42327), 7);
	}

	this.addPentecotePoint = function(point){
		this.pentecotePoints[this.pentecotePoints.length] = point.getMarker();
		point.setMap(this);
	}
	
	this.addJourneePoint = function(point){
		this.journeePoints[this.journeePoints.length] = point.getMarker();
		point.setMap(this);
	}
	
	this.hideJourneePoints = function(){
		for(var i = 0 ; i < this.journeePoints.length ; i++){
			this.journeePoints[i].setMap(null);
		}
	}
	
	this.hidePentecotePoints = function(){
		for(var i = 0 ; i < this.pentecotePoints.length ; i++){
			this.pentecotePoints[i].setMap(null);;
		}
	}
	
	this.showJourneePoints = function(){
		for(var i = 0 ; i < this.journeePoints.length ; i++){
			this.journeePoints[i].setMap(this.map);
		}
	}
	
	this.showPentecotePoints = function(){
		for(var i = 0 ; i < this.pentecotePoints.length ; i++){
			this.pentecotePoints[i].setMap(this.map);
		}
	}
	
	this.showJournee = function(){
		this.typeJournee=true;
		this.journee.show();
		this.pentecote.hide();
		this.showJourneePoints();
		this.hidePentecotePoints();
		this.map.setCenter(new google.maps.LatLng(latX,latY),7);
	}
	
	this.showPentecote = function(){
		this.typeJournee=false;
		this.journee.hide();
		this.pentecote.show();
		this.hideJourneePoints();
		this.showPentecotePoints();
		//this.map.setCenter(new GLatLng(latX,latY),5);
	}
	
   /* Calcule les calques affiches au survol des markers */
   this.buildInfoMarkers = function(){
      var tab = new Array();
      $('span[data-code]','#idDivSorties').each(function(i,e){
        var code = $(e).data("code");
        if(tab[code]==null){
            var marker = $(e).data("point").marker;
            var content = "<ul style=\"font-size:12px;padding-left:15px;\">";
            var title = null;
            $('span[data-code="' + code + '"]','#idDivSorties').each(function(i,span){
               var txt = $(span).text();
               index = /(le [0-9])/.exec(txt).index; // permet de separer le titre de la date
               if(title == null){
                  title = txt.substr(0,index);
               }
                content+="<li><a style=\"color:black\" href=\"javascript:clickOnSpan(" + $(span).data("id") + ")\">" + txt.substr(index) + "</a></li>";
            });
            content="<span style=\"font-size:14px;font-weight:bold\">" + title + "</span>" + content + "</ul>";
            var info = new google.maps.InfoWindow({content:content});
            google.maps.event.addListener(marker, 'click', function() {
                if(currentInfo!=null){currentInfo.close();}
                currentInfo = info;
                info.open(current.map,marker);
            });
        }
     });
   }

	this.init();	
}

/* Click sur le span en question */
function clickOnSpan(id){
   currentInfo.close();
   $('span[data-id="' + id +'"]').click();
   moveToByYearDiv($('span[data-id="' + id +'"]').parent());
}

var map;
function initMaps(){
	map = new MyMap("map_canvas","idJournee","idPentecote");
	map.addJourneePoint(new PointObject('vaires',null,latX,latY));
	map.addPentecotePoint(new PointObject('vaires',null,latX,latY));
	
	initActions();
	buildYearButton();
}

function addJourneePoint(point){
	map.addJourneePoint(point);
}

function addPentecotePoint(point){
	map.addPentecotePoint(point);
}

function showJournee(){
	hideShowBloc($('#idHideButton'),true);
	map.showJournee();
}

function showPentecote(){
	hideShowBloc($('#idHideButton'),true);
	map.showPentecote();
}

function hideShowBloc(button,forceShow){
	if($('#idGmap:visible').length == 0 || forceShow == true){
		$('#idGmap').show();
		button.html("Cacher");
	}
	else{
		$('#idGmap').hide();
		button.html("Afficher");
	}
}

function getSortieInformations(id,bindMethod){
	$.ajax({url:"MediaAction.php",data:{action:6,id:id},dataType:'json',success:function(data){
		if(data == null){return;}
		$('#sortie').show();
		$('#title_sortie').text(data.sortie.value);
		$('#desc_sortie').html(data.desc.replace(/\$;/g,"&").replace(/&lt;/g,"<").replace(/&gt;/g,">"));
		$('#present_sortie').show();
		$("#id_present").append("<span>(" + data.personnes.length + ") :</span>");
		
		/* Affichage des personnes */
		$("#idCaroussel").empty().append('<ul class="jcarousel-skin-tango"></ul>');
		$(data.personnes).each(function(i){
			var current = this;
			var li = $('<li><img src="' + this.photo + '___.jpg" alt="'+ this.surname + " " + this.name + '" title="'+ this.surname + " " + this.name + '"/></li>');			
			if(current.id!=null && current.id!="" && bindMethod!=null){
				li.bind('click',function(){
					bindMethod(current.id);
				});
			};
			$("ul","#idCaroussel").append(li);
		});
		$('#idCaroussel>ul').jcarousel({wrap: 'circular'});

		/* Creation des photos de la sortie */
		$('#idPhotos').empty();
		if(data.images!=null && data.images.length > 0){
			$('#idPhotos').show();
			displayPhotos({photos:data.images,root:data.root},'idPhotos');
			$('#idPhotos').css('max-height',$('#sortie').height());
		}
		else{
			$('#idPhotos').hide();
		}
		/* Lien vers les photos de la sortie */
		$('#idLinkPhotos').attr('href','photos.php?type=' + ((map.typeJournee)?0:1) + '&idPhotos=' + data.group.media);
		if($('#sortie').height() > $('#idPhotos').height() + 200){
			$('#idCaroussel').width($('#sortie').width() -50);
		}
		else{
			$('#idCaroussel').width($('#sortie').width() - $('#idPhotos').width() -50);
		}
		$('.jcarousel-container').width($('#idCaroussel').width() -100);
	}});
}

