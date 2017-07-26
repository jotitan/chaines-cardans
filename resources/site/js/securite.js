/** Gestion de la securite */

/* Connecte l'utilisateur */
function login(login,mdp){
	
	$.ajax({url:"SecuriteAction.php",dataType:"json",data:{action:1,login:login,mdp:MD5(mdp)},success:function(data){
		if(data!=null && data.message == 'ok'){
			location.href = location.href.replace('logout=true','');
		}
		else{
			$(':text[name]','#idLoginBox').css('background-color','#ff8c8c').bind('focus',function(){
				$(this).animate({backgroundColor:'white'},500);
				$(this).unbind('focus');		
			});
		}
	}});

}

function sendLogin(){
	if($(':text[name][value!=""],:password[name][value!=""]','#idLoginBox').length!=2){
		$(':text[name][value=""]','#idLoginBox').css('background-color','#ff8c8c').bind('focus',function(){
			$(this).animate({backgroundColor:'white'},500);
			$(this).unbind('focus');		
		});
		return;
	}
	login($(':text[name="login"]','#idLoginBox').val(),$(':password[name="mdp"]','#idLoginBox').val());
	
}

/* Affiche la fenetre de login */
function showLoginBox(){
	if($('#idLoginBox').length == 0){
		var loginBox = $('<div style="position:absolute;width:100%;height:1px;z-index:1000">'
			+ '<div id="idLoginBox" class="menu-box" style="display:none;"><form onsubmit="sendLogin();return false;">'
			+ '<span style="width:50px;">Login : </span> <input type="text" name="login" size="12"/><br/><span style="width:50px;">Pass : </span> <input type="password" name="mdp" size="12"/><br/>'
			+ '<div style="float:right"><input type="submit" value="Valider"/><button style="margin-left:5px;" onclick="closeLoginBox()">Annuler</button></form></div>'
			+ '</div></div>');
		$('#idBody').prepend(loginBox);
	}
	$('#idLoginBox').slideDown(function(){$('input[name="login"]','#idLoginBox').focus();});
}

function showMenuBox(){
	$('#idMenuCompte').slideDown();
}

/* Ferme la fenetre de login */
function closeLoginBox(){
	$('#idLoginBox').slideUp();
}
