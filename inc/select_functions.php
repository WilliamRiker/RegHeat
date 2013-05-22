<?php

function get_change_manual_control_link($zone) {
  $day = date("N");
  $sql = "SELECT * from manual_control WHERE mc_zone_id=".$zone."";
  $result = mysql_query($sql) or die(mysql_error());
  $num_rows = mysql_num_rows($result);
  
  if ($num_rows==0) {
    if(get_actual_home_mode() == 1) 
      echo '<p class="control_status"><a href="index.php?id=manual_control&amp;zone='.$zone.'">Letní režim</a>';
    if(get_actual_home_mode() == 2) 
      echo '<p class="control_status"><a href="index.php?id=manual_control&amp;zone='.$zone.'">Auto</a>';
    
    if(get_actual_home_mode() == 4) {
	echo '<p class="control_status"><a href="index.php?id=manual_control&amp;zone='.$zone.'">8. den</a>';
	$day = 8;	  
    }	
  } else {
     if (get_is_set_holiday() && (get_actual_home_mode() == 3)) {
	echo '<p class="control_status"><a href="index.php?id=manual_control&amp;zone='.$zone.'">Dovolená</a>';
     } else {
	echo '<p class="control_status"><a href="index.php?id=manual_control&amp;zone='.$zone.'">Manuál</a>';
     }  
  }  
  $label = "";
  if ($zone==10)
    $label = " <span class=\"bath_label\">(infra)</span>";
  echo ": ".get_program_name(get_actual_program($zone,$day))." ".get_actual_program_temperature(get_actual_program($zone,$day))."°C".$label."</p>";
}

/*======= SPEED Dial Array =============================================================*/
function get_speed_dial_array() {
  $sql = "SELECT * FROM zones";
  $result = mysql_query($sql) or die(mysql_error());
  $speed_ary = array();
  
  // Last temperature FOR trend
  $sql_t_h = "SELECT * FROM temperature WHERE temp_id = (SELECT max(temp_id) FROM temperature)";
  $result_t_h = mysql_query($sql_t_h) or die(mysql_error());
  $row_t_h = mysql_fetch_array($result_t_h, MYSQL_ASSOC);
  // Last temperature - 30 FOR trend
  $sql_t_l = "SELECT * FROM temperature WHERE temp_id = ".($row_t_h['temp_id']-30)."";
  $result_t_l = mysql_query($sql_t_l) or die(mysql_error());
  $row_t_l = mysql_fetch_array($result_t_l, MYSQL_ASSOC);
      
      
  // ADC values H    
  $s_adc_h = "SELECT teh_id, teh_divs1, teh_divs2, teh_divs3, teh_divs4 FROM thermo_electric_heads WHERE teh_id = (SELECT max(teh_id) FROM thermo_electric_heads)";
  $res_adc_h = mysql_query($s_adc_h) or die(mysql_error());
  $r_adc_h = mysql_fetch_array($res_adc_h);
  // ADC values L
  $record_adc = $r_adc_h["teh_id"] - 4;
  $s_adc_l= "SELECT teh_divs1, teh_divs2, teh_divs3, teh_divs4 FROM thermo_electric_heads WHERE teh_id = ".$record_adc."";
  $res_adc_l = mysql_query($s_adc_l) or die(mysql_error());
  $r_adc_l = mysql_fetch_array($res_adc_l);  
 
  
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {  
      $speed_ary[$row['zone_id']]['adc'] = $row['zone_head_adc'];
      $speed_ary[$row['zone_id']]['zone_name'] = $row['zone_name'];
      $speed_ary[$row['zone_id']]['zone_status'] = $row['zone_status'];
      $speed_ary[$row['zone_id']]['zone_sensor'] = $row['zone_sensor'];
      $speed_ary[$row['zone_id']]['zone_output_rele'] = $row['zone_output_rele'];
      
      // Plain Trend (diff)
      $speed_ary[$row['zone_id']]['zone_trend'] = round($row_t_h["temp_".$speed_ary[$row['zone_id']]['zone_sensor']] - $row_t_l["temp_".$speed_ary[$row['zone_id']]['zone_sensor']],2);
      $speed_ary[$row['zone_id']]['zone_temp'] = $row_t_h["temp_".$speed_ary[$row['zone_id']]['zone_sensor']];
      if ($speed_ary[$row['zone_id']]['adc'] > 0) {
	$speed_ary[$row['zone_id']]['zone_adc_trend'] = round($r_adc_h["teh_divs".$speed_ary[$row['zone_id']]['adc']] - $r_adc_l["teh_divs".$speed_ary[$row['zone_id']]['adc']],2);
      } else {
	$speed_ary[$row['zone_id']]['zone_adc_trend'] = -1;
      }
      if ($speed_ary[$row['zone_id']]['adc'] > 0) {
	$speed_ary[$row['zone_id']]['zone_adc_divs'] = $r_adc_h["teh_divs".$speed_ary[$row['zone_id']]['adc']];
      } else {
	$speed_ary[$row['zone_id']]['zone_adc_divs'] = 999;
      }
      
  }
  return $speed_ary;
  
}




/*======= ZONES =============================================================*/
function get_zone_status($zone) {
  $sql = "SELECT zone_status FROM zones WHERE zone_id = ".$zone."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row['0'];
}

/* ***** DODELAT *********************/

/**************************************************************
*  get_actual_zone_heating_status($zone)
*  OUTPUT 0...... in zone is heating OFF
* 	  1...... in zone is heating ON
**************************************************************/
function get_actual_zone_heating_status($zone) {
  $output_rele = get_zone_output_rele($zone);
  $last_cmd = get_last_rele_command();
  return $last_cmd[strlen($last_cmd)-1-$output_rele];
}

/**************************************************************
*  get_actual_zone_css_heating_color($zone)
*  OUTPUT 0...... class=\"blue_heat\" title=\"topení vypnuto\"
* 	  1...... class=\"red_heat\" title=\"topení zapnuto\"
**************************************************************/
function get_actual_zone_css_heating_color($zone,$divs,$trend,$active) {
  $output_rele = get_zone_output_rele($zone);
  $last_cmd = get_last_rele_command();
  $result = $last_cmd[strlen($last_cmd)-1-$output_rele];
  if ($active) {
    if ($result)
      return "".adc_trend_examination($divs,$trend, $result)."\"";
    else  
      return "".adc_trend_examination($divs,$trend, $result)."\"";	
  } else {
    if ($result)
      return "class=\"red_heat\" title=\"Topení zapnuto\"";
    else  
      return "class=\"blue_heat\" title=\"Topení vypnuto\"";	
  }
}

/**************************************************************
* get_zone_output_rele($zone)
*  OUTPUT zone RELE (0-7)
**************************************************************/
function get_zone_output_rele($zone) {
  $sql = "SELECT zone_output_rele FROM zones WHERE zone_id = ".$zone."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}



function get_zone_name($zone) {
  $sql = "SELECT zone_name FROM zones WHERE zone_id = ".$zone."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}

/*************************************************************
* RETURN sensor number from DB (WHAT temp sensor is it in DB)
*************************************************************/
function get_zone_sensor($zone) {
  $sql = "SELECT zone_sensor FROM zones WHERE zone_id = ".$zone."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);

  return $row['0'];
}


/*======= HOME MODES BGN=============================================================*/
/*************************************************************
* RETURN actual home mode
*************************************************************/
function get_actual_home_mode() {
  $sql = "SELECT hms_mode FROM home_mode_scenario WHERE 
	  hms_id=(SELECT MAX(hms_id) FROM home_mode_scenario)";

  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}

/*************************************************************
* RETURN actual home mode id
*************************************************************/
function get_actual_home_mode_id() {
  $sql = "SELECT hms_id FROM home_mode_scenario WHERE 
	  hms_id=(SELECT MAX(hms_id) FROM home_mode_scenario)";

  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}


/*************************************************************
* RETURN home mode name by hms id
*************************************************************/
function get_home_mode_name($hms_id) {
  $sql = "SELECT hm_name FROM home_modes WHERE hm_id = ".$hms_id."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}

/*======= HOME MODES END=============================================================*/



/*======= COMMANDS and CONTROL =============================================================*/

/*************************************************************
* RETURN last command sent to RSrele module
*************************************************************/
function get_last_rele_command() {
  $sql = "SELECT cc_command FROM commands_and_control WHERE 
	  cc_id=(SELECT MAX(cc_id) FROM commands_and_control)";

  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}


/*======= PROGRAMS =============================================================*/

/*************************************************************
* RETURN program id with lowest TEMPERATURE
*************************************************************/
function get_lowest_program_id() {
  $sql = "SELECT program_id FROM programs WHERE program_temperature=(SELECT MIN(program_temperature) FROM programs)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}

/*************************************************************
* RETURN program id with highest TEMPERATURE
*************************************************************/
function get_highest_program_id() {
  $sql = "SELECT program_id FROM programs WHERE program_temperature=(SELECT MAX(program_temperature) FROM programs)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}


/*************************************************************
* RETURN program id with 2nd highest TEMPERATURE
*************************************************************/
function get_2nd_highest_program_id() {
  $sql = "SELECT program_id FROM programs P1 WHERE  (2 - 1) = (select count(distinct(program_temperature)) 
                from programs P2 where P2.program_temperature > P1.program_temperature)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}




/*************************************************************
* RETURN program name by program id
*************************************************************/
function get_program_name($program_id) {
  $sql = "SELECT program_name FROM programs WHERE program_id = ".$program_id."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}



/*************************************************************
* RETURN actual program id by zone
*************************************************************/
function get_actual_program($zone, $day) {
	$sql_man = "SELECT mc_program_id FROM manual_control WHERE mc_zone_id = ".$zone." AND (mc_date_from < CURRENT_TIMESTAMP)";
	$result_man = mysql_query($sql_man) or die(mysql_error());
	$row_man = mysql_fetch_row($result_man);
	$num_rows = mysql_num_rows($result_man);
	//echo "-".$num_rows."-";
	if ($num_rows > 0) {
		return $row_man['0'];
	}  
  
	$sql = "SELECT schedule_day_".$day.", schedule_time as time FROM schedule WHERE schedule_zone = ".$zone." 
			AND schedule_time <= CURTIME() AND schedule_day_".$day." != 5 ORDER BY schedule_time DESC LIMIT 1";

	/*	    $sql = "SELECT schedule_day_".$day.", schedule_time FROM schedule WHERE schedule_zone = ".$zone." AND 
		schedule_time=(SELECT MAX(schedule_time) FROM schedule WHERE (schedule_time <= CURTIME())
		AND (schedule_zone = ".$zone.") AND (schedule_day_".$day." != 5))";
	*/
	  
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_row($result);
	
	return $row['0'];
}

/*************************************************************
* RETURN program temperature by program id
*************************************************************/
function get_actual_program_temperature($program_id) {
  $sql = "SELECT program_temperature FROM programs WHERE program_id = ".$program_id."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}

/*************************************************************
* RETURN program hysteresis by program id
*************************************************************/
function get_actual_program_hysteresis($program_id) {
  $sql = "SELECT program_hysteresis FROM programs WHERE program_id = ".$program_id."";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}



function get_program_select_box($program_id, $disabled) {
  $result_programs = mysql_query('SELECT program_id, program_name, program_temperature FROM programs');
  
  echo '
 
 <select name="select_program"'; if ($disabled) { echo "disabled=\"disabled\""; } echo '>
      ';
      mysql_data_seek($result_programs,0);
      while ($row_program = mysql_fetch_array($result_programs, MYSQL_ASSOC)) {  ?>
	<option value="<?=$row_program["program_id"]?>"<?php if ($row_program["program_id"] == $program_id) { echo " selected=selected";}?>>  <?=$row_program["program_name"]." ".$row_program["program_temperature"]?><?php if ($row_program["program_id"] != 5) { echo "°C"; }?> </option>
	<?php 
       }
      echo '
	</select>
	
	
	';
}


/*======= TEMPERATURES =============================================================*/

/***********************************************************************
* function get_temp($zone) 
* return actual temperature value of zone
*
************************************************************************/
function get_temp($zone) {
  $sql_zone_sensor = "SELECT zone_sensor FROM zones WHERE zone_id = ".$zone."";
  $result_zone_sensor = mysql_query($sql_zone_sensor) or die(mysql_error());
  $row_zone_sensor = mysql_fetch_row($result_zone_sensor);
  $sensor = $row_zone_sensor['0'];
  
  $sql = "SELECT temp_".$sensor." FROM temperature WHERE temp_id = (SELECT max(temp_id) FROM temperature)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}




/***********************************************************************
* function adc_trend_examination($trend) 
* return sentence, the aproximately state of thermo electric head
************************************************************************/
function adc_trend_examination($divs,$trend, $result) {
  if (($divs > 70) && ($divs <= 255) && ($result == 1)) {
    return "class=\"orange_heat\" title=\"Hlavice se otevírá (TR".$trend." DIV: ".$divs."mA)";
  } 
  if (($divs == 255) && ($trend != 0) && ($result == 0)) {
    return "class=\"violet_heat\" title=\"Hlavice se zavírá (TR".$trend." DIV: ".$divs."mA)";
  } 
  if (($result) && (($divs <= 70  ) || ($divs > 10))) {
    return "class=\"red_heat\" title=\"Hlavice je otevřená (TR".$trend." DIV: ".$divs."mA)";
  }
  if ((!$result) && (($divs == 255) || ($trend == 0))) {
    return "class=\"blue_heat\" title=\"Hlavice je zavřená (TR".$trend." DIV: ".$divs."mA)";
  } else {
    return "class=\"blue_heat\" title=\"NEROZHODNE (TR".$trend." DIV: ".$divs."mA)";
  }  
}


/***********************************************************************
* function get_current_adc_trend($zone) 
* return actual adc trend for last 10 minutes
************************************************************************/
function get_current_adc_trend() {

  $sql = "SELECT teh_shunt_c1, teh_shunt_c2, teh_shunt_c3, teh_shunt_c4 FROM thermo_electric_heads WHERE teh_id = (SELECT max(teh_id) FROM thermo_electric_heads)";
  $result = mysql_query($sql) or die(mysql_error());
  $row_curr = mysql_fetch_array($result);
  

  $sql = "SELECT AVG(teh_shunt_c1) as avg1, AVG(teh_shunt_c2) as avg2, AVG(teh_shunt_c3) as avg3, AVG(teh_shunt_c4) as avg4 FROM thermo_electric_heads WHERE (teh_timestamp >= DATE_SUB(NOW(),INTERVAL 10 MINUTE));";
  $result = mysql_query($sql) or die(mysql_error());
  $row_avg = mysql_fetch_array($result);  
  
  $adc_array = array(round(($row_curr['teh_shunt_c1']-$row_avg['avg1']),2), round(($row_curr['teh_shunt_c2']-$row_avg['avg2']),2), 
		     round(($row_curr['teh_shunt_c3']-$row_avg['avg3']),2), round(($row_curr['teh_shunt_c4']-$row_avg['avg4']),2));
		     
  return $adc_array;

}



/***********************************************************************
* function get_temp_trend_plain($zone) 
* return actual temperature trend for last half hour
************************************************************************/
function get_temp_trend_plain($zone) {
  $sensor = get_zone_sensor($zone);
  $sql = "SELECT AVG(temp_".$sensor.") as temp, temp_stamp FROM temperature WHERE (temp_stamp >= DATE_SUB(NOW(),INTERVAL 30 MINUTE)) AND (temp_".$sensor." <> 999)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);  
  $val = round((get_temp($zone)-$row['0']),2);
  return $val;

}

/***********************************************************************
* DEPRICATED
* function get_temp_trend($zone) 
* return actual CSS temperature trend for last half hour
************************************************************************/
function get_temp_trend($zone) {
  $val = get_temp_trend_plain($zone);
  $color = ($val > 0 ? "<span class=\"color_red\" title=\"Trend za posledních 30 min.\">&uarr;" : "<span class=\"color_blue\" title=\"Trend za posledních 30 min.\">&darr;");
  $ret = $color."".$val."°C</span>";
  echo $ret;
  return 0;
}

/***********************************************************************
* function get_css_temp_trend($val) 
* return actual CSS temperature trend for last half hour
************************************************************************/
function get_css_temp_trend($val) {
  $color = ($val > 0 ? "<span class=\"color_red\" title=\"Trend za posledních 30 min.\">&uarr;" : "<span class=\"color_blue\" title=\"Trend za posledních 30 min.\">&darr;");
  $ret = $color."".$val."°C</span>";
  echo $ret;
  return 0;
}


function get_temp_color($temp, $zone, $zoneOUT) {
  if (($temp<17.5 )) {
    $color = 'blue';
  }
  elseif (($temp>=17.5 ) && ($temp<19.5)) {
    $color = "green";
  }
  elseif (($temp>=19.5 ) && ($temp<22.5)) {
    $color = "orange";
  }
  else {
    $color = 'red';
  }
  
  return "<span class=\"color_".$color."\"><a href=\"index.php?id=room_det&amp;zoneIN=$zone&amp;zoneOUT=$zoneOUT\">".$temp."°C</a></span>";
}

/***********************************************************************
* function get_is_set_holiday() 
* return 1 if manual control in mode holiday was set
*
************************************************************************/
function get_is_set_holiday() {
  $sql = "SELECT s_value FROM settings WHERE s_name = 'holiday'";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  
  return $row['0'];
}


/***********************************************************************
* function get_key_in_production_table($key)
* return value from production table
*
************************************************************************/
function get_key_in_production_table($key) {
  $sql = "SELECT pt_value FROM production_table WHERE pt_key = '$key'";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row['0'];
}

?>