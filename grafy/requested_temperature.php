<?php

require_once('../inc/select_functions.php');

echo "<br>-------------<br>";
$zone = 2;
$zone = $argv[1];
$ary_days =  array("Po", "Út", "St", "Čt", "Pá", "So", "Ne", "D8");

include('../inc/connect.php');
include('fce_graphs.php');
include('fce_graphs2.php');


echo "Requqested temp";

for ($i=1;$i<=8;$i++) {
  // 	  	       (zone, day_of_week[1-8], out_file, day)
  render_schedule_graph($zone,$i,'zone_'.$zone.'_day'.$i,$ary_days[($i-1)]);
}

mysql_close($link);

?>