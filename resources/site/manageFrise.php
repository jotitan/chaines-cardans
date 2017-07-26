<? 

require_once("Model.php"); 
require_once("decorator.php");  
require_once("UserDao.php");

writeHeaderSecurite("Historique moto","compte",true); ?>

<link rel="stylesheet" media="screen" type="text/css" href="css/farbtastic.css" />

<script type="text/javascript" src="js/farbtastic.js"></script>
<script type="text/javascript" src="js/upload_image.js"></script>

<style>
	.table-motos th {padding-left:5px;padding-right:5px;}
	.table-motos tr:not(:first-child):hover{
		background-color:#83bfd0;
		color:white;
		cursor:pointer;
	}
	.table-motos td > span {padding-left:10px;padding-right:10px;}
	.table-motos td {border-right:solid 1px black;}
	.table-motos {font-size:12px;}

	input.ui-button {
		padding:0.2em 0.2em;
		font-size:0.8em;
	}

</style>

<h2>Historique de mes motos</h2>

<img src="friseUser2.php?idUser=<?echo $_SESSION['user']->id; ?>" id="idFrise" style="width:835px;height:120px;"/>

<table id="idTableMotos" class="table-motos">
<tr><th></th><th>Nom</th><th>Debut</th><th>Fin</th><th><img src="img/img_icone.gif" style="height:20px;"/></th><th>Commentaire</th><th></th></tr>
<tr style="display:none" id="idTemplateMoto">
	<td data-type="color"><input type="button" name="color" style="width:50px;border-radius:10px;margin-right:10px;"/></td>
	<td data-type="moto"><span name="nom" data-size="25" style="display:block;width:300px;"></span></td>
	<td data-type="text"><span name="debut" data-size="8" style="display:inline-block;width:60px;"></span></td>
	<td data-type="text"><span name="fin" data-size="8" style="display:inline-block;width:60px;"></span></td>
  	<td data-type="photo"><img name="photo" title="Modifier" style="height:20px;"/>
		<form action="GestionImageAction.php" enctype="multipart/form-data" target="dash" method="POST">
		  <input type="file" name="photo" style="position:absolute;left:-300px;top:-200px;"/>
		  <input type="hidden" name="action" value="tmpfile"/>
		  <input type="hidden" name="resizeH" value="400"/>
		</form>
  	</td>
	<td data-type="text" style="width:250px;"><span name="commentaire" data-size="30" data-maxsize="100" style="display:inline-block;width:250px;"></span></td>
	<td onclick="GestionMotos.removeMoto(this)"><img src="img/delete.png" style="height:20px;cursor:pointer" title="Supprimer"/></td>
</tr>
</table>
<button onclick="GestionMotos.createMoto();">Ajouter une moto</button>

<div>
	<input type="hidden" id="idHiddenColorPicker" value="#000000" />
	<div id="colorSelector" style="display:none;position:absolute;background-color:white;border:solid 1px black"></div>
</div>

<div id="idPreviewPhoto">
	<img src="" style="height:200px;"/>
</div>

<div id="idPreviewHover" style="display:none;max-height:200px;position:absolute;background-color:white;border:solid 1px black;border-radius:10px;"><img src="" style="max-height:180px;padding:5px;"/></div>


<script language="Javascript">
var motos = new Array();
var idUser = <? echo $_SESSION['user']->id; ?>;
$(function(){
<?
	$dao = new UserDao();
	$motos = $dao->getMotosOfUser($_SESSION['user']->id,true);

	foreach($motos as $i => $moto){
		$color = sprintf("rgb(%d,%d,%d)",$moto->getInfo("R"),$moto->getInfo("V"),$moto->getInfo("B"));
		?>
			motos[motos.length] = new Moto(<? echo $moto->getInfo("idPeriode"); ?>,<? echo (($moto->getInfo("id")==null)?'null':$moto->getInfo("id")); ?>,'<? echo $moto->getInfo("moto"); ?>'
				,'<? echo $moto->getInfo("dateDebut"); ?>','<? echo $moto->getInfo("dateFin"); ?>','<? echo $color; ?>','<? echo $moto->getInfo("pathPhoto");?>','<? echo utf8_encode($moto->getInfo("commentaire"));?>');
		<?
	}
	
?>
    GestionMotos.init();
    GestionMotos.manageMotos(motos);
    //$(':button.design').button();
    UploadImage.init();
});

var colors = new Array();
var currentColor = "";

var farb = $('#colorSelector').farbtastic('#idHiddenColorPicker');

/*
$('input:button[id⁼"idDisplayColor"]').live('click',function(e){
	showPicker($(this));
	$('body').bind('click',function(){
		$('#colorSelector').fadeOut(200);
		$('body').unbind('click');
	});
	e.stopPropagation();
});*/



var GestionMotos = {
	table:$('#idTableMotos'),
	currentLigne:null,	// Contient la ligne qui a affiche le color picker
	/* Ajoute une moto */	
	createMoto:function(){
    $.ajax({
      url:'UserAction.php',
      dataType:'json',
      data:{action:5},
      success:function(data){
        GestionMotos.addMoto(new Moto(data.id));  
      }
    });
  },
  addMoto:function(moto){
		// On cree la moto
    if(moto == null){
      return;      
    }
    var ligne = $('#idTemplateMoto').clone().attr('id','');
		$('td',ligne).each(function(){
			var td = $(this);
			if($(this).data('type') == 'text'){
        $('span[name]',td).text(moto.infos[$('span[name]',td).attr('name')]);
        if($('span[name]',td).text() == ''){
          $('span[name]',td).html("&nbsp;");
        }
				GestionMotos.addBehaveText(td,moto);
			}
			if($(this).data('type') == 'color'){
				$('input[name="color"]',td).css('background-color',moto.infos.color);
				ligne.data('picker',$('input[name="color"]',td));
				GestionMotos.addBehaveColor(td,moto);
			}
			if($(this).data('type') == 'moto'){
				$('span[name]',td).text(moto.infos[$('span[name]',td).attr('name')]);
				if($('span[name]',td).text().trim() == ''){$('span[name]',td).html('&nbsp;');}
				GestionMotos.addBehaveMoto(td,moto);
			}
			if($(this).data('type') == 'photo'){
				var label = moto.infos[$('img[name]',td).attr('name')];		
				if(label!=null){
					$('img[name]',td).attr('src',label);
				}
				else{
 					$('img[name]',td).attr('src','img/edit.gif');
				}
				GestionMotos.addBehaveFile(td,moto);
				GestionMotos.addBehavePreviewFile(td);
			}
			
		});
		this.table.append(ligne.show());
		/* Double link */
		moto.ligne = ligne;
		ligne.data('moto',moto);
	},
	/* Ajoute le comportement pour modifier un champ text */
	addBehaveText : function(td,moto){
		$('span[name]',td).bind('click',function(){
			var span = $(this);
			var input = $('<input type="text" value="' + span.text() + '" size="' + span.data('size') + '"' + ((span.data('maxsize')!=null)?' maxLength="' + span.data('maxsize') + '" ':'') + '/>');
			input.bind('blur',function(){
				input.remove();
				span.show();
			}).bind('keypress',function(e){
				if(e.which == 13){
					span.text(input.val());
					moto.updateField(span.attr('name'),input.val());
					input.remove();
					span.show();
					e.stopPropagation();
				}
				if(e.keyCode == 27){
					input.remove();
					span.show();
				}
			});
			span.after(input).hide();
			input.focus();           			
		});
	},
	/* Ajoute le comportement pour modifier une couleur */
	addBehaveColor : function(td,moto){
		$('input[name="color"]',td).bind('click',function(e){
			GestionMotos.showPicker(td.parent());
			$('body').bind('click',function(){
				$('#colorSelector').fadeOut(200);
				$('body').unbind('click');
			});
			e.stopPropagation();
		});		
	},
	/* Ajoute le comportement pour l autocomplete moto */
	addBehaveMoto : function(td,moto){
		$('span[name]',td).bind('click',function(){
			var span = $(this);			
			var autoInput = $('<input type="text" value="' + $(this).text() + '" size="' + $(this).data('size') + '"/>');
			autoInput.autocomplete({
				source: "UserAction.php?action=4",
				minLength: 2,
				select:function(event,ui){
				  td.parent().data("moto").updateField($('span[name]',td).attr("name"),ui.item.id,function(){
				    $('span[name]',td).text(ui.item.label).show();
				    autoInput.remove();
				  });
				  
				}
			});
			$(this).hide().parent().append(autoInput);
			autoInput.focus();
		});
		
	},
	/* Ajoute le comportement pour l'ajout d'un fichier */
	addBehaveFile : function(td,moto){
		$('img[name]',td).parent().find(':file').change(function(){
        $(this).closest("form").submit();
		GestionMotos.showLoader();
      UploadImage.checkResponse(function(chemin){
         GestionMotos.showTempImage(chemin,td);
      });
      
   });
    $('img[name]',td).click(function(){
			$(this).parent().find(':file').click();
		});
	},
	/* Affiche une miniature au survol */
	addBehavePreviewFile : function(td){
		$('img:first',td).hover(function(e){
			if($(this).attr('src').indexOf("edit.gif") != -1){return;}	// cas de l'image par defaut
			$('img:first','#idPreviewHover').attr('src',$(this).attr('src'));
			$('#idPreviewHover')
			 .css("top",(e.pageY + 20 - $('#idPreviewHover').parent().offset().top) + "px")
	         .css("left",(e.pageX + 10 - $('#idPreviewHover').parent().offset().left) + "px")
	         .fadeIn("fast");                  
		},function(){
			$('#idPreviewHover').hide();
		});
	},
	showLoader : function(){
		$('#idPreviewPhoto').prepend('<div class="loading"><img src="img/ajax-loader.gif"/>chargement</div>');
		$('#idPreviewPhoto').dialog('open');	
	},
	hideLoader : function(){
		$('#idPreviewPhoto > div.loading').remove();
	},
  showTempImage : function(chemin,td){
	GestionMotos.hideLoader();
	$('img','#idPreviewPhoto').attr('src',chemin + '?rand=' + Math.round(Math.random()*100000)).parent().css('text-align','center');
	$('#idPreviewPhoto').data('infoPhoto',{td:td,chemin:chemin});
	$('#idPreviewPhoto').dialog('open');
  },
	/* Change la photo de l'utilisateur */
	/* @param infoPhoto : content td et chemin */
	changeImgPhoto : function(infoPhoto){
		infoPhoto.td.parent().data('moto').updateField("photo",infoPhoto.chemin,function(data){
			$('#idPreviewPhoto').dialog('close');
			infoPhoto.td.find('img:first').attr('src',data.value);
		});
	},
  
	/* Supprime une moto de la frise */
	removeMoto:function(td){
		if(confirm("Voulez vous vraiment supprimer cette periode ?")){
			$(td).parent().data("moto").remove(function(){
				$(td).parent().remove();
			});
		}
	},
	manageMotos:function(motos){
		$(motos).each(function(){
			GestionMotos.addMoto(this);
		});
	},
	/* Affiche le picker */
	showPicker:function(ligne){
		this.currentLigne = ligne;
		$.farbtastic('#colorSelector').setColor(this.utils.rgbCssToHex(ligne.data('moto').infos.color));
		$('#colorSelector').css('top',ligne.data('picker').position().top + ligne.data('picker').height() + 10);
		$('#colorSelector').css('left',ligne.data('picker').position().left + ligne.data('picker').width() + 10);
		$('#colorSelector').show();
	},
  reloadFrise : function(){
    $('#idFrise').attr('src','friseUser2.php?idUser=' + idUser + '&rand=' + Math.round(Math.random()*100000));
  },
	/* Initialise plusieurs comportements comme le color picker */
	init:function(){
		var current = this;
		// Recuperation de la valeur du color picker
		$('#idHiddenColorPicker').bind('change',function(){
			var color = current.utils.hexToRbbCss($(this).val());
			current.currentLigne.data('picker').css('background-color',color);
			current.currentLigne.data('moto').setColor(color);
			current.currentLigne.data('moto').updateField('color',color);
		});
		/* Initialisation de la fenetre de preview */
		$('#idPreviewPhoto').dialog({
			autoOpen:false,
			title:"Apercu",
			buttons:{"Annuler":function(){$('#idPreviewPhoto').dialog('close');},"Enregistrer":function(){
				GestionMotos.changeImgPhoto($('#idPreviewPhoto').data('infoPhoto'));
			}}
		});
	},
	utils:{
		rgbCssToHex:function(rgb){
			var colors = /rgb\((\d{1,3}),(\d{1,3}),(\d{1,3})/.exec(rgb);
			if(colors.length!=4){return null;}
			return '#' + this.intToHex(colors[1]) + '' + this.intToHex(colors[2]) + '' + this.intToHex(colors[3]);
		},
		intToHex:function(val){
			var hex = Number(val).toString(16);
			return ((hex.length == 1)?'0':'') + hex;
		},
		hexToRbbCss:function(hex){
			return 'rgb(' + this.hexToInt(hex.substring(1,3)) + ',' + this.hexToInt(hex.substring(3,5))  + ',' + this.hexToInt(hex.substring(5,7)) + ')';
		},
		hexToInt:function(val){
			return parseInt(val,16);
		}
		
	}
	
};

function Moto(idPeriode,idMoto,nom,debut,fin,color,photo,commentaire){
	this.infos = {idPeriode:idPeriode,idMoto:idMoto,nom:nom,debut:debut,fin:fin,color:(color!=null)?color:'rgb(0,0,0)',photo:photo,commentaire:commentaire};
	this.setColor = function(color){
		this.infos.color = color;
	}
	this.updateField = function(name,value,callback){
		this.infos[name] = value;
		// Sauvegarde
		$.ajax({url:'UserAction.php',data:{action:3,id:this.infos.idPeriode,field:name,value:value},dataType:'json',success:function(data){
			if(data!=null && data.message == 'ok'){
				// On remet a jour la frise
				GestionMotos.reloadFrise();
				// Appel du callback			
				if(callback!=null){
					callback(data);
				}
			}
		}});
	}
	/* Supprime la periode */	
	this.remove = function(callback){
		$.ajax({url:'UserAction.php',data:{action:6,id:this.infos.idPeriode},dataType:'json',success:function(data){
			if(data!=null && data.message == 'ok'){
        GestionMotos.reloadFrise();
        if(callback!=null){
				  callback();
			  }
      } 
		}});
	}
	this.ligne = null;	// stocke la ligne dans le tableau
}




</script>


<? writeFooter(); ?>
