<?php

/*****************************************************************************************************************************
* <Skript pro kontrolu teploty, ovládání termoelektrických pohonů>
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

require_once('../inc/connect.php');
require_once('../inc/constants.php');
require_once('../inc/select_functions.php');
require_once('../inc/delete_functions.php');
require_once('../inc/insert_functions.php');

$zapnout_kotel = 0;

$speed_ary = get_speed_dial_array();
print_r($speed_ary);

echo "\n---".LOG_PATH."----\n";
$myLogFile = LOG_PATH;
$fh = fopen($myLogFile, 'a') or die("can't open file");
$stringData = date("Y-m-d H:i:s")."<------ BEGIN\n";
fwrite($fh, $stringData);



// Delete old settings of manual control
fwrite($fh, "\ndelete_old_manual_control : ");
fwrite($fh, delete_old_manual_control());

// Switch AUTO in home_mode_scenario -- add line to table
fwrite($fh, "\nswitch_to_auto_if_manual_mode_is_past : ");
fwrite($fh, switch_to_auto_if_manual_mode_is_past());


//echo "---IS SET HOLIDAY:".get_is_set_holiday()."\n";

$home_mode = get_actual_home_mode();
fwrite($fh, "\nActual_home_mode : ");
fwrite($fh, $home_mode);
echo "Home mode: ".$home_mode."\n";
echo "Aktuální režim: ".get_home_mode_name($home_mode)."\n";
// create array for command and control
$ary_cmd = array();
/*********************************************************************
*  0  0  0  0  0  0  0  0               = CMD =========
*  |  |  |  |  |  |  |  |====kotel      +++++++++++++++
*  |  |  |  |  |  |  |=====Infra        | 0 0 | SR    |
*  |  |  |  |  |  |======RELE 1 (BED.)  | 0 1 | SRS   |
*  |  |  |  |  |=======RELE 2 (LIV. R.) | 1 0 | SRR   |
*  |  |  |  |========RELE 3 (KUCHYN) 	 | 1 1 | GR    |
*  |  |  |=========RELE 4 (POKOJIK)	 +++++++++++++++
*  |  |==========CMD1
*  |===========CMD2
**********************************************************************/

// GET last rele settings from DB
$last_cmd = get_last_rele_command();
for ($i=0; $i<strlen($last_cmd); $i++) {
  $ary_cmd[7-$i] = $last_cmd[$i];
}
 //print_r($ary_cmd);
//$ary_cmd[0] = '0'; // vynulovani kotle
//$ary_cmd[1] = '0'; // Infrapanel
if ($ary_cmd[0] == 1) {
  $zapnout_kotel = 1;
}
$ary_cmd[7] = '0'; // S // SR... CMD
$ary_cmd[6] = '0'; // R

$sql = "SELECT zone_id, zone_output_rele FROM zones WHERE zone_status = 1";
$result = mysql_query($sql) or die(mysql_error());

// Kdyz je rezim osmy den, tak je osmy den, jinak je den podle kalendare
$day = ($home_mode == 4 ? 8 : date("N"));



while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $zone = $row['zone_id'];
  $program_id = get_actual_program($zone,$day);
  if ($home_mode == 1) 
    $program_id = 1;
  $requested_temp = get_actual_program_temperature($program_id);
  $hysteresis = get_actual_program_hysteresis($program_id);
  $actual_temp = $speed_ary[$zone]['zone_temp'];
  $label_pos = $row['zone_output_rele'];
  
  echo "\n=== ZONE:".get_zone_name($zone)."========";
  echo "\nRQST TEMP:".$requested_temp.", HYST:".$hysteresis.
       "\nACTUAL TEMP:".$actual_temp;
  
  //if ($actual_temp > ($requested_temp+$hysteresis)) {
  if ($actual_temp > ($requested_temp)) {
      echo "\nSRR";
      $ary_cmd[$label_pos] = '0';
  } 
  elseif ($actual_temp <= ($requested_temp-$hysteresis)) {
    if (get_temp_trend_plain($zone) < -0.55) { 
      $ary_cmd[$label_pos] = '0';
      echo "\nSRR---VETRANI";
    } else {
      $ary_cmd[$label_pos] = '1';
      if ($speed_ary[$row['zone_id']]['zone_adc_divs'] <= ZONE_ADC_LEVEL_TO_ON) {
        echo "OTEVIRAM: ".$speed_ary[$row['zone_id']]['zone_adc_divs'];
	$zapnout_kotel++;
      } 
      echo "\nSRS";
    }  
  }
  else {
    echo "\nOK, OLD settings";
  }
  //echo "\nOUTR:".$label_pos;
  echo "\n===========================\n";
 
}
echo "PRED";
print_r($ary_cmd);
$key = array_keys($ary_cmd, "1");
$count_on = sizeof($key);

if ($count_on == 0) {
    $ary_cmd[0] = '0';
} else {    
    if (($ary_cmd[1] == '1') && (sizeof($key) == 1)) 
	$ary_cmd[0] = '0'; 
    else 
	if ($zapnout_kotel >= 1) {
	  $ary_cmd[0] = '1';
	} else {
	  print "Kotel vypnut, protze se otevira hlavice";
	  $ary_cmd[0] = '0';
	}
}
echo "PO vyhodnoceni";
print_r($ary_cmd);

if (!in_array('1',$ary_cmd)) {
  $ary_cmd[0] = '1'; 
  $ary_cmd[1] = '1'; 
  $ary_cmd[2] = '1'; 
  $ary_cmd[3] = '1'; 
  $ary_cmd[4] = '1'; 
  $ary_cmd[5] = '1'; 
  $ary_cmd[6] = '1'; 
  $ary_cmd[7] = '1'; 
  $ary_cmd[8] = '1'; 
  $ary_cmd[9] = '1'; 
}

echo "\n";
print_r($ary_cmd);
echo "\n";

fwrite($fh, print_r($ary_cmd, true));

echo "RESPONSE:".add_relays_command($ary_cmd);
// echo "spouštím nacteni hodnot z ADC";
// 	//echo 'cd '.SCRIPTS_PATH.' && php control_temperature.php';
// 	exec('cd '.SCRIPTS_PATH.' && php get_thermoheads_values.php');
mysql_close($link);

/**************************************
* Send query to uSDS for send values
* from ADC
***************************************/
$response = file_get_contents("http://192.168.111.183/sdscep?p=0&sys141=10");
echo $response;

fclose($fh);

?>