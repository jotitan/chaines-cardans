var currentFrom = 1;
var size = 30;
var nbTotal = 0;


function newSearch(){
   currentFrom = 1;
   launchSearch();
}

function launchSearch(){
      $('button.active','#pages').removeClass("active");
      $('button:nth-child(' + currentFrom + ')','#pages').addClass("active");
      $('#divPhotos').empty();
      $('#idLoader').css('display','');
		$.ajax({url:"MediaAction.php",
         data:{action:5,request:$('#request').val(),from:(currentFrom-1)*size,size:size},
         dataType:'json',
         success:function(data){
            if(data.message!=null){
               $('#idTotal').text("Aucun résultat");
               $('#pages').empty();
               $('#idLoader').css('display','none');
               return;
            }
            if(currentFrom == 1){
               nbTotal = data.nb;
               $('#idTotal').text(nbTotal + " photos");
               writePages(nbTotal);
            }
            displayPhotos(data,'divPhotos');
            $('#idLoader').css('display','none');
		   }
      });
}

function writePages(total){
   var nbPages = Math.ceil(total/size);
   $('#pages').empty();
   for(var i = 1 ; i <= nbPages ; i++){
      $('#pages').append('<button>' + i + '</button>');
   }
   $('button:first','#pages').addClass("active");
   $('button','#pages').click(function(){
      currentFrom = $(this).text();
      launchSearch();
   });
}

$(function(){
    $('#idPrevious').click(function(){
       if(currentFrom <= 1){return;}
       currentFrom--;
       launchSearch();
    });

    $('#idNext').click(function(){
       if(parseInt(currentFrom) +1 > Math.ceil(nbTotal/size)){return;}
       currentFrom++;
       launchSearch();
    });
});