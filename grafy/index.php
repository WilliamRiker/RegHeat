<?php

include('../inc/connect.php');
include('../inc/select_functions.php');

include('fce_graphs.php');
include('fce_graphs2.php');

define("TEMP_INTERVAL",     "24");
define("ZONE_OUTDOOR_E",     "8");
define("ZONE_OUTDOOR_W",     "9");

/*****************************************************************
* FILE NAME   day_temp_24_zone_1-OUTzone_8
* ex. INTERVAL_24-INzone_1-OUTzone_8.png
*
*****************************************************************/
//               (TEMP_INTERVAL,ZoneIN,	ZoneOUT)    
render_temp_graph(TEMP_INTERVAL,1,		ZONE_OUTDOOR_E);
render_temp_graph(TEMP_INTERVAL,2,		ZONE_OUTDOOR_E);
render_temp_graph(TEMP_INTERVAL,3,		ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,4,		ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,5,		ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,6,		ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,7,		ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,ZONE_OUTDOOR_E,	ZONE_OUTDOOR_W);
render_temp_graph(TEMP_INTERVAL,ZONE_OUTDOOR_W,	ZONE_OUTDOOR_E);

//				    ($zone,	$day_of_week)
render_schedule_graph_with_real_temp(1,		date("N"));
render_schedule_graph_with_real_temp(2,		date("N"));
render_schedule_graph_with_real_temp(3,		date("N"));
render_schedule_graph_with_real_temp(5,		date("N"));
render_schedule_graph_with_real_temp(7,		date("N"));


//render_schedule_graph(2,1,'zone_2_day1');

mysql_close($link);


?>