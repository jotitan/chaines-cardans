<?php

mysql_connect("TODO_your_sql_server","TODO_login_server",$_GET['pass']);
mysql_select_db("TODO_your_database");
$motos = mysql_query("select idMoto,nommoto from Motos");

while(($row = mysql_fetch_row($motos))!=null){
   foreach(split(" ",$row[1]) as $value){
      $value = str_replace(")","",str_replace("(","",$value));
      if(strlen($value)>1){
		 //mysql_query(sprintf("insert into moto_index (ID_INDEX,ID_MOTO,LABEL) values(null,%d,'%s')",$row[0],$value));
      }
	}
}

mysql_close();

?>
