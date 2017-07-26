/** GESTION DE LA TIMELINE **/

var FLICKR_LF = true;

var events = [];			// Evenements a supprimer a chaque chargement
var timeline;				// Objet qui stocke la timeline
var subtimeline = false;	// Indique qu'on affiche une sous frise (a partir d'un groupe pentecote)
var datasImages = [];		// Stocke les infos des images affichees
var flickrUserId = "97739281@N06";
var flickrAPIKey = "195bad828fbaf0ef4fd82a7d32fadf94";

/* Initialise la timeline */
function initTimelinePhotos(){
   /* Redefinition du bind de la timeline pour permettre la suppression des evenements */
	VMM.bindEvent = function(element, the_handler, the_event_type, event_data) {
		var e;
		var _event_type = "click";
		var _event_data = {};

		if (the_event_type != null && the_event_type != "") {
			_event_type = the_event_type;
		}

		if (_event_data != null && _event_data != "") {
			_event_data = event_data;
		}

		if( typeof( jQuery ) != 'undefined' ){
			$(element).bind(_event_type, _event_data, the_handler);
			events.push({element:element,type:_event_type});
		}
	}

	/* Internationalisation des dates */
	VMM.Util.date.month = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
	VMM.Util.date.month_abbr = ["Janv.","Févr.","Mars","Avr.","Mai","Juin","Juill.","Août","Sept.","Oct.","Nov.","Déc."];
	VMM.Util.date.theHour=function(hr){return hr;}
	VMM.Util.date.hourSuffix=function(){return "";}
 }



/* Charge une timeline */
/*@param id : identifiant du group photo */
/*@param name : nom du group (affiche dans le chemin de fer */
/*@param idPhotos : identifiant des photos a charger (passe dans le l'url) */
function loadTimeline(id,name,idPhotos){
	/* Gestion du chemin de fer */
	if(name != null){
		DisplayChemin.setRoot(name);
		subtimeline = false;
	}
	else{
		subtimeline = true;
	}
	// On supprime les evenements attaches
	$(events).each(function(){
		$(this.element).unbind(this.type);
	});

	// On intercepte le retour serveur pour recuperer les informations
	VMM.bindEvent(global, function(e,d){
      if(d.timeline.folder!=null && d.timeline.folder!=""){
         loadCollections(d.timeline.folder);
      }
		datasImages = [];
		$(d.timeline.date).each(function(){
			datasImages[this.startDate] = {id:this.data,name:this.headline,isTimeline:this.isTimeline};
		});
      /* Intercepte le click sur les timeline */
      
	}, "DATAREADY");

	/* Une fois les donnees chargees, on se positionne sur le dernier element et on charge un lien s'il existe */
	VMM.bindEvent(global,function(){
		// On focus sur le dernier element du slide
		timeline.timenav.goToMarker($('.marker').length-1);
		if(idPhotos!=null){
			loadPhotosById(idPhotos);
		}
      $('.flag-content').unbind('click.loadPhotos').bind('click.loadPhotos',function(){
          $('title').text("Photos : " + $(this).find('h3').text());
        	 var id = $(this).parent().parent().attr('id');
          loadPhotos(datasImages[id.substr(0,id.indexOf('-'))]);
    	});
	},"DATALOADED");

	// Ajoute des boutons lors du chargement de la timeline
	VMM.bindEvent(global,function(){
		var toolbar = $('.navigation > .toolbar');
		if(toolbar.length > 0 && $('.back-home',toolbar).length == 0){
			toolbar.append('<div class="back-home" rel="tooltip" '
				+ ' data-original-title="Aller au début"><div class="icon"></div></div>');
			$('.back-home',toolbar).bind('click',function(){
				VMM.DragSlider.cancelSlide();
				timeline.timenav.goToMarker(0);
			});
			toolbar.append('<div class="back-end" rel="tooltip" '
				+ ' data-original-title="Aller à la fin"><div class="icon"></div></div>');
			$('.back-end',toolbar).bind('click',function(){
				VMM.DragSlider.cancelSlide();
				timeline.timenav.goToMarker($('.marker').length-1);
			});
		}
	},"LOADED");

	timeline = new VMM.Timeline();
	var url = "MediaAction.php?action=1&idGroup=" + id;
   if(id == 33){
      loadCollections("Journees");
   }
	timeline.init(url);
}

/* Charge la timeline pour les sorties journees */
function loadTimelineJournee(idPhotos){
	loadTimeline(33,'Journée',idPhotos);
}

/* Charge la timeline pour les sorties pentecote */
function loadTimelinePentecote(idPhotos){
	loadTimeline('','Pentecôte',idPhotos);
}

/* Charge la timeline pour les autres photos */
function loadTimelineAutres(idPhotos){
	loadTimeline('44','Divers',idPhotos);
}

/* Charge une timeline a partir d'un identifiant */
function loadPhotosById(id){
	for(var i in datasImages){
		if(datasImages[i].id == id){
			// Chargement
			loadPhotos(datasImages[i]);
			// Placement sur le flag
			var nb = $('[id^="' + i + '"]').prevAll().length;
			timeline.timenav.goToMarker(nb);
		}
	}
}

/* Charge les photos lors du click sur la timeline. En fonction du type, on charge une autre timeline */
function loadPhotos(photo){
   if(subtimeline){
		DisplayChemin.setTitle2(photo.name);
	}
	else{
		DisplayChemin.setTitle1(photo.name);
	}
	/* Sous timeline, on la charge */
	if(photo.isTimeline == 'true'){
		loadTimeline(photo.id);
	}
	else{
		$('#divPhotos').empty();
      $('#divPhotosFlickr').empty();
		$.ajax({url:"MediaAction.php",data:{action:2,idMedia:photo.id},dataType:'json',success:function(data){
         getFlickrInfos(data.root.value);
         if(FLICKR_LF){
            // Recharge les photos si la taille de la fenetre change
            $(window).unbind('resize').bind('resize',function(){
               $('#divPhotosFlickr').empty();
               displayPhotosFlickrLF(data,'divPhotosFlickr');
            });
            displayPhotosFlickrLF(data,'divPhotosFlickr');
         }else{
            displayPhotos(data,'divPhotos');
         }
		}});
	}
}

var galleries = new Array();
var photosHD = new Array();

/* Charge toutes les galleries de la collection */
function loadCollections(collectionName){
   galleries = new Array();
   photosHD = new Array();
   $.ajax({
      url:"https://api.flickr.com/services/rest/",
      data:{
         method:"flickr.collections.getTree",
         api_key:flickrAPIKey,
         user_id:flickrUserId,
         format:"json",
         nojsoncallback:1
      },
      dataType:'json',
      success:function(data){
         if(data == null || data.collections == null){return;}
         $(data.collections.collection).each(function(){
       	if(this.title == collectionName){
               $(this.set).each(function(){
                  galleries[this.title] = this.id;
               });
            }
         });
      }
   })
}

/* Recupere les liens HD de flickr */
function getFlickrInfos(value){
   var name = value.substring(value.lastIndexOf("/")+1);
   if(galleries[name] == null){return;}
   saveGalleryInMemory(galleries[name]);
}

/* Recupere les photos de la gallerie et les chemins HD */
function saveGalleryInMemory(idGallery){
   photosHD = new Array();
   $.ajax({
      url:"https://api.flickr.com/services/rest/",
      data:{
         method:"flickr.photosets.getPhotos",
         api_key:flickrAPIKey,
         photoset_id:idGallery,
         extras:"url_o",
         format:"json",
         nojsoncallback:1,
      },
      dataType:'json',
      success:function(data){
         if(data == null || data.photoset == null){return;}
         $(data.photoset.photo).each(function(i){
            photosHD[this.title] = this.url_o;
         });
      }
   })
}

/* GESTION DU CHEMIN DE FER **/


var DisplayChemin = {
	chemins:["","",""],
	display:function(){
		$('#idChemin').empty();
		$(this.chemins).each(function(i){
			if(this!=null && this!=""){$('#idChemin').append(((i!=0)?" > ":"") + this);}
		});
	},
	setRoot:function(root){
		this.chemins[0] = root;
		this.chemins[1] = "";
		this.chemins[2] = "";
		this.display();
	},
	setTitle1:function(title){
		this.chemins[1]=title;
		this.chemins[2]="";
		this.display();
	},
	setTitle2:function(title){
		this.chemins[2]=title;
		this.display();
	}
}

/** GESTION DES PHOTOS **/

/* Affiche les miniatures des photos */
function displayPhotos(data,id){
	if(data == null){
		return;
	}
   // On charge les liens avec Flickr
   // On cherche les sets de la collection.
	var images = new Array();
	var root = null;
	if(data.root!=null){
		root = data.root.value;
	}
	$(data.photos).each(function(i){
		if(root!=null){
			images[i] = {root:root,name:this.name,width:this.width,tag:this.tag};
		}
      else{
         images[i] = {root:this.root,name:this.name,width:this.width,tag:this.tag};
      }
	});
	for(var i = 0 ; i<images.length;i++){
		var div = "<span data-tag=\"" + images[i].tag + "\" href=\"" + images[i].root + "/sd/" + images[i].name + ".jpg\">"
			+ "<img src=\"" + images[i].root + "/ld/" + images[i].name + ".jpg" + "\"/></span>";
		$('#' + id).append(div);
	}
	// Ajout du comportement
	$('#' + id).find("span").lightBox({
		titleGroup:'',
		getInfo:getTagInfos,
		tagPhoto:null
	});
}

/* FLICKR DISPLAY STYLE */

function displayPhotosFlickrLF(data,id){
   if(data == null){
		return;
	}
   // On charge les liens avec Flickr
	var images = new Array();
	var root = null;
   // On calcule les chemins des images
   if(data.root!=null){
		root = data.root.value;
	}
	$(data.photos).each(function(i){
		if(root!=null){
			images[i] = {root:root,name:this.name,width:this.width,height:this.height,tag:this.tag};
		}
      else{
         images[i] = {root:this.root,name:this.name,width:this.width,height:this.height,tag:this.tag};
      }
	});
   runDisplay(images,id);
	/*for(var i = 0 ; i<images.length;i++){
		var div = "<span data-tag=\"" + images[i].tag + "\" href=\"" + images[i].root + "/sd/" + images[i].name + ".jpg\">"
			+ "<img src=\"" + images[i].root + "/ld/" + images[i].name + ".jpg" + "\"/></span>";
		$('#' + id).append(div);
	}*/
	// Ajout du comportement
	$('#' + id).find("span").lightBox({
		titleGroup:'',
		getInfo:getTagInfos,
		tagPhoto:null
	});
}


var minFlickrLFWidth = 130;

function runDisplay(images,id){
   $('#divPhotosFlickr').show();
   $('#divPhotos').hide();
   var divWidth = $('#' + id).width()-20;
   var totalPart = 0;
   var toDisplay = [];
	for(var i in images){
		var img = images[i];
      var ratio = img.width / img.height;

		if((divWidth - (toDisplay.length)*4)/ (ratio + totalPart) > minFlickrLFWidth){
			 // On continue l'algo
			 totalPart+=ratio;
			 toDisplay.push(img);
		}else{
			// On affiche les images avec ce ratio
			display(toDisplay,totalPart,id,divWidth);
			totalPart = ratio;
			toDisplay = [img];
		}
	}
	display(toDisplay,totalPart,id,divWidth,150);
}
function display(images,totalPart,id,divWidth,max){
   var div = $('#' + id);
	max = max || 500;
	var height = Math.min((divWidth -(images.length)*4)  / totalPart,max);
	// Pour toutes les images, calcule la taille reelle basee sur cette part
   for(var i = 0 ; i < images.length ; i++){
		var img = images[i];
      var width = (img.width/img.height)*height;
      var imgDiv = "<span data-tag=\"" + img.tag + "\" href=\"" + img.root + "/sd/" + img.name + ".jpg\">"
			+ "<img src=\"" + img.root + "/ld/" + img.name + ".jpg\" height=\"" + height + "\" width=\"" + width + "\"/></span>";

      div.append(imgDiv);
		/*var image = document.createElement('img');
		image.src= img.root + "/ld/" + img.name + ".jpg";
		image.height = height;
		image.width = (img.width/img.height)*height;
		div.appendChild(image);*/
      if(i == images.length -1){
         // Cas taille plus grande que le cadre
         var last = $('img:last','#' + id);
         var pos = last.position().left;
          if(divWidth <= pos + last.width()){
             last.width(divWidth - pos);
          }else{
            // Cas taille plus petite (5)
             if(divWidth - 10 < pos + last.width()){
               last.width(divWidth - pos);
             }
          }
      }
	}
   div.append('<br/>');
	//div.appendChild(document.createElement('br'));
}



function getTagInfos(id,span){
	$.ajax({url:"MediaAction.php",data:{action:3,idPhotoTag:id},dataType:'json',success:function(data){
		var types = ["Groupe","Portrait","Paysage","Moto","Groupe motos","Visite","Autres"];
		span.html("<br/><b>Date</b> : " + data.date + "<br/>"
			+ ((data.type!=-1)?"<b>Type</b> : " + types[data.type] + "<br/>":"")
			+ ((data.personnes!="")?"<b>Personnes</b> : " + data.personnes + "<br/>":"")
			+ ((data.motos!="")?"<b>Motos</b> : " + data.motos + "<br/>":"")
			+ ((data.motsCles!="")?"<b>Mots cles</b> : " + data.motsCles + "<br/>":"")
			+ ((data.lieux!="")?"<b>Lieux</b> : " + data.lieux + "<br/>":"")
			+ ((data.commentaire!="")?"<b>Commentaires</b> : " + data.commentaire + "<br/>":""));
	}});
}

function pushDemandeHD(path,button){
   $.ajax({
      url:"MediaAction.php",
      data:{action:8,path:path},
      success:function(data){
         if(data.error!=null){
            $('#lightbox-image-details').before("<div style='display:none;color:red;font-weight:bold' id='idMessageDemande'>" +data.error+ "</div>");
               $('#idMessageDemande').fadeIn(1000).delay(2500).fadeOut(500,function(){
                  $(this).remove();
               });
         }
         if(data.message == "ok"){
            $('#lightbox-image-details').before("<div style='display:none;color:green;font-weight:bold' id='idMessageDemande'>Demande enregistrée</div>");
               $('#idMessageDemande').fadeIn(1000).delay(2500).fadeOut(500,function(){
                  $(this).remove();
               });
         }
      }
   })
}
