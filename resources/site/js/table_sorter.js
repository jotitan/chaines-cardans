/* Permet de trier un tableau et d'ajouter un ascenseur latérale pour le défilement. 
/* @param idHeader : Identifiant du header du tableau
/* @param scroll : boolean, indique s'il faut activer le défilement
/* @param sort : boolean, indique s'il faut activer le tri alphabétique sur les colonnes
/* @param height : hauteur du tableau, obligatoire si scroll activte
/* @param width : largeur du tableau
/* @param classHeader : class css du header
*/
$.fn.sorter = function(options){
    var current = $(this);
    var currentObject = this;
	var header = $(this).find('tr:first')
	options = $.extend({},{classHeader:'header'},options);	
    this.scroller = false;  // Indique que le scroller est en place
	/* On cree un scroll */
	
    this.init = function(){
        if(options.scroll){
    		$(this).wrap('<div style="position:relative"></div>');
    		
    		// on créé un div pour le header et on déporte le header
    		var headerId = generateUniqueId('idDivHeader');
    		$(this).before('<div><table class="' + options.classHeader + '" id="' + headerId + '" style="width:' + options.width + 'px"></table></div>');
    		$('#' + headerId).append(header);
    		
    		var wrapper = $('<div class="wrapper-table" style="overflow:hidden;width:' + (parseInt(options.width)) + 'px;max-height:' + options.height + 'px"></div>');
    
    		// On encadre d'un div central
    		$(this).wrap(wrapper);
    		
    		// On agrandit la derniere colonne pour permettre le placement de l'ascenseur
    		header.find('th:last').css('width',parseInt(header.find('th:last').css('width')) + 20);
    		
    		// On copie les styles du header sur la premiere ligne du tableau
    	    this.resizeColumn();
          // S'il faut mettre en place un scroll
          this.putScroller();
    		
            // On retaille les cellules du tableau en cas d'update (lance lors du tri)
    		$(this).bind('update',function(){
    			currentObject.resizeColumn();
                // On verifie si un scroll est necessaire
                currentObject.putScroller();
    		});    		
    	}
    	
    	if(options.sort){
    		header.find('th').css('cursor','pointer');
    		header.find('th').each(function(i){
    			$(this).click(function(){
    				var index = i+1;
    				if(this.order == null){
    					this.order = 0;
    				}
    				this.order=(this.order+1)%2;
    				var order = this.order;
    				current.find('tr').each(function(){
    					var td = $(this).find('td:nth-child(' + index + ')');
    					var val = td.text();
    					var trs = current.find('tr');
    					for(var i = 0; i< trs.length ; i++){
    						if((order == 0 && $(trs.get(i)).find('td:nth-child(' + index + ')').text() <val)
    							|| (order == 1 && $(trs.get(i)).find('td:nth-child(' + index + ')').text() >val)){
    							$(trs.get(i)).before(td.parent());
    							break;
    						}
    					}
    				});
    			});
    		});
    	}
    }
    
    /* Verifie si un scroller est necessaire et le cree */
    this.putScroller = function(){
        if(($(this).height() > $(this).parent().height() || options.forceScroll == true) && this.scroller == false){
            var wrapper = $(this).parent();
        	// On gere le scroll a la main
        	wrapper.append('<div class="scroller" style="position:absolute;width:12px;height:' + (options.height-25) + 'px;top:35px;left:' + (options.width-20) + 'px;"></div>');
        	$('.scroller',wrapper).fadeTo(100,0.5).slider({min:0,max:100,value:100,orientation:'vertical',slide:function(event,ui){
        		currentObject.slideWrapper(wrapper,ui.value);
                /*var shift = ((100-ui.value)*(wrapper.find('table:first').height()-wrapper.height()))/100;
        		wrapper.scrollTop(shift);*/
        	}}).mouseover(function(){
        		$(this).stop().fadeTo(300,1);
        	}).mouseout(function(){
        		$(this).stop().fadeTo(600,0.5);
        	});
            this.scroller = true;
            /* Gestion de la molette pour faire défiler les listes */
            $(this).unbind('mousewheel').bind('mousewheel', function(event, delta) {
                var value = $('.scroller',wrapper).slider('value');
                value += (value == 0 && delta<0)?0:(value == 100 && delta > 0)?0:delta*10;
                $('.scroller',wrapper).slider('value',value);
                currentObject.slideWrapper(wrapper,value);
                return false;
            });
        }
    }
    
    /* Fait glisser le div dans le wrapper (lors de l'utilisateur de la molette) */
    this.slideWrapper = function(wrapper,value){
        var shift = ((100-value)*(wrapper.find('table:first').height()-wrapper.height()))/100;
        wrapper.scrollTop(shift);
    }
    
    this.resizeColumn = function(){
        header.find('th').each(function(i){
        	//$(current.find('tr:first>td').get(i)).css('width',$(this).css('width'));
            $(current.find('tr:first>td').get(i)).css('width',$(this).outerWidth(true));
        });   
    }
        
    this.init();
	
};


function generateUniqueId(prefix){
	var id = prefix + Math.round(Math.random()*1000);
	if($('#' + id).length >0){
		return generateUniqueId(prefix);
	}
	return id;
}
