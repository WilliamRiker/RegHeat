<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta http-equiv="content-language" content="cs"/>
<meta http-equiv="pragma" content="no-cache"/>

<?php
if (isset($_GET['id']) && ($_GET['id'] == "home_mode"))  { ?>
<script type="text/javascript" src="client/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="client/jquery-ui.js"></script>
<script type="text/javascript" src="client/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="client/jquery.ui.datepicker-cs-CZ.min.js"></script>
<?php
}
if (isset($_GET['id']) && (($_GET['id'] == "stats_years") || ($_GET['id'] == "stats_months")))  { ?>
<script type="text/javascript" src="client/dg.js"></script>
<?php
}

if (isset($_GET['id']) && ($_GET['id'] == "home_mode")) {
?>
<script>
  $(document).ready(function() {
    $('.timedatepicker').datetimepicker();
  });
</script>
<?php
}

require_once('includes/header.php');

require_once('inc/select_functions.php');
include('inc/set_functions.php');
include('inc/delete_functions.php');
require_once('inc/insert_functions.php');
require_once('inc/speed_functions.php');
include('inc/times_temperatures.php');
require_once('inc/contents/error.php');
require_once('inc/constants.php');


if (isset($_GET['id']) && $_GET['id'] == "control_panel" && !isset($_GET['action']))  {
?>
<meta http-equiv="refresh" content="30" />
<?php
}
?>
<title>Domácí automatizace</title>

<style type='text/css'>
@import url(styles/basic.css);
<?php
if (isset($_GET['id']) && ($_GET['id'] == "home_mode")) {?>
@import url(client/jquery-ui-timepicker-addon.css);
@import url(client/jquery-ui.css);
<?php
}
?>
</style>
</head>
<?php

header("Cache-control: no-cache");
ini_set('display_errors',1); 
error_reporting(E_ALL);


//načtení soboru s třídou login
//require_once('includes/login.php');
// načtení souboru header.php pro prihlaseni


// if (speed_change()) {
//     echo "Spoustim control_temperature\n";
//     exec('cd '.ROOT_PATH.'/scripts && php control_temperature.php');  
// }			  

// Valid constant names
//define("ROOT_PATH",realpath($_SERVER["DOCUMENT_ROOT"]."/regheat/"));

?>

<body>
<?php
if ($login->is_logged==1){
// zde bude kód stránek provedený při přihlášeném uľivateli
?>

<div id="login">
<?php
if ($login->is_logged==1){
	echo "<p>Přihlášen: <strong>".$login->firstname." ".$login->lastname."</strong></p>
	<p><a href='?logout=1'>odhlásit</a></p>";
} else {
	echo "Žádný uživatel není přihlášený.";
}
?>
</div>

<div id="head">
<h1>RegHeat</h1>
</div>

<div id="nav">
<ul class="level1">
 <li class="color"><a href="index.php?id=control_panel">Kontrolní panel</a></li>
 <li class="submenu"><a href="index.php?id=times_temperatures">Topení</a>
  <ul class="level2">
    <li><a href="index.php?id=times_temperatures">Časy/teploty</a></li>
    <li><a href="index.php?id=programs">Programy</a></li>
    <li><a href="index.php?id=home_mode">Režim domácnosti</a></li>
  </ul>
 </li>
 <li class="submenu"><a href="index.php?id=stats_heating">Statistiky</a>
  <ul class="level2">
   <li><a href="index.php?id=stats_heating">topení</a></li>
   <li class="submenu"><a href="index.php?id=stats_years">teploty</a>
    <ul class="level3">
     <li><a href="index.php?id=stats_years">Roční</a></li>
     <li><a href="index.php?id=stats_months">Měsíční</a></li>
    </ul>
   </li>
  </ul>
 </li>
 <li class="submenu"><a href="index.php?id=times_temperatures">Spotřeba</a>
  <ul class="level2">
   <li><a href="">Voda</a></li>
    
  </ul>
 </li>
</ul>
</div>


<div id="edge">
<div id="main">

<?php # C O N T E N T S

if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = "control_panel";	
}
switch($id) 
{
	case "control_panel" :
		include("inc/contents/control_panel.php"); # home
		break;
	case "times_temperatures":
		include("inc/contents/schedule.php"); 
		break;
	case "home_mode":
		include("inc/contents/home_mode.php"); 
		break;
	case "manual_control":
		include("inc/contents/manual_control.php");
		break;	  
	case "stats_heating":
		include("inc/contents/stats_heating.php");
		break;	  
	case "room_det":
		include("inc/contents/room_det.php");
		break;	  
	case "programs" :
		?><h2>Nastavení programů</h2><?php
		include("inc/contents/programs.php"); 
		break;
	case "stats_years":
		include("inc/contents/years_stats.php"); 
		break;
	case "stats_months":
		include("inc/contents/months_stats.php"); 
		break;
	case 3 :
		include("inc/contents/3.php");
		break;
	case 5:
		include("inc/contents/5.php");
		break;
}


?>


</div><!-- END main  -->

<div id="clear"></div>
<div id="foot">Václav Bobek</div>
</div>

<?php
} else {
  $login->show_login_form();
}
?>

<?php
mysql_close($link);
?>
</body>
</html>
