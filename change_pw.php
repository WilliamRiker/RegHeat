<?php

include('inc/connect.php');
//načtení soboru s třídou login
include('includes/login.php');

/****************************************
* default user: regheat
* default passwd: regheat
*
****************************************/

$login = new login;
// Change next line and run a script
//				   user,	   password
$login->change_pw('regheat', 'regheat');
?>