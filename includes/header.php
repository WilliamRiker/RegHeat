<?php
// zahájení relace session
session_start();

// připojení k databázi MySQL
//include "includes/db_init.php";
include('inc/connect.php');

//načtení soboru s třídou login
include "login.php";

//inicializace třídy login
$login = new login;

//jestliže je proměnná logout nastavena na 1 provede se odhlášení
if(isset($_GET["logout"])) {
  if($_GET["logout"]==1){
	  $login->logout();
  }
}

// jestliže je odeslán přihlašovací formulář, testují se jméno a heslo
if(isset($_POST["login_name"]) and isset($_POST["login_pw"])){
	$login->first_login();
}

?>