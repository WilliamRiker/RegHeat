<?php

include('../inc/connect.php');
include('../inc/select_functions.php');

include('fce_graphs_stats_scatter.php');


/*****************************************************************
* FILE NAME   day_temp_24_zone_1-OUTzone_8
* ex. INTERVAL_24-INzone_1-OUTzone_8.png
*
*****************************************************************/
//               (TEMP_INTERVAL,ZoneIN,	ZoneOUT)    
//$zone,$day_of_week,$out_file,$day

render_on_off_scatter_graph(1,2,"dddw",1);
//render_schedule_graph(2,1,'zone_2_day1');

// mysql_close($link);


?>