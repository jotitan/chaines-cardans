<? 

require_once("Model.php"); 
require_once("decorator.php");
require_once("NewsDao.php");

writeHeaderSecurite("Editez news","compte",true); ?>

<script language="Javascript" src="js/upload_image.js"></script>

<div style="margin-left:20px;">
<span class="label">News : </span>
<select style="width:400px;" id="listNews">
<option style="font-style:italic;font-weight:normal">--- Sélectionnez une news</option>
<?
	$dao = new NewsDao();
	$news = $dao->getListNews();
	foreach($news as $n){
		echo sprintf("<option value=\"%d\">%s - %s</option>\n",$n->id,$n->date,$n->titre);
	}
?>
</select>

<span id="idMessage" style="font-weight:bold;display:none;font-size:14px;"></span><br/>
<span class="label">Titre : </label><input type="text" id="titre" style="width:400px;"/><br/>

<table>
	<tr>
		<td style="width: 100%;">
			<textarea id="contenu" style="width: 100%;height:300px;float:left"></textarea>
		</td>
		<td>
			<div style="float:left;width:213px;height:140px;border:solid 1px black">
			   <img src="img/add_photo.jpg" style="width:210px;height:140px;cursor:pointer" id="idPreview" title="Cliquez pour modifier / ajouter une photo"/>
			   <form action="GestionImageAction.php" enctype="multipart/form-data" target="dash" method="POST">
					  <input type="file" name="photo" style="position:absolute;left:-1300px;top:-200px;" id="idSelectPhoto"/>
					  <input type="hidden" name="action" value="tmpfile"/>
					  <input type="hidden" name="resizeW" value="210"/>
					<input type="hidden" name="resizeH" value="140"/>
				</form>
			</div>
		</td>
	</tr>
</table>
<div style="clear:both"></div>
<button id="idAddNews">Nouvelle news</button>
<button id="idDeleteNews">Supprimer news</button>
<button id="idValider" style="margin-left:50px">Valider</button>
<button id="idCancel">Annuler</button>

<script language="Javascript">
	var currentNews;
	
	var GestionNews = {
		currentNews:null,
		save : function(){
			this.currentNews.contenu = this.line2BR($('#contenu').val());
			this.currentNews.titre=$('#titre').val();
			$.ajax({
				url:'NewsAction.php',
				data:{
					action:2,
					id:this.currentNews.id,
					contenu:this.currentNews.contenu,
					titre:this.currentNews.titre,
					urlImage:(this.currentNews.changeImage!=null && this.currentNews.changeImage == true)?"true":"false"
				},
				dataType:'json',
				success:function(data){
					if(data.message == null || data.message == 'null' || data.message==0){
						$('#idMessage').css('color','red').text("Erreur lors de la création").fadeIn(500).fadeOut(3000);
					}
					else{
                  GestionNews.currentNews.changeImage = false; // Pour ne pas remodifier l'image
						if(GestionNews.currentNews.id == null){
							$('#idMessage').css('color','green').text("News créée").fadeIn(500).fadeOut(3000);						
							GestionNews.currentNews.id=data.message;
							var date = new Date();
							date = ((date.getDate()<10)?'0':'') + date.getDate() + '/' + ((date.getMonth() < 10)?'0':'') + date.getMonth() + '/' + date.getFullYear();
							$('#listNews > option:first').after('<option value="' + GestionNews.currentNews.id + '">' + date + ' - ' + GestionNews.currentNews.titre + '</option>');
							$('#listNews > option[value="' + GestionNews.currentNews.id + '"]').attr('selected','selected');
						}
						else{
							var date = $('#listNews > option[value="' + GestionNews.currentNews.id + '"]').text().substr(0,13);
							$('#listNews > option[value="' + GestionNews.currentNews.id + '"]').text(date + "" + GestionNews.currentNews.titre);
							$('#idMessage').css('color','green').text("News mise a jour").fadeIn(500).fadeOut(3000);
						}
					}
				}
			});
		},
		load:function(id){
			$.ajax({
				url:'NewsAction.php',
				data:{action:1,id:id},
				dataType:'json',
				success:function(news){
					if(news!=null && news.erreur!=null){
						alert(news.erreur);
						return;
					}
					GestionNews.display(news);
				}
			});	
		},
		display:function(news){
			this.currentNews = news;

			$('#contenu').val(this.BR2line(news.contenu));	
			$('#titre').val(news.titre);
	      	if(news.urlImage!=null && news.urlImage!=''){
         		$('#idPreview').attr('src',news.urlImage + '?rand=' + Math.round(Math.random()*100000));
      		}
      		else{
         		$('#idPreview').attr('src','img/add_photo.jpg');
      		}
		},
		create:function(){
			this.display({id:null,contenu:'',titre:'',changeImage:false});
			$('#listNews > option:first').attr('selected','selected');
		},
		cancel:function(){
			this.display(this.currentNews);
		},
		delete:function(){
			if(this.currentNews!=null && this.currentNews.id!=null){
				$.ajax({
					url:'NewsAction.php',
					data:{action:3,id:this.currentNews.id},
					dataType:'json',
					success:function(data){
						if(data!=null && data.message == "1"){
							$('#listNews > option[value="' + GestionNews.currentNews.id + '"]').remove();
							$('#listNews > option:first').attr('selected','selected');
							GestionNews.create();
							$('#idMessage').css('color','green').text("News supprimée.").fadeIn(500).fadeOut(3000);
						}
					}
				});
			}
		},
		line2BR:function(val){
			return val.replace(/\n/g,'<br/>').replace(/\*([^\*]*)\*/g,'<b>$1</b>').replace(/\[\[([^\|]*)\|([^\]\]]*)\]\]/g,"<a href=\"$1\">$2</a>");
		},
		BR2line:function(val){
			return val.replace(/<br\/>/g,"\n").replace(/<b>([^\(<\/b>)]*)<\/b>/g,'*$1*').replace(/<a href=\"([^\"]*)\">([^<]*)<\/a>/g,"[[$1|$2]]");
		}
	};
	

	$(function(){
		UploadImage.init();
		$('#listNews > option[value]').live('click',function(){
         GestionNews.load($(this).val());
		});
		
		$('#idValider').click(function(){
			GestionNews.save();
		});
		
		$('#idAddNews').click(function(){
			GestionNews.create();
		});
		
		$('#idCancel').click(function(){
			GestionNews.cancel();
		});
		
		$('#idDeleteNews').click(function(){
			GestionNews.delete();
		});

      $('#idSelectPhoto').change(function(){
         $(this).closest('form').submit();
         UploadImage.checkResponse(function(chemin){
            $('#idPreview').attr('src',chemin);
            GestionNews.currentNews.changeImage = true;
         });
      });

      $('#idPreview').click(function(){
      	if(GestionNews.currentNews!=null){
	         $('#idSelectPhoto').click();
	    }
      });
	});	
	
</script>

</div>

<? writeFooter(); ?>
