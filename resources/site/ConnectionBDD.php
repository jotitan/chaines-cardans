<?

/* Objet qui permet la gestion et les acces a la base de donnees */

class ConnectionBDD{
	
	function ConnectionBDD(){}

	/* Fonction pour se connecter a la base de donnees */
	function connect(){
		$connect = mysql_connect("TODO_your_sql_server","TODO_login_server","TODO_password_server");
		$db = mysql_select_db("TODO_your_database");
		return $connect;
	}

	function close(){
		mysql_close();
	}
}

?>