<?php
require_once('../inc/constants.php');
/*****************************************************************************************************************************
* <Skript pro ukládání teplot z SDS MICRO>
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

$file = fopen(SDS_ADDRESS."temp.txt", "r");
if (!$file) {
    echo "<p>Unable to open remote file.</p>\n";
    exit;
}

$i = 0;

$pole = array();
$result = 1;
while (!feof($file) ) {      
      $line_of_text = fgets($file);
      $parts = explode(':', $line_of_text);
      preg_match_all('![\+\-]?\d+(?:\.\d+)?!', $parts[1], $matches);
      $floats = array_map('floatval', $matches[0]);
      if(!isset($floats[0])) {
		$floats[0] = 999;
		//echo "neni nastaveno";  
      }
      if ((($floats[0] <= -100) || ($floats[0] >= 100)) && ($floats[0] != 999) ) {
		$result = 0;
		echo "\nCHYBA!: ".$floats[0]."\n";
      }
      $pole[$i] = $floats[0];
      $i++;
}

if ($result) {
  $sql = 'INSERT INTO temperature SET temp_1 = '.$pole[0].
	  ',temp_2 = '.$pole[1].
	  ',temp_3 = '.$pole[2].
	  ',temp_4 = '.$pole[3].
	  ',temp_5 = '.$pole[4].
	  ',temp_6 = '.$pole[5].
	  ',temp_7 = '.$pole[6].
	  ',temp_8 = '.$pole[7].
	  ',temp_9 = '.$pole[8].
	  ',temp_10 = '.$pole[9].
	  ',temp_11 = '.$pole[10].
	  ',temp_12 = '.$pole[11].
	  ',temp_13 = '.$pole[12].
	  ',temp_14 = '.$pole[13].
	  ',temp_15 = '.$pole[14].
	  ',temp_16 = '.$pole[15].'';

  $result=mysql_query($sql) or die(mysql_error());
} else {
  echo "Neukladam do DB, filtrace hodnoty";
}
fclose($file);
mysql_close($link);

?>



