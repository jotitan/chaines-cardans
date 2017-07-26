<? 

require_once("Model.php"); 
require_once("decorator.php");
require_once("MediaDao.php");

writeHeaderSecurite("Tagger des photos","compte",true); ?>

<link rel="stylesheet" media="screen" type="text/css" href="css/farbtastic.css" />

<script type="text/javascript" src="js/farbtastic.js"></script>
<script type="text/javascript" src="js/jquery/jquery.color.js"></script>

<style>
	.ui-menu .ui-menu-item a {
		line-height:1;
		font-size:12px;
	}

	.choice_icone > .icon {
		background-repeat:no-repeat;
		background-size:18px auto;
		padding-left:25px;
      background-image:url('css/img/sprite_tag.png')
	}
	
	#idContent > div {
		font-size:12px;
		border:#004C62 1px outset;
		margin-bottom:3px;
		padding-top:3px;
		padding-bottom:3px;
		border-radius:5px;
		margin-right:5px;
		display:inline-block;
		background-color:white;
		transition:background-color .6s ease-in;
	}


	#idContent > div:hover{
		background-color:#8ab6bd !important;		
		color:white;
		cursor:pointer;
	}

	.delete{
		background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wHGRY5J4U8VeIAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAACNUlEQVQ4y6WTP0wTcRTHP3dH2ysWKg3QUqSoNIARJLEYdVDCIFMTmNlcWBhxNcbBxIQYBwfFmLA7YYyKOICDEUNYQEJbG4NY2iNoyyVwvau9/hwIf2KLg77x977vk/d+7/skIQT/E1V/Pgg9O7K7GpvIL3zA2Ejtieoa8fb14wi34ar3S0f10tEOzPiK+Dk1hbPaibf3IkpNLfmdXzj1dTKzHwHw3xxB7TgvlQHM+IrYnHxK46UIUlcEeW0ZRfsOQMHjRwk2k515ibaRonPs7gFEEkIg9OxI+snjCV+4BakrQun9C8y8iVwsobr2phSGjtJzGf3NK9Kqi85b93DV+yUZYHc1NqGWTJwd53DMv0YuFJCLJRzGFnYug53LAGDNTeO5dgMjGWN7aeFwhK1H48IT9O+J1hIUPicq/rg7UI1dfwq7IJHSM/TcfyZVARgbKbztLZS+xHDZu5ycfF4RoA0PouRtXBeuYK5+AkDeT9o7BYShU7KMv+69aNrlPqiqa8RaS6BWeynlcmjDg8cCnK1t5BIJ1GDosANvXz/m4jKi6Sx5zUDUnjgW4GoNkU4uEhgYOgQ4wm1YodNsv5umJhpFsYoViz3RKMm5t7h9AdzdkXIjxR7cwS87qbnej/VtHTsRB0Bp78DhD/B1fpZ8ViN8e5yGnqtSRSvHJx9iJGOc6e5F8TWzv6V0chG3L0Dz6NhBcRkAwPqxKbaXFtBmpjDT6wCowRCBgSHc3RFqG5qOP6Z/id/3IPWZjuWFhAAAAABJRU5ErkJggg==");
		margin-left:10px;
		display:inline;
		background-repeat:no-repeat;
		float:right;
		width:20px;
	}
   .planche {
      border:dashed 3px white;
      background-color:black;
      position:absolute;
      z-index:1000;
   }
  
</style>

<script language="Javascript">

$(function(){
    $('#idFilter').autocomplete({
       source:"GestionTagAction.php?action=2",
       minLength:2,
       select:function(event,ui){
		// On verifie s'il existe
		if(GestionTag.isTagExist(ui.item.id,true)){
			$('#idFilter').val('');
			event.preventDefault();
			return;
		}
         $('#idFilter').val('');
		GestionTag.addTag(ui.item.id,ui.item.label);
         event.preventDefault();
       },
		close:function(){GestionTag.autocompleteOpen=false;},
		open:function(){GestionTag.autocompleteOpen=true;},
      response:function(event,ui){
         console.log(ui);
      }
    });

   $('#idFilter').keypress(function(event){
      if(event.keyCode == 9){
        GestionTag.setNextTypeTag ();
         event.stopPropagation();
         return false;
      }
	// Cas de l'ajout d'un nouvel element (pas de resultat dans la liste et ajout possible)
	if(event.keyCode == 13){
		if($('#idFilter').val()!='' && GestionTag.autocompleteOpen == false && GestionTag.isNewPossible()){
			GestionTag.addNewTag($('#idFilter').val());
			$('#idFilter').val('');
		}
	}
   });

   $('#choice > :radio').click(function(){
      GestionTag.setType($(this));
   });

	
var GestionTag = {
	types:$('#choice > :radio'),
	autocompleteOpen:false,
	idTag:null,	// Id du tag courant
	nbTotal:0,	// Nb total de photos
	nbTotalTags:0,	// Nb total de photos taggees
	options:{
		P:{
			type:"P",
			addUrl:"GestionTagAction.php?action=14",
			deleteUrl:"GestionTagAction.php?action=10",
			searchUrl:"GestionTagAction.php?action=2",
			classImg:"icon_users icon"
		},
		M:{
			type:"M",
			addUrl:"GestionTagAction.php?action=15",
			deleteUrl:"GestionTagAction.php?action=11",
			searchUrl:"GestionTagAction.php?action=3",
			classImg:"icon_bike2 icon"
		},
		K:{
			type:"K",
			addUrl:"GestionTagAction.php?action=16",
			deleteUrl:"GestionTagAction.php?action=12",
			searchUrl:"GestionTagAction.php?action=4",
			classImg:"icon_keyword icon",
			newPossible:true,
			createNewUrl:'GestionTagAction.php?action=6'
		},
		L:{
			type:"L",
			addUrl:"GestionTagAction.php?action=17",
			deleteUrl:"GestionTagAction.php?action=13",
			searchUrl:"GestionTagAction.php?action=5",
			classImg:"icon_location icon",
			newPossible:true,
			createNewUrl:'GestionTagAction.php?action=7'
		}
	},
	/* Renvoie les options propre au bouton radio selectionne */
	getCurrentOption:function(){
		var options = this.types.parent().find(':radio:checked').data("options");
		return (options!=null)?options:{};		
	},
	getOption:function(key){
		return this.options[key];
	},
	/* Verifie que le tag n'existe pas dans la liste lors de l'ajout par l'outil d'autocomplete */
	isTagExist:function(idTagElement,highlight){
		var index = this.getCurrentOption().type + '-' + idTagElement;
		var div = $('#idContent > div[data-index="' + index + '"]');
		if(div.length > 0){
			if(highlight){
				div.animate({backgroundColor:'#FA8072'},400,function(){$(this).animate({backgroundColor:'white'},2000);})
			}
			return true;
		}
		return false;
	},
	setTypeTag:function(idType){
		$.ajax({url:"GestionTagAction.php",data:{action:18,idTag:this.idTag,id:idType},dataType:'json',success:function(data){

		}});
	},
	setCommentaire:function(commentaire){
		$.ajax({url:"GestionTagAction.php",data:{action:19,idTag:this.idTag,commentaire:commentaire},type:'post',dataType:'json',success:function(data){

		}});
	},
	/* Ajoute un tag dans la liste */	
	addTag:function(id,value){
		var opt = this.getCurrentOption();
		if(opt == null){return;}
		$.ajax({url:opt.addUrl,data:{idTag:this.idTag,id:id},dataType:'json',success:function(data){
			if(data!=null && data.message == "ok"){
				GestionTag.addTagIG(opt,id,value,true);
			}
		}});
		
	},
	removeFromTag:function(option,id){
		$.ajax({url:option.deleteUrl,dataType:'json',data:{idTag:this.idTag,id:id},success:function(data){
			if(data!=null && data.message == 'ok'){
				var index = option.type + '-' + id;
				$('#idContent > div[data-index="' + index + '"]').remove();
			}
		}});
	},
	addTagIG:function(option,id,value,highlight){
		if($('#idContent > div').length == 0 && highlight == true){
			// on incremente le compteur
			this.nbTotalTags++;
			$('#idNbTags').text(this.nbTotalTags);
			$( "#idProgressBar" ).progressbar({
		       value: this.nbTotalTags/this.nbTotal*100
    		});
		}
		var deleteSpan = $('<div class="delete">&nbsp;</div>').click(function(){
			GestionTag.removeFromTag(option,id);
		});
		var div = $('<div data-type="' + option.type + '" data-value="' + value + '" data-index="' + option.type + '-' + id + '">' + value + '</div>').append(deleteSpan);
		if(option.classImg!=null){
			div.addClass(option.classImg);
		}
		// On cherche la position
		var tab = $('#idContent').find('div[data-type="' + option.type + '"]');
		if(tab.length == 0){
			// On ajoute a la fin
			$('#idContent').append(div);
		}
		else{
			// On parcourt les elements pour trouver la place
			var done = false;
			tab.each(function(){
				if(!done && $(this).data("value")>value){
					$(this).before(div);
					done = true;
				}
			});
			if(!done){
				// On ajoute a la fin
				$(tab.get(tab.length -1)).after(div);
			}
		}
		if(highlight!=null){
			div.css('background-color','salmon').animate({backgroundColor:'white'},1000);
		}
		
	},
	resetTag:function(){
		$('#idContent').empty();
	},
	addNewTag:function(label){
		if(!this.isNewPossible()){return;}
		var _self = this;
		$.ajax({url:this.getCurrentOption().createNewUrl,data:{value:label},dataType:'json',type:'get',success:function(data){
			if(data!=null && data.id!=null){
				_self.addTag(data.id,label);
			}
		}});
	},
   duplicateTags:function(nb){
      var media = $('#idMedias').val();
      $.ajax({
         url:"GestionTagAction.php",
         dataType:'json',
         data:{action:20,idMedia:media,nbFrom:nb-1,idTagTo:this.idTag},
         success:function(data){
            if(data!=null && data.nb!=null){
               // on recharge le tag
               $('#idSelectPhoto').change();
            }
         }
      })

   },
	init:function(){
		var _self = this;
		this.types.each(function(){
			if(_self.options[$(this).data('type')] != null){
				$(this).data("options",_self.options[$(this).data('type')]);
			}
		});
	},
	/* Indique si le type de taf permet l'ajout d'une nouvelle valeur */	
	isNewPossible:function(){
		return this.getCurrentOption().newPossible == true && this.getCurrentOption().createNewUrl!=null;
	},
	setNextTypeTag:function(){
	  	var length = this.types.length;
	  	var pos = ((length - $(':radio:checked~:radio',this.types.parent()).length)%length);
        var radio = $(this.types.get(pos));
        this.setType(radio);

   },
	setType:function(input){
		input.attr('checked','checked');
        $('#idFilter').autocomplete('option','source',this.getCurrentOption().searchUrl);
        $('#idFilter').val('').autocomplete('search','').focus();
	},
	displayTag:function(tag){
			this.resetTag();
			this.idTag = tag.id;
			$('#idPreview').attr('src',tag.path);
			$('#idCommentaire').val(tag.commentaire);	
			$(tag.motos).each(function(){GestionTag.addTagIG(GestionTag.getOption('M'),this.id,this.label);});
			$(tag.personnes).each(function(){GestionTag.addTagIG(GestionTag.getOption('P'),this.id,this.label);});
			$(tag.motsCles).each(function(){GestionTag.addTagIG(GestionTag.getOption('K'),this.id,this.label);});
			$(tag.lieux).each(function(){GestionTag.addTagIG(GestionTag.getOption('L'),this.id,this.label);});		
			$('#idTypeTag > :radio').removeAttr('checked');
			$('#idTypeTag > :radio[value="' + tag.type + '"]').attr('checked','checked');
	}
	
	
};
GestionTag.init();
$('#idGroups').change(function(){
	$('#idMedias > option:not(:first)').remove();
	if($(this).val() == ''){return;}
	$.ajax({
		url:'MediaAction.php',
		data:{idGroup:$(this).val(),action:1},
		type:'post',
		dataType:'json',
		success:function(data){
			$(data.timeline.date).each(function(){
				$('#idMedias').append('<option value="' + this.data + '">' + this.headline + '</option>');
			});
		}
	});
});

$('#idMedias').change(function(){
	if($(this).val() == ''){return;}
	$.ajax({
		url:"GestionTagAction.php",
		data:{action:9,idMedia:$(this).val()},
		dataType:'json',
		success:function(data){
			if(data==null){return;}
			GestionTag.displayTag(data.tag);
			$('#idSelectPhoto').empty();
			for(var i = 0 ; i < data.nb ; i++){
				$('#idSelectPhoto').append('<option value="' + i + '">' + (i+1) + '</option>');
			}
			$('#idNbTags').text(data.nbTags);
			$('#idNbTotal').text(data.nb);
			GestionTag.nbTotal=data.nb;
			GestionTag.nbTotalTags=data.nbTags;
			$( "#idProgressBar" ).progressbar({
		       value: data.nbTags/data.nb*100
    		});
		}
	});
});

$('#idSelectPhoto').change(function(){
	if($(this).val() == ""){return;}
	$.ajax({
		url:"GestionTagAction.php",
		data:{action:8,nb:$(this).val(),idMedia:$('#idMedias').val()},
		dataType:'json',
		success:function(data){
			if(data==null){return;}
			GestionTag.displayTag(data);
		}
	});
});

$('#idPreviousTag').click(function(){
	$('#idSelectPhoto > option:checked').prev().attr('selected','selected');
	$('#idSelectPhoto').change();
});

$('#idNextTag').click(function(){
	$('#idSelectPhoto > option:checked').next().attr('selected','selected');
	$('#idSelectPhoto').change();
});

/* Gestion du type */
$('#idTypeTag > :radio[name="type"]').click(function(){
	GestionTag.setTypeTag($(this).val());
});

$('#idCommentaire').change(function(){
	GestionTag.setCommentaire($(this).val());
});

$("#idDuplique").click(function(){
   GestionTag.duplicateTags($('#idNbTag').val());

});

var currentUrl = null;
var currentPage = null;
var currentId = null;
function loadPlanche(pageDecalage){
   if(currentId!=$('#idMedias').val()){
   		currentPage = null;	// Initialise la pagination
   }
   var url = "planche_contact.php?id=" + $('#idMedias').val();
   currentId =  $('#idMedias').val();
   var nb = $('#idSelectPhoto > option').length;
   if(nb > 100){
		if(currentPage!=null && pageDecalage!=null){
            currentPage=Math.max(Math.min(Math.ceil(nb/100),pageDecalage+currentPage),1);
   		}
		else{   		
	      currentPage = Math.floor($('#idSelectPhoto').val()/100) + 1;
	    }
      url+="&page=" + currentPage;
      $('#pagination').show();
   }
   else{
      $('#pagination').hide();
   }
   return url;
}


$('#idPlanche').click(function(){
	if($('#idMedias > option:selected').length == 0){return;}
   
	if($('#idDivPlanche').length == 0){
		var div = $('<div id="idDivPlanche" class="planche" style="display:none;color:white">'
         + '<button style="float:right">Fermer</button><br/>'
         + '<div id="idContainerImage"><img style="cursor:pointer" src=""/></div></div>');
		div.find('>:button').click(function(){div.hide();});
		div.find('>:button').after('<span id="pagination"><button>&lt;&lt;</button><button>&gt;&gt;</button></span>');
		div.find('#pagination > :button:last').click(function(){
			$('#idContainerImage').empty().append('<img style="cursor:pointer" src=""/>');
			$('#idContainerImage > img').attr('src',loadPlanche(1));
		});
		div.find('#pagination > :button:first').click(function(){
			$('#idContainerImage').empty().append('<img style="cursor:pointer" src=""/>');
			$('#idContainerImage > img').attr('src',loadPlanche(-1));
		});
		$('#idContainerImage').find('img').live('click',function(e){
			var x = Math.floor((e.clientX - div.position().left)/85);
			var y = Math.floor((e.clientY - div.position().top - $(this).position().top)/60);
			var nb = y*10 + x + Math.max(0,currentPage-1)*100;
         if($('#idSelectPhoto > option[value="' + nb + '"]').length > 0){
				$('#idSelectPhoto > option[value="' + nb + '"]').attr('selected','selected');
				$('#idSelectPhoto').change();
				div.hide();
			}
		});
		div.draggable();
		$('body').append(div);
	}

// On recupere la page courrante et le nombre
   var url = loadPlanche();
  
	$('#idDivPlanche').show();
   /* Evite de recharger l'image */
   if(url == currentUrl){return;}
   currentUrl = url;
	$('#idContainerImage').empty().append('<img style="cursor:pointer" src=""/>');
	$('#idContainerImage > img').attr('src',url);
});

});

</script>

<div id="idSelection" style="float:right;margin-right:10px;">
	<select id="idGroups" style="width:250px">
	<option value="">-----------</option>
	<?
		$dao = new MediaDao();
		$groups = $dao->getGroups();
		foreach($groups as $id => $libelle){
			echo "<option value=\"" . $id . "\">" . $libelle . "</option>";
		}
	?>
	</select>
	<select id="idMedias" style="width:250px">
		<option value="">-----------</option>
	</select>
</div>

<!--<div style="clear:both"></div>-->
<table style="width:100%"><tr><td style="width:400px;">
<div style="">
	<div id="choice" class="choice_icone">
	   <input type="radio" name="req" checked="checked" data-type="P"> <span class="icon_users icon" title="Personne"></span>
	   <input type="radio" name="req" data-type="M"> <span class="icon_bike2 icon" title="Motos"></span>
	   <input type="radio" name="req" data-type="L"> <span class="icon_location icon" title="Lieux"></span>
	   <input type="radio" name="req" data-type="K"> <span class="icon_keyword icon" title="Mots cles"></span>
	   <input type="text" id="idFilter" style="font-size:12px;width:180px;" placeholder="Tapez un mot..."/>
	</div>
	<div style="width:400px;">
		<img src="" id="idPreview" style="width:100%;max-height:500px"/>
	</div>
	<div id="idTypeTag" class="choice_icone">
		<input type="radio" name="type" value="0"/><span class="icon_group_people icon" title="Groupe"></span>
		<input type="radio" name="type" value="1"/><span class="icon_people icon" title="Portrait"></span>
		<input type="radio" name="type" value="2"/><span class="icon_paysage icon" title="Paysage"></span>
		<input type="radio" name="type" value="3"/><span class="icon_bike2 icon" title="Motos"></span>
		<input type="radio" name="type" value="4"/><span class="icon_group_bike icon" title="Groupe motos"></span>
		<input type="radio" name="type" value="5"/><span class="icon_visite icon" title="Visite"></span>		
		<input type="radio" name="type" value="6"/><span class="icon_autres icon" title="Autres"></span>
		
		
	</div>
	<div>
		<textarea rows="3" style="width:400px;" placeholder="Un commentaire ici..." id="idCommentaire"></textarea>
	</div>
	<div id="idNavigate">
		<button id="idPreviousTag">&lt;</button>
		<select style="width:70px;" id="idSelectPhoto"></select>
		<button id="idNextTag">&gt;</button>
		<button id="idPlanche">Planche</button>
	</div>
   <div>Dupliquer a partir du tag n° : <input type="text" id="idNbTag" size="3"/><button id="idDuplique">Ok</button></div>
	<table>
	<tr>
	<td style="font-size:12px;"><span id="idNbTags">0</span>&nbsp;/&nbsp;<span id="idNbTotal">0</span></td>
	<td style="width:100%"><div id="idProgressBar" style="height:12px;width:100%"></div></td>
	</tr></table>

</div>

</td><td style="width:100%;vertical-align:top">

<div id="idContent" class="choice_icone" style="margin-top:35px;"></div>
</td></tr></table>







<? writeFooter(); ?>
