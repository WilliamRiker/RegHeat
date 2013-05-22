<?php



if ((isset($_POST['delete_temp_prog']))) {
  $id = $_POST['delete_temp_prog'];
  if ($id > 5) {
    $sql = 'DELETE FROM programs WHERE program_id = '.$id.'';
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
	succ_text('Program byl úspěšně vymazán z databáze');
    } else {
	error_text('Došlo k chybě při mazání programu z databáze');
    }
  }
  
}

$is_ok = true;
if ((isset($_POST['change_temp_prog']) || isset($_POST['add_temp_prog']))) {
  if (isset($_POST['change_temp_prog'])) { $id = $_POST['change_temp_prog']; }
  if (isset($_POST['add_temp_prog'])) { $id = $_POST['add_temp_prog_id']; }
  
  echo "<br/>ID:".$id;
  
  $ary_temp = $_POST['ary_temp'];
  if ((!is_numeric($ary_temp[1][$id])) || (!is_numeric($ary_temp[2][$id])) ) {
    $is_ok = false;
  }
  
  echo "ID:".$id;
  echo "<br/> Temp:".$ary_temp[1][$id];
  echo "<br/> HYST:".$ary_temp[2][$id];
  echo "<br/> NAME:".$ary_temp[0][$id];
  
  
  if ($is_ok && (isset($_POST['change_temp_prog']))) {
    succ_text('Data OK');
    $sql="UPDATE programs SET program_name='".$ary_temp[0][$id]."'
	 ,program_temperature=".$ary_temp[1][$id]."
	 ,program_hysteresis=".$ary_temp[2][$id]." WHERE program_id = ".$id."";
    
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
      exec('cd '.ROOT_PATH.'/grafy && php index.php');
      succ_text('Úspěšně uloženo do databáze');
      succ_text('Graf nastavení teplot vygenerován');
    } else {
      error_text('Při ukládání se stala chyba: '.$result.'');
    }
  } elseif ($is_ok && (isset($_POST['add_temp_prog']))) {
     succ_text('Data OK');
      $sql="INSERT INTO programs (program_name, program_temperature, program_hysteresis)
	    VALUES ('".$ary_temp[0][$id]."',".$ary_temp[1][$id].",".$ary_temp[2][$id].")";
      $result=mysql_query($sql) or die(mysql_error());
      if ($result) {
	succ_text('Úspěšně uloženo do databáze');
      } else {
	error_text('Při ukládání se stala chyba: '.$result.'');
      }
  } else {
    error_text('Špatně zadaná data, opakujte prosím');
  }
  
}


$sql = "SELECT * from programs ORDER BY program_temperature ASC";
$result = mysql_query($sql) or die(mysql_error());

$ary_temp = array();

?>
<form action="" method="post" name="temp_programs">
<table class="normal">
<tr>
<th>č.p.</th><th>název</th><th>teplota [°C]</th><th>hysteréze [°C]</th><th>akce</th>
</tr>
<?php
$i = 1;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { // begin while
    if ($i%2) {
    echo '<tr class="odd">';
    } else {
    echo '<tr>';
    }
    echo '
    
      <td>'.$i.'</td>
      <td><input type="text" name="ary_temp[0]['.$row["program_id"].']" value="'.$row["program_name"].'"/></td>
      <td>
	';
	if (($row["program_id"]!=5)) {
	  echo '<input type="text" name="ary_temp[1]['.$row["program_id"].']" value="'.$row["program_temperature"].'"/>';
	}
      echo '  
      </td>
      <td>
      ';
	if (($row["program_id"]!=5)) {
	  echo '<input type="text" name="ary_temp[2]['.$row["program_id"].']" value="'.$row["program_hysteresis"].'"/></td>';
	}
      echo '  
      <td><input class="submit_ch" type="submit" name="change_temp_prog" value="'.$row["program_id"].'" title="Upravit záznam" onClick=\'return confirm("Opravdu upravit záznam '.$row["program_name"].'?");\'/>
      ';
      if (($row["program_id"]<>5)) {

	echo '<input class="submit_delete" type="submit" name="delete_temp_prog" value="'.$row["program_id"].'" title="Smazat program" onClick=\'return confirm("Opravdu smazat záznam '.$row["program_name"].'?");\'/>';
      }
      echo '
      </td>
    </tr>
    ';
    
    $i++;
} // end While
echo '
<tr>
<th colspan=5>Vytvořit nový program</th>
</tr>
  <tr>
    <td>'.$i.'</td>
    <td><input type="text" name="ary_temp[0]['.($i+1).']" value="Název programu"/></td>
    <td><input type="text" name="ary_temp[1]['.($i+1).']" value="20"/></td>
    <td><input type="text" name="ary_temp[2]['.($i+1).']" value="0.5"/></td>
    <td>
    <input type="hidden" name="add_temp_prog_id" value="'.($i+1).'"/>
    <input class="submit_ch" type="submit" name="add_temp_prog" value="Přidat záznam" title="Přidat záznam" onClick=\'return confirm("Opravdu přidat záznam '.$row["program_name"].'?");\'/></td>
  </tr>';

?>
</table>
</form>