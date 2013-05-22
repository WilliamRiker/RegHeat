<?php

include('../inc/connect.php');
include('../inc/select_functions.php');

include('fce_graphs_stats.php');
include('fce_graphs_stats_scatter.php');

/*****************************************************************
* FILE NAME   day_temp_24_zone_1-OUTzone_8
* ex. INTERVAL_24-INzone_1-OUTzone_8.png
*
*****************************************************************/
//               (TEMP_INTERVAL,ZoneIN,	ZoneOUT)    

render_heating_graph(24);
render_on_off_scatter_graph();
//render_heating_graph_on_off(24);

//render_schedule_graph(2,1,'zone_2_day1');

mysql_close($link);


?>