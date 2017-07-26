<?

class DateHelper{
   static $months = array("Jan","Fev","Mar","Avr","Mai","Juin","Juil","Aou","Sept","Oct","Nov","Dec");

   static function getShortMonth($month){
      return self::$months[$month -1];
   }

}

?>
