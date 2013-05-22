<?php

/*****************************************************************************************************************************
* <Skript pro ukládání zpětné vazby z AD převodníků z modulu regheat RS485, prostřednictvím SDS MICRO>
* Copyright (C) <2012> <Václav Bobek>
*
* Tento program je svobodný software: můžete jej šířit a upravovat podle ustanovení Obecné veřejné licence GNU 
* (GNU General Public Licence), vydávané Free Software Foundation a to buď podle 3. verze této Licence, 
* nebo (podle vašeho uvážení) kterékoli pozdější verze.
*
* Tento program je rozšiřován v naději, že bude užitečný, avšak BEZ JAKÉKOLIV ZÁRUKY. Neposkytují se ani odvozené 
* záruky PRODEJNOSTI anebo VHODNOSTI PRO URČITÝ ÚČEL. Další podrobnosti hledejte v Obecné veřejné licenci GNU.
*
* Kopii Obecné veřejné licence GNU jste měli obdržet spolu s tímto programem. Pokud se tak nestalo, 
* najdete ji zde: <http://www.gnu.org/licenses/>.
*
******************************************************************************************************************************/

include('../inc/connect.php');


function count_shunt_voltage($ad_divs) {
  $pom = (5.02/256)*$ad_divs;
  $pom = $pom - 0.88;
  return $pom;
}

function count_shunt_current($shunt_voltage) {
  return $shunt_voltage/4.3;
}

if (isset($_GET['text'])) {
  echo $_GET['text'];
  $text =  $_GET['text'];
}


$ad1 = floatval(hexdec(substr($text,4,2)).".".hexdec(substr($text,6,1)));
$ad2 = floatval(hexdec(substr($text,11,2)).".".hexdec(substr($text,13,1)));
$ad3 = floatval(hexdec(substr($text,18,2)).".".hexdec(substr($text,20,1)));
$ad4 = floatval(hexdec(substr($text,25,2)).".".hexdec(substr($text,27,1)));


$shunt_v1 = count_shunt_voltage($ad1);
$shunt_v2 = count_shunt_voltage($ad2);
$shunt_v3 = count_shunt_voltage($ad3);
$shunt_v4 = count_shunt_voltage($ad4);

echo "<br/>";
echo $ad1."<br/>";
echo $ad2."<br/>";
echo $ad3."<br/>";
echo $ad4."<br/>";
echo "====Napeti na sunt======<br/>";
echo $shunt_v1."<br/>";
echo $shunt_v2."<br/>";
echo $shunt_v3."<br/>";
echo $shunt_v4."<br/>";
echo "<br/>====Proud shuntem======<br/>";
echo count_shunt_current($shunt_v1)."<br/>";
echo count_shunt_current($shunt_v2)."<br/>";
echo count_shunt_current($shunt_v3)."<br/>";
echo count_shunt_current($shunt_v4)."<br/>";
 
if (($ad1 != 0) && ($ad2 != 0)) {
  $sql = 'INSERT INTO thermo_electric_heads SET teh_divs1 = '.$ad1.
	  ',teh_divs2 = '.$ad2.
	  ',teh_divs3 = '.$ad3.
	  ',teh_divs4 = '.$ad4.
	  ',teh_shunt_v1 = '.$shunt_v1.
	  ',teh_shunt_v2 = '.$shunt_v2.
	  ',teh_shunt_v3 = '.$shunt_v3.
	  ',teh_shunt_v4 = '.$shunt_v4.
	  ',teh_shunt_c1 = '.count_shunt_current($shunt_v1).
	  ',teh_shunt_c2 = '.count_shunt_current($shunt_v2).
	  ',teh_shunt_c3 = '.count_shunt_current($shunt_v3).
	  ',teh_shunt_c4 = '.count_shunt_current($shunt_v4).'';

  $result=mysql_query($sql) or die(mysql_error());
} else {
  echo "Neukladam do DB, filtrace chybne hodnoty";
}

mysql_close($link);


?>