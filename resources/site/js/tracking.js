
$.tracking = {
	update:function(name){
		$.ajax({
			url:'http://vbaranzini.free.fr/v3.0/tracking.php',
			data:{name:name}
		});
	}

}
