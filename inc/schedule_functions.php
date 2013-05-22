<?php



function change_schedule_zone($zone,$day_of_week) {

$is_ok = false;
/********************************************************************
* Vykona cast 
* Uklada, maze data...
*
********************************************************************/
// Program Delete
if ((isset($_POST['delete_temp_prog']))) {
  $id = $_POST['delete_temp_prog'];
  if ($id > 1) { // Dodelat tohle nebude fungovat
    $sql = 'DELETE FROM schedule WHERE schedule_id = '.$id.'';
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
	succ_text('Program byl úspěšně vymazán z databáze');
	$is_ok = true;
    } else { error_text('Došlo k chybě při mazání programu z databáze'); }
  } else { error_text('Teplotní program pro půlnoc nelze smazat, jedná se o základní program'); }
}
// Program change or add 
if ((isset($_POST['change_temp_prog']) || isset($_POST['add_temp_prog']))) {
  $is_ok = true;
  if (isset($_POST['change_temp_prog'])) { $id = $_POST['change_temp_prog']; }
  if (isset($_POST['add_temp_prog'])) { $id = $_POST['add_temp_prog']; } 
  $day = $_POST['schedule_day'];
  $time = $_POST['time'];
  if(isset($_GET['zone'])) {
    $zone = $_GET['zone'];
  }
//   echo "TIME:".$time[$id]."<br>";
//   echo "Zone:".$zone."<br>";
  if (check_time_format($time[$id])) {
    $write_time = $time[$id];
    echo $write_time; 
  } else { 
    $is_ok = false; 
  }
    //   echo "<br>ID:".$id."<br>";
    //   echo "<br></br>";
  // Program change
  if ($is_ok && (isset($_POST['change_temp_prog']))) {
    succ_text('Data OK');
    $str_days = '';
    for($i=1;$i<=8;$i++) {
      $str_days = $str_days."schedule_day_".$i."='".$day[$i][$id]."', ";
    }
    echo $str_days;
    // DB update
    $sql="UPDATE schedule SET ".$str_days." schedule_time ='".$write_time."'
	 WHERE schedule_id = ".$id."";
    $result=mysql_query($sql) or die(mysql_error());
    
    if ($result) {
      succ_text('Úspěšně uloženo do databáze');
    } else {
      error_text('Při ukládání se stala chyba: '.$result.'');
    }
  } elseif ($is_ok && (isset($_POST['add_temp_prog']))) {
     succ_text('Data OK');
     $str_days = "";
     $str_values = "";
     for($i=1;$i<=8;$i++) {
       $str_days = $str_days."schedule_day_".$i.", ";
       $str_values = $str_values."'".$day[$i][$id]."', ";
     }
      $sql="INSERT INTO schedule (".$str_days." schedule_zone, schedule_time)
	    VALUES (".$str_values."'".$zone."','".$write_time."')";
	    
      //echo "<br>-------------------------".$sql."<br>-----------------";    
      $result=mysql_query($sql) or die(mysql_error());
      if ($result) {
	succ_text('Úspěšně uloženo do databáze, presmerovam');
	header("location: index.php?id=times_temperatures&zone=".$zone."&day_of_week=".$day_of_week.""); 
      } else {
	error_text('Při ukládání se stala chyba: '.$result.'');
      }
  } else {
    error_text('Špatně zadaná data, opakujte prosím');
  }
}
// Graph generate if everything OK
if ($is_ok) {
    $room = $_POST['room'];
    echo 'cd '.SCRIPT_GRAPHS_PATH.' && php requested_temperature.php '.$zone.'';
    exec('cd '.SCRIPT_GRAPHS_PATH.' && php requested_temperature.php '.$zone.'');
    succ_text('Graf nastavení teplot vygenerován');
}



/********************************************************************
* User Interface GUI
* 
*
********************************************************************/
$sql = "SELECT schedule_id, schedule_zone, schedule_time, schedule_day_1, schedule_day_2, schedule_day_3,
        schedule_day_4, schedule_day_5, schedule_day_6, schedule_day_6, schedule_day_7, schedule_day_8,
        zone_name FROM schedule, zones WHERE schedule.schedule_zone = zones.zone_id 
        AND (schedule.schedule_zone = ".$zone.") ORDER BY schedule_time";
$result = mysql_query($sql) or die(mysql_error());


$result_programs = mysql_query('SELECT program_id, program_name, program_temperature FROM programs ORDER BY program_temperature ASC');

$ary_temp = array();

?>
<form action="" method="post" name="temp_programs">
<table class="normal widthschedule">
<tr>
<th>Čas</th><th>Po</th><th>Út</th><th>St</th><th>Čt</th><th>Pá</th><th>So</th><th>Ne</th><th>den 8.</th><th>Akce</th>
</tr>

<?php
$i = 1;
$last_id = 0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { // begin while
    if ($i%2) {
    echo '<tr class="odd">';
    } else {
    echo '<tr>';
    }
    echo '
      <td><input type="text" name="time['.$row["schedule_id"].']" value="'.date("H:i", strtotime($row["schedule_time"])).'"/></td>';
      
      for ($j=1;$j<=8;$j++) {
      
      echo '
      <td>
       <select name="schedule_day['.$j.']['.$row["schedule_id"].']">
      ';
      mysql_data_seek($result_programs,0);
      while ($row_program = mysql_fetch_array($result_programs, MYSQL_ASSOC)) {  ?>
	<option value="<?=$row_program["program_id"]?>"<?php if ($row_program["program_id"] == $row["schedule_day_".$j.""]) { echo " selected=\"selected\"";} 
	  ?>><?=$row_program["program_name"]." ".$row_program["program_temperature"]?><?php if ($row_program["program_id"] != 5) { echo "°C"; }?></option>
	<?php 
       }
      echo '
	</select>
      </td>
      ';
      }
     
      echo '  
      <td class="akce">
      <input type="hidden" name="room" value="'.$row['schedule_zone'].'"/>
      <input class="submit_ch" type="submit" name="change_temp_prog" value="'.$row["schedule_id"].'" title="Upravit záznam" onClick=\'return confirm("Opravdu upravit záznam '.$row["schedule_time"].'?");\'/>
      <input class="submit_delete" type="submit" name="delete_temp_prog" value="'.$row["schedule_id"].'" title="Smazat program" onClick=\'return confirm("Opravdu smazat záznam '.$row["schedule_time"].'?");\'/>
      
      </td>
    </tr>
    ';
  
    $i++;
    $last_id = $i;
} // end While

    echo '
    <tr>
    <th colspan="10">Přidat novou změnu</th>
    </tr>
    <tr>
      <td><input type="text" name="time['.$last_id.']" value="HH:MM"/></td>';
      
      for ($j=1;$j<=8;$j++) {
      
      echo '
      <td>
       <select name="schedule_day['.$j.']['.$last_id.']">
      ';
      mysql_data_seek($result_programs,0);
      while ($row_program = mysql_fetch_array($result_programs, MYSQL_ASSOC)) {  ?>
	<option value="<?=$row_program["program_id"]?>"<?php if ($row_program["program_id"] == 5) { echo " selected=\"selected\"";} 
	  ?>><?=$row_program["program_name"]." ".$row_program["program_temperature"]?><?php if ($row_program["program_id"] != 5) { echo "°C"; }?></option>
	<?php 
       }
      echo '
	</select>
      </td>
      ';
      }
      echo '<td class="akce"><input class="submit_ch" type="submit" name="add_temp_prog" value="'.$last_id.'" title="Přidat záznam" onClick=\'return confirm("Opravdu přidat záznam '.$row["schedule_time"].'?");\'/></td> ';
?>
</tr>
</table>
</form>

<?php
}
?>