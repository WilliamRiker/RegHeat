<?php
date_default_timezone_set('Europe/Prague');

$link = mysql_connect('localhost', 'user', 'password');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_set_charset('utf8', $link);
mysql_select_db("regheat", $link);

?>
