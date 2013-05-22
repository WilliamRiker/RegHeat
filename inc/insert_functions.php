<?php

function add_manual_control_change($zone, $program, $date_from, $date_to) {

    $sql = "SELECT mc_id from manual_control WHERE mc_zone_id=".$zone."";
    $result = mysql_query($sql) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
    $row = mysql_fetch_row($result);
  
    $change_id = $row['0'];
    
    if ($num_rows==0) {
	succ_text('Přidávám záznam');
	$sql="INSERT INTO manual_control SET mc_zone_id='".$zone."'
	  ,mc_program_id='".$program."'
	  ,mc_date_from='".$date_from."'
	  ,mc_due_date='".$date_to."'";
	$result=mysql_query($sql) or die(mysql_error());
	if ($result) {
	  succ_text('Manuální změna úspěšně uložena do databáze');
	  if ($result) {
	    sleep(1); // Bez sleepu podivne chovani (nekdy se ulozi, nekdy ne)
	    exec('cd '.ROOT_PATH.'/scripts && php control_temperature.php');
	  }
	} else {
	  error_text('Změna se nepodařila zapsat do databáze');
	}
    } else {
      sleep(1);
      succ_text('Upravuji záznam...');
      update_manual_control_change($change_id, $program, $date_from, $date_to);
    }
   
 
}


function add_relays_command($ary_cmd) {
    $str = implode("",$ary_cmd);
    if (!in_array("0", $ary_cmd)) {
	$str_db = '00000000';
    } else {
	$str_db = implode("",$ary_cmd);
    }
    $sql="INSERT INTO commands_and_control SET cc_command='".$str_db."'";
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      echo('CMD added');
    } else {
      echo('ERROR');
    }
    $dec_value = bindec($str);
    $response = file_get_contents(SDS_ADDRESS."sdscep?p=0&sys140=".$dec_value);
    return $response;
}



function update_manual_control_change($change_id, $program, $date_from, $date_to) {
    $sql="UPDATE manual_control SET 
	 mc_program_id='".$program."'
	 ,mc_date_from='".$date_from."'
	 ,mc_due_date='".$date_to."' WHERE mc_id=".$change_id."";
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      succ_text('Manuální změna úspěšně uložena do databáze');
    } else {
      error_text('Změna se nepodařila zapsat do databáze');
    }
    if ($result) 
      exec('cd '.ROOT_PATH.'/scripts && php control_temperature.php');
}

function update_all_manual_control_change($program, $date_from, $date_to) {
    $sql="UPDATE manual_control SET 
	 mc_program_id='".$program."'
	 ,mc_date_from='".$date_from."'
	 ,mc_due_date='".$date_to."'";

    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      succ_text('Manuální změna úspěšně uložena do databáze');
    } else {
      error_text('Změna se nepodařila zapsat do databáze');
    }
}





function switch_to_auto_if_manual_mode_is_past() {
  echo "\nFunkce prepnuti do AUTO rezimu, pokud manual pozbyl platnosti\n";
  $sql = 'SELECT hms_mode, hms_to FROM home_mode_scenario WHERE hms_id = '.get_actual_home_mode_id().'';
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  $hms_mode = $row['0'];
  $hms_to = $row['1'];
  if (($hms_mode == 3) || ($hms_mode == 4)) {
    if($hms_to < date('Y-m-d H:i:s')) {
      $sql="INSERT INTO home_mode_scenario SET hms_mode='2', hms_lock = '0'";
       $result=mysql_query($sql) or die(mysql_error());
       if ($result) {
	  echo "\nAUTO Režim úspěšně uložen\n";
	  return 1;
       } else {
	  echo "\nPři ukládání režimu došlo k chybě\n";
	  return 0;
       }
    }
  }
  return 1;
}


/***
* update_set_holiday
* function set holiday to 1  
*/
function update_set_holiday() {
    $sql="UPDATE settings SET 
	 s_value='1'
	 WHERE s_name='holiday'";
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      echo('Holiday SET, manual control are operational\n');
    } else {
      echo('Holiday write SET Error\n');
    }
}

/***
* update_set_holiday
* function set holiday to 1  
*/
function update_reset_holiday() {
    $sql="UPDATE settings SET 
	 s_value='0'
	 WHERE s_name='holiday'";
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      echo('Holiday was RESET\n');
    } else {
      echo('Holiday write  RESET Error\n');
    }
}



/***
* update_set_holiday
* function set holiday to 1  
*/
function update_key_in_production_table($key,$data) {
    $sql="UPDATE production_table SET 
	 pt_value='$data'
	 WHERE pt_key='$key'";
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      echo('Data byla uspesne updatovana\n');
    } else {
      echo('Chyba pri updatu dat\n');
    }
}



?>