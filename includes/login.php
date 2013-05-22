<?php
/*
  Přihlašování uživatelů do webové aplikace v PHP
  autor: Pavel Beníšek
  http://www.abclinuxu.cz/clanky/programovani/prihlasovani-uzivatelu-do-webove-aplikace-v-php
  
  Modifikováno 22.5.2013
  Václav Bobek
*/

// začátek třídy login
class login {

	// hodnoty předávané při příhlašování uživatele
	var $login_name;
	var $login_pw;

	// hodnoty o uživateli
	var $id_user;
	var $firstname;
	var $lastname;

	// interní hodnoty
	var $is_logged;
	var $session_login_string;
	var $ip;
	var $last_time;

	// délka nečinosti uživatele před odhlášením
	var $checktimelimit;

	// spojení na databázi
	var $link;
	var $table;
	// konstruktor třídy obsahující inicializaci některých proměných
	
	function login(){
		
		
		if (isset($_POST["login_name"])){
			$this->login_name=htmlspecialchars($_POST["login_name"]);
		}else{
			if (isset($_SESSION["login_name"]))
			  $this->login_name=htmlspecialchars($_SESSION["login_name"]);
		}
		if (isset($_POST["login_pw"]))
		  $this->login_pw=htmlspecialchars($_POST["login_pw"]);
		
		if (isset($_SESSION["session_login_string"]))
		  $this->session_login_string=htmlspecialchars($_SESSION["session_login_string"]);
		
		// zabezpečení proti útokům typu SQL inject
		$this->session_login_string=$this->test_sql($this->session_login_string);
		$this->login_name=$this->test_sql($this->login_name);
		$this->login_pw=$this->test_sql($this->login_pw);

		global $link;
		$this->db=$link;
		$this->table="users";

		$this->ip=$_SERVER["REMOTE_ADDR"];

		// délka časového limitu v sekundách od posledního přístupu
		$this->checktimelimit=999999;

		$this->logged();
	}

	// přihlášení/odmítnutí uživatele
	function first_login(){
		if (strlen($this->login_name)>1){
			$new_pw=md5($this->login_pw);

			$query="SELECT session FROM $this->table WHERE username LIKE '".$this->login_name."' AND pw LIKE '".$new_pw."'";
			$result = @mysql_query($query,$this->db);

			if (mysql_num_rows($result)==1){
				// ok prilogovat
				$this->session_login_string=md5(uniqid(rand()));
				$query="UPDATE $this->table SET session='".$this->session_login_string."', ip='$this->ip' WHERE username='".$this->login_name."' AND pw='".$new_pw."'";
				$result = mysql_query($query,$this->db);

				//zapsání času přihlášení uživatele
				$query="UPDATE $this->table SET lasttime=now() WHERE session='".$this->session_login_string."' AND username='".$this->login_name."'";
				$result = @mysql_query($query,$this->db);

				$_SESSION["session_login_string"]=$this->session_login_string;
				$_SESSION["login_name"]=$this->login_name;

				$this->load();
				return 1;

			} else {
				//zobrazit hlasku o neuspesnem logovani
				return 0;
			}
		} else {
			// nezadano username ...
			return 0;
		}
	}

	// odhlášení uživatele
	function logout(){
		$query="UPDATE $this->table SET session='".md5(uniqid(rand()))."' WHERE session='".$this->session_login_string."' and ip='$this->ip'";
		$result = mysql_query($query,$this->db);

		$this->session_login_string=md5(uniqid(rand()));
		$this->login_name=md5(uniqid(rand()));
		session_unset();
		session_destroy();
		$this->load();
		$this->logged();
	}

	// testování zda je uživatel již přihlášen
	function logged() {
		$query="SELECT username FROM $this->table WHERE session='".$this->session_login_string."' AND username='".$this->login_name."' AND ip='".$this->ip."'  AND lasttime>=DATE_SUB(now(),INTERVAL ".$this->checktimelimit." SECOND)";
		$result = @mysql_query($query,$this->db);
		$data = @mysql_fetch_array($result);

		if (mysql_num_rows($result)==1){

			$query="UPDATE $this->table SET lasttime=now() WHERE session='".$this->session_login_string."' AND username='".$this->login_name."'";
			$result = @mysql_query($query,$this->db);

			$this->load();
			return (1);
		} else {
			return (0);
		}
	}

	// naplnění proměných
	function load(){
		$query="SELECT * FROM $this->table WHERE session='".$this->session_login_string."' AND ip='$this->ip' AND username='".$this->login_name."'";
		$result = @mysql_query($query,$this->db);
		$data = @mysql_fetch_array($result);

		if (mysql_num_rows($result)==1){
			$this->id_user=$data['id'];
			$this->firstname=$data['firstname'];
			$this->lastname=$data['lastname'];
			$this->lasttime=$data['lasttime'];

			$this->is_logged=1;
		} else {
			$this->is_logged=0;
		}

	}

	// zabezpečení proti útokům typu SQL inject
	function test_sql($teststring){
		$teststring=strtr($teststring," ","x");
		$teststring=strtr($teststring,"+","x");
		$teststring=strtr($teststring,"--","x");
		$teststring=strtr($teststring,"&","x");

		return ($teststring);
	}

	// zobrazení formuláře pro přihlášení
	function show_login_form(){
		?>
		<br>
		<br>
		<form method="POST" alt="form_prihlaseni" action="index.php">

			<table align=center>
			<tr>
				<td> Přihlašovací jméno: </td>
				<td> <input type="text" name="login_name"> </td>
			</tr>
			<tr>
				<td> Uživatelské heslo: </td>
				<td> <input type="password" name="login_pw"> </td>
			</tr>
			<tr>
				<td> <input type="submit" value="Přihlásit" alt="Přihlášení do systému"> </td>
				<td> <input type="reset" value="Zrušit" alt="Vymazat přihlašovací údaje"> </td>
			</tr>
		</table>

		</form>
		<?php
	}
	
	
	
	function change_pw($user, $pwd) {
		echo "Change pw";
		$mypw=md5($pwd);
		
		echo $mypw;
		
		$query="UPDATE users SET pw='".$mypw."' WHERE username='".$user."'";
		echo "<br>".$query;
		$result = mysql_query($query);
		
		if ($result) echo "OK";
		else echo "KO";
	}

// konec třídy login
}
?>