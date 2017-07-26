/** GESTION DE LA TIMELINE **/


var events = [];			// Evenements a supprimer a chaque chargement 
var timeline;				// Objet qui stocke la timeline
var subtimeline = false;	// Indique qu'on affiche une sous frise (a partir d'un groupe pentecote)
var datasImages = [];		// Stocke les infos des images affichees


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

	/* Intercepte le click sur les timeline */
	$('.flag').live('click',function(){
		var id = $(this).parent().attr('id');
		loadPhotos(datasImages[id.substr(0,id.indexOf('-'))]);
	});
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
		datasImages = [];
		$(d.timeline.date).each(function(){
			datasImages[this.startDate] = {id:this.data,name:this.headline,isTimeline:this.isTimeline};
		});
	}, "DATAREADY");
	
	/* Une fois les donnees chargees, on se positionne sur le dernier element et on charge un lien s'il existe */
	VMM.bindEvent(global,function(){
		// On focus sur le dernier element du slide
		timeline.timenav.goToMarker($('.marker').length-1);
		if(idPhotos!=null){
			loadPhotosById(idPhotos);
		}
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
		$.ajax({url:"MediaAction.php",data:{action:2,idMedia:photo.id},dataType:'json',success:function(data){
			displayPhotos(data,'divPhotos');
		}});
	}
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
		var div = "<div data-tag=\"" + images[i].tag + "\" href=\"" + images[i].root + "/sd/" + images[i].name + ".jpg\">"
			+ "<img src=\"" + images[i].root + "/ld/" + images[i].name + ".jpg" + "\"/></div>";
		$('#' + id).append(div);	
	}
	// Ajout du comportement
	$('#' + id).find("div").lightBox({
		titleGroup:'',
		demandePhoto:null,
		getInfo:getTagInfos,
		tagPhoto:null
	});
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
