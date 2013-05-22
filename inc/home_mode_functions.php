<?php

function change_home_mode() { 

  $sql = "SELECT * FROM home_modes";
  $result = mysql_query($sql) or die(mysql_error());
   
   ?>

  <div class="form">
  <h3>Změna režimu domácnosti</h3>
  <form action="" method="post" name="change_home_mode">
  <div>
  <label>Aktuální režim:</label>
  <select name="home_mode">
  <?php
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { // begin while ?>
  <option value="<?=$row["hm_id"]?>"<?php if ($row["hm_id"] == get_actual_home_mode()) { echo " selected=\"selected\"";} 
	    ?>><?=$row["hm_name"]?></option>
	  <?php 
	}
  ?>
  </select>
  <br>
  <input type="hidden" name="save_home_mode" value="1">
  <input type="submit" name="change_home_mode" value="Uložit">
  <div class="cleaner"></div>
  </div> 
  </form>
  
  </div>
  <?php
}

function change_home_mode_more_settings() {
/*******************************************
* 1...Letni rezim
* 2...Auto
* 3...Dovolena
* 4...Osmy den
*
* LOCK == 0 .... OK
*******************************************/

  $sql = "SELECT * FROM home_mode_scenario WHERE 
	  hms_id=(SELECT MAX(hms_id) FROM home_mode_scenario)";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  
  
  
      
  $hms_mode = $row['hms_mode']; 
  
  $hms_from = $row['hms_from'];
  $hms_to = $row['hms_to'];
  /*
  echo "TF:---".$hms_from."---";
  echo "<br>TT:---".$hms_to."---";
  */
  $hms_from = ($hms_from == '0000-00-00 00:00:00' ? date( "Y-m-d H:i", time()) : $hms_from);
  $hms_to = ($hms_to == '0000-00-00 00:00:00' ?  date( "Y-m-d H:i", time()) : $hms_to);
  $hms_from = date("Y-m-d H:i", strtotime($hms_from)); 
  $hms_to = date("Y-m-d H:i", strtotime($hms_to));
  $hms_program = $row['hms_program'];
  $hms_lock = $row['hms_lock']; 
  $program_id = $row['hms_program'];
      
  

  $can_modify = ($hms_mode==4 || $hms_mode==3 ? 0 : 1);
  $can_modify_prog = ($hms_mode==3 ? 0 : 1);
  
  $disabled = (!$hms_lock || $hms_mode==4 || $hms_mode==3 ? 1 : 0);
  $lock = (!$hms_lock ? 1:0);
  ?>
  <?php
  if (!$lock) { echo "<p class=\"error\">Je třeba doplnit podrobnosti, aby bylo možné nový režim domácnosti úspěšně spustit</p>"; }
  ?>
  
  
  <div class="form">
  <h3>Nastavení podrobností režimu domácnosti</h3>
  <form action="" method="post" name="change_home_mode_more_settings">
  
  <div>
  <table>
  <tr>
  <td class="tdl"><label>Nastavení od:</label></td><td><input class="timedatepicker" name="time_from" value="<?=$hms_from?>" <?php if ($can_modify) { echo 'disabled="disabled"';} ?>/> </td>
  </tr>
  <tr>
  <td class="tdl"><label>Nastavení do:</label></td><td><input class="timedatepicker" name="time_to" value="<?=$hms_to?>" <?php if ($can_modify) { echo 'disabled="disabled"';} ?>/></td>
  </tr>
  <tr>
  <td class="tdl"><label>Program:</label></td><td>
  <?php
    get_program_select_box($program_id, $can_modify_prog);
  ?></td>
  </tr>
  </table>
  </div>
  
  
  <input type="hidden" name="save_home_mode_advance" value="1">
  <input type="submit" name="change_home_mode_advance" value="Uložit"  <?php if ($can_modify) { echo 'disabled="disabled"';} ?>/>
  <div class="cleaner"></div>
  </form>
  </div> 
  <?php
}

function save_home_mode() {
  $save = "";
  if (isset($_POST['home_mode'])) {
    $home_mode = $_POST['home_mode'];
    // echo "MODE:".$home_mode."\n";
      delete_all_old_manual_control();
    // update_reset_holiday();
    if ($_POST['home_mode'] == 1 || $_POST['home_mode'] == 2) {
      $save = ', hms_lock = 0';
    }
    if ($_POST['home_mode'] == 3) {
      update_set_holiday();
    }
    succ_text('Režim úspěšně uložen');
		echo "\nSpoustim: ".'cd '.SCRIPTS_PATH.' && php control_temperature.php';
		exec('cd '.SCRIPTS_PATH.' && php control_temperature.php');
        $sql="INSERT INTO home_mode_scenario SET hms_mode='".$home_mode."'".$save."";
        $result=mysql_query($sql) or die(mysql_error());
        if ($result) {	
        	succ_text('Záznam úspěšně uložen do DB.');
		} else {
			error_text('Při ukládání režimu došlo k chybě');
		}
  
  }
}


function save_home_mode_advanced() {
  $save = "";
  if (isset($_POST['save_home_mode_advance'])) {
 //   echo "TF:---".$_POST['time_from']."---";
  //    echo "<br>TT:---".$_POST['time_to']."---";
    
  //  echo "Program: ".$_POST['select_program'];
    $time_from = date("Y-m-d H:i:s",strtotime($_POST['time_from']));
    $time_to = date("Y-m-d H:i:s",strtotime($_POST['time_to']));
  //  echo "<br>TF:".$time_from;
  //  echo "<br>TT:--".$time_to."--";
    
    $sql = "SELECT hms_id, hms_mode FROM home_mode_scenario WHERE 
	    hms_id=(SELECT MAX(hms_id) FROM home_mode_scenario)";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $hms_id = $row['hms_id'];
 //   echo "HMS ID:".$hms_id;
    $hms_mode = $row['hms_mode'];
    if ($hms_mode == 3) {
      $select_program = $_POST['select_program'];
      $save = "hms_program='".$select_program."',";
    }
      
    $sql_ins="UPDATE home_mode_scenario SET hms_from='".$time_from."', hms_to='".$time_to."',
	      ".$save." hms_lock='0' WHERE hms_id='".$hms_id."' ";
    //  echo $sql_ins;	      
    $result=mysql_query($sql_ins) or die(mysql_error());
    if ($result) {
      succ_text('Podrobnosti režimu úspěšně uloženy');
      echo "spouštím control_temperature...<br/>";
      //echo 'cd '.SCRIPTS_PATH.' && php control_temperature.php';
      exec('cd '.SCRIPTS_PATH.' && php control_temperature.php');
      if ($hms_mode==3) {
	if (get_is_set_holiday()) {
	  $sql = "SELECT zone_id FROM zones WHERE zone_status = 1";
	  $result = mysql_query($sql) or die(mysql_error());
	  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { 
	      $zone = $row['zone_id'];  
	      echo "Zapisuji manualni zmenu do zony:".$zone."\n";
	      add_manual_control_change($zone, $select_program, $time_from, $time_to);
	      update_set_holiday();
	  }
	} else {
	  update_all_manual_control_change($select_program, $time_from, $time_to);
	}
	echo "spouštím control_temperature...<br/>";
	//echo 'cd '.SCRIPTS_PATH.' && php control_temperature.php';
	exec('cd '.SCRIPTS_PATH.' && php control_temperature.php');
      }
    } else {
      error_text('Při ukládání podrobností režimu došlo k chybě');
    }
    
    
  }
}


?>