/* Gestion du chat */


var GestionChat = {
   DELAY_MESSAGES:2000,
   DELAY_GET_USERS:5000,
	/* Div representant la fenetre de chat*/
	div:null,
	divMessages:null,
	lastReadId:null,
	connected:false,
   login:null, // Login de connexion
	/* Indique qu'une requete de recuperation des messages est en cours */
	REQUEST_IN_PROGRESS : false,
	/* Liste des commandes utilisables*/
   commands:{
      "nick":function(value){GestionChat.changeNick(value);},
      "clear":function(){GestionChat.divMessages.empty();},
      "time":function(){
         var d = new Date();
         GestionChat.showAdminMessage("Il est " + ((d.getHours()<10)?0:'') + d.getHours() + ':' + ((d.getMinutes()<10)?0:'') + d.getMinutes());
      },
      "exit":function(){GestionChat._doLogout();},
      "whois":function(login){GestionChat.whois(login);},
      "help":function(){
         var aide = "Liste des commandes disponibles : ";
         for(var c in GestionChat.commands){aide+=c + " ";}
         GestionChat.showAdminMessage(aide);
      }
   },
   /* Gere l'historique des messages envoyes */
   history:{
      list:new Array(),
      currentIterator:null,
      put:function(value){
         if(value == null || value == this.getLast()){return;}
         this.list.push(value);
      },
      getLast:function(){
         if(this.list!=null && this.list.length > 0){
            return this.list[this.list.length -1];
         }
         return null;
      },
      getCurrentIterator:function(){
         if(this.currentIterator == null){
            this.currentIterator = this.iterator();
         }
         return this.currentIterator;
      },
      reset:function(){
         this.currentIterator = null;
      },
      previous:function(){
         return this.getCurrentIterator().previous();
      },
      next:function(){
         return this.getCurrentIterator().next();
      },
      iterator:function(){
         var list = this.list;
         return {
            innerList:list,
            pointer:list.length,
            previous:function(){
               if(this.pointer<=0){return null;}
               this.pointer--;
               return this.innerList[this.pointer];
            },
            next:function(){
               if(this.pointer>=this.innerList.length -1){
                  this.pointer=this.innerList.length;
                  return '';
               }
               this.pointer++;
               return this.innerList[this.pointer];
            }

         };
      }
   },
   /* Affiche le bouton de connexion */
   showButton:function(){
      if($('#idButtonOpenChat').length == 0){
         $('#idBody').append('<div class="div_chat_action" id="idButtonsChat"><button id="idButtonOpenChat" class="chat">Chat</button></div>')
         $('#idButtonOpenChat').bind('mouseup',function(){GestionChat.show();});
      }
   },
	/* Montre la fenetre de chat */
	show : function(){
      //si pas connecte on connecte
		if(!this.connected){
			this.connect();
			return;
		}
      if(this.div == null){
			this.create();
      }
		this.div.slideDown('slow',function(){
         $('#idButtonOpenChat').text('Fermer').unbind('mouseup').bind('mouseup',function(){GestionChat.hide();}).animate({
            marginBottom:108},200
         );
      });
		$('#idFooter').addClass("chat_active");		
	},
	/* Cache la fenetre de chat */
	hide : function(){
      this.div.slideUp('slow',function(){
         $('#idButtonOpenChat').text('Ouvrir').unbind('mouseup').bind('mouseup',function(){GestionChat.show();}).animate({
            marginBottom:0},200
         );
      });
		$('#idFooter').removeClass("chat_active");
	},
	/* Cree la fenetre de chat */
	create:function(){
		this.div = $('<div class="chat" style="display:none"></div>');
		$('#idBody').append(this.div).css('marginBottom','108px');
		$('#idButtonsChat').prepend('<button id="idQuit">Quitter</button>');
		$('#idQuit').click(function(){
			GestionChat.logout();			
		});
		this.divMessages = $('<div class="messages" style="border:0;width:100%;height:77px;overflow:auto;background-color:white">');
		var blocMessages = $('<div style="float:left;width:89%;height:100%"></div>');
		var saisieMessage = $('<input type="text" id="idMessage" disabled="disabled" style="width:100%;height:15px;margin-top:5px;border:0"/>');
		saisieMessage.bind('keypress',function(event){
         if(event.keyCode == 13){
				// On envoie le message
            GestionChat.processMessage($(this).val());
				$(this).val('');
            GestionChat.history.reset();  // Reinitialise le parcours d'historique
			}
         if(event.keyCode == 38){
            // Instruction precedente
            var val = GestionChat.history.previous();
            $(this).val((val!=null)?val:$(this).val());
         }
         if(event.keyCode == 40){
            // Instruction suivante
            var val = GestionChat.history.next();
            $(this).val((val!=null)?val:$(this).val());
         }
		});
		blocMessages.append(this.divMessages).append(saisieMessage);
		this.div.append(blocMessages);

		this.div.append('<div style="float:right;width:10%;height:100%"><select id="usersList" multiple style="background-color:white;width:100%;height:100%;font-size:12px"></select></div>');
		this.startGetUsers();
	},
   /* Gere l'analyse et l'envoi de message */
   processMessage:function(message){
      if(message == ''){return;}
      // On enregistre le message dans l'historique
      this.history.put(message);
      // Commence par / => instruction
      if(message.indexOf('/') == 0){
         var command = (message.indexOf(" ")!=-1)?message.substr(1,message.indexOf(" ")-1):message.substr(1);
         if(this.commands[command]!=null){
            var value = message.replace("\/" + command + " ","");
            this.commands[command](value);
         }
         else{
            this.showAdminMessage("Commande " + command + " inconnue.");
         }
         return;
      }
      this.sendMessage(message);
   },
   /* Change le nom de l'utilisateur */
   changeNick:function(login){
      $.ajax({
         url:'GestionChatAction.php',
         data:{action:6,login:login},
         dataType:'json',
         success:function(data){
            if(data!=null && data.error!=null){
               alert(data.error);
               return;
            }
            if(data!=null && data.login!=null){
               GestionChat.login = data.login;
            }
         }
      });
   },
	/* Recupere les messages sur le serveur*/
	getMessages:function(){
      // on ne relance pas tant qu'il y a une requete en cours
		if(GestionChat.REQUEST_IN_PROGRESS == true){return;}
		GestionChat.REQUEST_IN_PROGRESS = true;
		$.ajax({
			url:'GestionChatAction.php',
			data:{action:1,lastId:this.lastReadId},
			dataType:'json',
			success:function(datas){
				if(datas==null || datas.messages == null || datas.messages.length == 0){GestionChat.REQUEST_IN_PROGRESS = false;return;}
				GestionChat.showMessages(datas.messages);
				GestionChat.REQUEST_IN_PROGRESS = false;
			},
			error:function(){
				GestionChat.REQUEST_IN_PROGRESS = false;
			}
		});
	},
   /* Recupere les utilisateurs connectes */
   getUsers:function(){
      // Quand les utilisateurs sont nouveaux, on affiche la connexion, si se deconnecte, on l'affiche
      $.ajax({
         url:'GestionChatAction.php',
         dataType:'json',
         data:{action:2},
         success:function(data){
            // Liste temp a partir des users existants
            var actuals = new Array(); // Map
            var actualsId = new Array();  // Liste des ids
            $('#usersList > option').each(function(i,option){
                  actuals[$(option).val()] = 1;
                  actualsId[i] = $(option).val();
            });
            $(data.users).each(function(i,e){
               // Verifie s'il existe
               if(actuals[e.id]!=null && actuals[e.id] == 1){
                  actuals[e.id] = 0; /* Existe deja */
                  // on verifie si le login change
                  if($('#usersList > option[value="' + e.id + '"]').text() != e.user){
                     GestionChat.showAdminMessage($('#usersList > option[value="' + e.id + '"]').text() + " change de nom en " + e.user);
                     $('#usersList > option[value="' + e.id + '"]').text(e.user);
                  }
               }
               else {
                  /* on le cree */
                  $('#usersList').get(0).options[$('#usersList > option').length] = new Option(e.user,e.id);
                  GestionChat.showAdminMessage(e.user + " vient de se connecter.");
               }
            });
            // On parcours les users qui reste à 1
            $(actualsId).each(function(i,e){
               if(actuals[e]!=null && actuals[e] == 1){
                  // on le supprime
                  GestionChat.showAdminMessage($('#usersList > option[value="' + e + '"]').text() + " s'est déconnecté");
                  $('#usersList > option[value="' + e + '"]').remove();
               }
            });
            // Si je suis le dernier connecte (moins de deux utilisateurs), on coupe la thread des messages
            if($('#usersList > option').length <=1){
               GestionChat.stopGetMessages();
            }
            else{
               GestionChat.startGetMessages();
            }
         }
      });
   },
   /* Format un message envoye par un user */
   formatMessage:function(message){
   		return message.replace(/http:\/\/([^ ]*)/gi,"<a target=\"_blank\" title=\"Aller...\" href=\"http://$1\">http://$1</a>");
   },
	showMessages:function(messages){
		$(messages).each(function(){
			GestionChat.divMessages.append('<p>' + this.user + ' : ' + GestionChat.formatMessage(this.message) + '</p>');
         GestionChat.lastReadId = this.id;
      });
		this.slideDown();
      /* Si le bloc est ferme, on fait clignoter le bouton */
	},
   /* Affiche des messages d'administration */
   showAdminMessage:function(message){
      GestionChat.divMessages.append('<p style="font-style:italic">* ' + message + '</p>');
      $('#idMessage').focus();
      this.slideDown();
   },
   /* Deroule la fenetre des messages */
   slideDown:function(){
      GestionChat.divMessages.scrollTop(GestionChat.divMessages.get(0).scrollHeight - GestionChat.divMessages.height());
   },
	startGetMessages:function(){
      if(GestionChat.threadMessage == null){
         GestionChat.threadMessage = setInterval(function(){GestionChat.getMessages();},GestionChat.DELAY_MESSAGES);
         $('#idMessage').removeAttr('disabled');
      }
	},
   /* Arrete la recuperation des messages */
   stopGetMessages:function(){
      if(GestionChat.threadMessage!=null){
         clearInterval(GestionChat.threadMessage);
         GestionChat.threadMessage = null;
         $('#idMessage').attr('disabled','disabled');
      }
   },
   startGetUsers:function(){
		GestionChat.threadUsers = setInterval(function(){GestionChat.getUsers();},GestionChat.DELAY_GET_USERS);
   },
   stopGetUsers:function(){
   	if(GestionChat.threadUsers!=null){
         clearInterval(GestionChat.threadUsers);
         GestionChat.threadUsers = null;
      }
   },
   /* Envoie un message. Il n'est pas affiche dans la liste, mais le sera lors de la recuperation des messages */
   sendMessage:function(message){
      $.ajax({
         url:'GestionChatAction.php',
         dataType:'json',
         data:{action:3,message:message},
         success:function(data){
            $('#idMessage').focus();
         }
      });      
   },
   /* Methode permettant de loggue l'utilisateur. On lui demande son login, sauf s'il est connecte */
   connect:function(){
   		// Ouvre une fenetre pour le nom
      // Si un login est deja defini, on l'utilise
      var login = (this.login!=null)?this.login:prompt("Login : ");
      if(login == null){return;}
		$.ajax({
			url:'GestionChatAction.php',
			dataType:'json',
			data:{action:4,login:login},
			success:function(data){
				if(data!=null && data.error!=null){
					alert(data.error);
               GestionChat.login = null;
					return;
				}
				if(data == null || data.id == null){return;}
            GestionChat.connected = true;
				GestionChat.show();
			}
		});
   },
   logout:function(){
	   if(confirm("Etes vous sur de vouloir quitter") == true){
	   	this._doLogout();
	   }
   },
   /* Renvoie les infos sur un utilisateur */
   whois:function(login){
      $.ajax({
         url:'GestionChatAction.php',
         dataType:'json',
         data:{action:7,login:login},
         success:function(data){
            if(data!=null){
               GestionChat.showAdminMessage(data.info + data.lastMessage);
            }
         }
      });
   },
   _doLogout:function(){
         this.stopGetMessages();
         this.stopGetUsers();
         $.ajax({
	   		url:'GestionChatAction.php',
	   		data:{action:5},
	   		dataType:'json',
	   		success:function(data){
	   			if(data!=null && data.message == 'ok'){
	   				GestionChat.div.remove();
                  GestionChat.div = null;
	   				 $('#idButtonOpenChat').text('Chat').unbind('mouseup').bind('mouseup',function(){GestionChat.show();}).animate({marginBottom:0},200);
	   				 $('#idQuit').remove();
	   			}
	   		}
	   	});
   }
}
