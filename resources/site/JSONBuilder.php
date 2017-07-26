<?php

/* Builder JSON */
class JSONBuilder{
   var $fields = array();   // Tableau associatif
   /* @param writeEmpty : affiche tout de meme si vide */
   function set2($field,$value,$writeEmpty){
      if($field!=null && $field!="" && (($value!=null && $value!="")
         || (is_array($value) && (sizeof($value) > 0 || $writeEmpty == true)))){
         $this->fields[$field] = array($value,$writeEmpty);
      }
      return $this;  // pour permettre le chainage
   }

   function set($field,$value){
      return $this->set2($field,$value,false);
   }

   function build(){
      $pos = 0;
      $json = "{";
      foreach($this->fields as $f => $v){
         $value = $v[0];
         // cas string, tableau, objet
         if(is_array($value)){
            // String ou object
            $json.=(($pos++>0)?",":"") . "\"" . $f . "\":[";
            if(sizeof($value) > 0){
                $pos2 = 0;
                foreach($value as $elem){
                   if(is_object($elem)){
                      $json.=(($pos2++>0)?",":"") . $elem->write();
                   }
                   else{
                      $json.=(($pos2++>0)?",":"") . "\"" . $elem . "\"";
                   }
                }
            }
            $json.="]";
         }
         else if(is_object($value)){
            $json.=(($pos++>0)?",":"") . "\"" . $f . "\":" . $value->write();
         }
         else {
            $json.=(($pos++>0)?",":"") . "\"" . $f . "\":\"" . $value . "\"";
         }
      }
      $json.="}";
      return $json;
   }
}

?>