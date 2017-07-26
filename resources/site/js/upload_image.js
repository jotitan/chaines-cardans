var UploadImage = {
   /* Retourne le contenu ajax de l'iframe */
  getIframeReturnContent : function(){
    var content = $($('[name="dash"]').get(0).contentDocument.body).html();
    if(content == ''){
      throw "No content";
    }
    return $(content).html();
  },
  checkResponse:function(callback){
      var pos = 0;
      var checker = setInterval(function(){
      try{
        var content = UploadImage.getIframeReturnContent();
        clearInterval(checker);
        var response = $.parseJSON(content);
        if(response.error!=null){
          alert(response.error);
        }
        else{
          callback(response.chemin);
        }
      }catch(e){
        // On fait rien
        console.log(' re ' + (pos++));
      }
    },200);
  },
  init:function(){
      $('body').append('<iframe name="dash" style="display:none"></iframe>');
      return this;
  }
}