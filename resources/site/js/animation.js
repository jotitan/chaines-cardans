/* Animation d'une moto */
function AnimeBike(srcImageRight,srcImageLeft,top){
	this.img = null;
	this.sens = true;	// Indique sens vers la droite
	var current = this;
	this.getRandomTime = function(){
		return (Math.round(Math.random()*1000%10) + 5)*1000;
	}
	
	this.getRandomTop = function(){
		return 150 + Math.round(Math.random()*1000%($(window).height()-250));
	}

	this.init = function(){
		this.img = $('<img style="z-index:2;height:50px;display:none;position:absolute;top:' + top + 'px;left:0px" src="' + srcImageRight + '"/>');
		$('#idDivMotos').append(this.img);
		this.runAnimate();
	}	

	this.runAnimate = function(){
		setTimeout(function(){current.animate();},this.getRandomTime());
	}
	this.animate = function(){
		this.img.show().animate({left:((this.sens)?'+':'-') + '=' + ($(window).width()-80)},3000,function(){
			current.img.fadeOut(200,function(){
				current.img.css('top',current.getRandomTop() + 'px');
				// On change l'image
				current.img.attr('src',(current.sens)?srcImageLeft:srcImageRight);
				current.sens = !current.sens;
			});
			
			
		})
		this.runAnimate();
	}
	this.init();
}