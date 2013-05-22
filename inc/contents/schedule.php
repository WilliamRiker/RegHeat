<?php

// Include of schedule_functions tables, changes and so on
include(ROOT_PATH.'/inc/schedule_functions.php');

$ary_days =  array("Po", "Út", "St", "Čt", "Pá", "So", "Ne", "D8");

if (isset($_GET['zone'])) {
  $zone = $_GET['zone'];
} else {
  $zone = 1;
}

if (isset($_GET['day_of_week'])) {
  $day_of_week = $_GET['day_of_week'];
} else {
  $day_of_week = 1;
}





$sql = "SELECT * FROM zones WHERE zone_status = 1";
$result = mysql_query($sql) or die(mysql_error());


// exec('cd /srv/http/regheat/grafy && php requested_temperature.php');

?>




<div id="room_menu">
<ul>
<?php
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { // begin while
  echo '
 <li'; if($row['zone_id'] == $zone) {echo ' class="active"';} echo '>
  <a href="index.php?id=times_temperatures&amp;zone='.$row['zone_id'].'">'.$row['zone_name'].'</a>
 </li>';
}
?>
</ul>
</div>


<div id="days_menu">
<ul>
<?php
for($i=0;$i<sizeof($ary_days);$i++) {
  echo '
  <li'; if(($i+1) == $day_of_week) {echo ' class="active"';} echo '>
    <a href="index.php?id=times_temperatures&amp;zone='.$zone.'&amp;day_of_week='.($i+1).'">'.$ary_days[$i].'</a>
  </li>';
}
?>
</ul>
</div>

<div class="schedule_graph">
<?php show_requested_temp_graph($zone,$day_of_week); ?>
</div>

<div class="clearer"></div>

<?php
change_schedule_zone($zone,$day_of_week);



?>