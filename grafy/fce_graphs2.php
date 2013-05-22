<?php

/* CAT:Scatter chart */

 /* pChart library inclusions */
//  include("../pChart2.1.3/class/pData.class.php");
//  include("../pChart2.1.3/class/pDraw.class.php");
//  include("../pChart2.1.3/class/pImage.class.php");
 include("../pChart2.1.3/class/pScatter.class.php");



 
 
/***********************************************
* render_schedule_graph($zone[Number],$day_of_week,$out_file[File.png])
*
*
************************************************/
function render_schedule_graph_with_real_temp($zone,$day_of_week) {
  // Load request temperature
  $temp_sensor = get_zone_sensor($zone);
  $zone_name = get_zone_name($zone);
  $sql = "SELECT schedule_time, program_temperature, schedule_day_".$day_of_week." FROM schedule,programs
	  WHERE programs.program_id=schedule.schedule_day_".$day_of_week." AND schedule_zone = ".$zone." 
	  AND program_temperature IS NOT NULL GROUP BY schedule_time";
  $result = mysql_query($sql) or die(mysql_error());
  $i = 0;
  $ary_time = array();
  $ary_temp = array();
  $ary_time_g = array();
  $ary_temp_g = array();
  $ary_real_hour = array();
  $ary_real_temp = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ary_time[$i] = $row["schedule_time"];
    $ary_temp[$i] = $row["program_temperature"];
    $i++;
  }
 
  // korekce graf
  $ary_time_g[0] = $ary_time[0];
  $ary_temp_g[0] = $ary_temp[0];
  
  $help = 0;
  $end_pos = 0;
  for($i=1;$i<sizeof($ary_time);$i++) {
    $ary_time_g[$i+$help] = $ary_time[$i];
    $ary_time_g[$i+$help+1] = $ary_time[$i];  
    $ary_temp_g[$i+$help] = $ary_temp[$i-1];
    $ary_temp_g[$i+$help+1] = $ary_temp[$i];
    $help++;
    $end_pos = $i+$help+1;
  }
  
  // Load real temperature
  $sql = "SELECT MONTH(temp_stamp), DAY(temp_stamp), HOUR(temp_stamp) as hour, MINUTE(temp_stamp) as minute, 
	  AVG(temp_".$temp_sensor.") as temp, @temp_id:=(@temp_id + 1) AS temp_id FROM temperature WHERE NOT temp_id % 4 
	  AND (DAY(temp_stamp) = DAYOFMONTH(CURRENT_DATE)) AND (MONTH(temp_stamp) = MONTH(CURRENT_DATE)) 
	  AND (YEAR(temp_stamp) = YEAR(CURRENT_DATE)) AND (temp_".$temp_sensor." != 999)  GROUP BY MONTH(temp_stamp),
	  DAY(temp_stamp), HOUR(temp_stamp), minute;";
  $result = mysql_query($sql) or die(mysql_error());
  $i = 0;
  $ary_hour = array();
  $ary_temp = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ary_real_hour[$i] = $row["hour"] + ($row["minute"]/60);
    $ary_real_temp[$i] = $row["temp"];
    $i++;
  }
  // Count last value
  $ary_time_g[$end_pos] = date("H:i:s",strtotime('23:59:59'));
  $ary_temp_g[$end_pos] = $ary_temp_g[$end_pos-1];
 
  /* Create the pData object */
  $myData = new pData();     
  for($i=0;$i<sizeof($ary_time_g);$i++) {
 	$myData->AddPoints($ary_temp_g[$i],"Probe 3");
	
	$myData->AddPoints($ary_temp_g[$i]+2,"Probe 2");
	$hour = date("H",strtotime($ary_time_g[$i]));
	$minute = date("i",strtotime($ary_time_g[$i]));
	$hour_minute = $hour + ($minute/60);
	$myData->AddPoints($hour_minute,"Probe 1");
  }
  
  // Create RealTime axis
  for($i=0;$i<sizeof($ary_real_hour);$i++) {
      $myData->AddPoints($ary_real_hour[$i],"RealTime");
      //   echo "\n".$i.":RealTIme axe:".$ary_real_hour[$i]."Temp".$ary_real_temp[$i];
      $myData->AddPoints($ary_real_temp[$i],"Probe 4");
  }
  $max_real = max($ary_real_temp);
  $max_requested = max($ary_temp_g);
  if ($max_real>$max_requested) {
    $maximum = $max_real;
  } else {
    $maximum = $max_requested;
  }
  
  $myData->setAxisName(0,"Doba");
  $myData->setAxisXY(0,AXIS_X);
  $myData->setAxisPosition(0,AXIS_POSITION_BOTTOM);
 
 /* Create the Y axis and the binded series */
 $myData->setSerieOnAxis("Probe 3",1);
 $myData->setAxisName(1,"Teplota [°C]");
 $myData->setAxisXY(1,AXIS_Y);
 $myData->setAxisUnit(1,"°");
 $myData->setAxisPosition(1,AXIS_POSITION_LEFT);

 // Setting probe 2 axis
 $myData->setSerieOnAxis("Probe 2",1);
  $myData->setSerieOnAxis("Probe 4",1);
 
 /* Create the 1st scatter chart binding */
 $myData->setScatterSerie("Probe 1","Probe 3",0);
 $myData->setScatterSerieDescription(0,"požadovaná teplota");
 $myData->setScatterSerieTicks(0,0);
 $myData->setScatterSerieColor(0,array("R"=>255,"G"=>0,"B"=>0));

  /* Create the 2nd scatter chart binding */
 $myData->setScatterSerie("RealTime","Probe 4",2);
 $myData->setScatterSerieDescription(2,"reálná teplota");
 $myData->setScatterSerieTicks(2,0);
 $myData->setScatterSerieColor(2,array("R"=>0,"G"=>0,"B"=>255));

   /* Create the 3rd scatter chart binding */
 /*$myData->setScatterSerie("RealTime","Probe 4",4);
 $myData->setScatterSerieDescription(4,"Pokus");
//  $myData->setScatterSerieTicks(3,0);
 $myData->setScatterSerieColor(4,array("R"=>0,"G"=>0,"B"=>255));
 */  
 
 
 
  /* Create the pChart object */
  $myPicture = new pImage(700,400,$myData);

  /* Draw the background */
  $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
  $myPicture->drawFilledRectangle(0,0,700,400,$Settings);

  /* Overlay with a gradient */
  $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
  $myPicture->drawGradientArea(0,0,700,400,DIRECTION_VERTICAL,$Settings);
  $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

  /* Write the picture title */ 
  $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>13));
  $myPicture->drawText(10,18, $zone_name."  |  Požadovaná vs. reálná teplota ",array("R"=>255,"G"=>255,"B"=>255));

  /* Add a border to the picture */
  $myPicture->drawRectangle(0,0,799,399,array("R"=>0,"G"=>0,"B"=>0));

  /* Set the default font */
  $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
  
  /* Set the graph area */
  $myPicture->setGraphArea(50,50,650,350);

  /* Create the Scatter chart object */
  $myScatter = new pScatter($myPicture,$myData);

  /* Draw the scale */
//   $ScaleSettings = array("Mode"=>SCALE_MODE_START0);
//  $myScatter->drawScatterScale(); 

/* Draw the scale */ 
  $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>24,"Rows"=>24,"RowHeight"=>1),1=>array("Min"=>0,"Max"=>$maximum),2=>array("Min"=>0,"Max"=>30));
  $ScaleSettings = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"DrawSubTicks"=>TRUE);	
  $myScatter->drawScatterScale($ScaleSettings); 

 
  

  /* Turn on shadow computing */
  $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

  /* Draw a scatter plot chart */
  $myScatter->drawScatterLineChart();

  /* Draw the legend */
  $myScatter->drawScatterLegend(20,380,array("Mode"=>LEGEND_HORIZONTAL,"Style"=>LEGEND_NOBORDER));

  /* Render the picture (choose the best way) */
  // requested_vs_real_zone_1
  $myPicture->autoOutput("pic/requested_vs_real_zone_$zone.png");
  echo "Dokonceno";
//   echo "\n".strtotime($ary_time[1])."<br>fewfewfew:".date("H:i:s",$ary_time_g[1]);
  //echo "SIZE:".sizeof($ary_hour);


}



/***********************************************
* render_schedule_graph($zone[Number],$day_of_week,$out_file[File.png])
* (zone, day_of_week[1-8], out_file, day) 
*
************************************************/
function render_schedule_graph($zone,$day_of_week,$out_file,$day) {
  $zone_name = get_zone_name($zone);
  // Load request temperature
  $sql = "SELECT schedule_time, program_temperature, schedule_day_".$day_of_week." FROM schedule,programs WHERE programs.program_id=schedule.schedule_day_".$day_of_week." AND schedule_zone = ".$zone." AND program_temperature IS NOT NULL GROUP BY schedule_time";
  $result = mysql_query($sql) or die(mysql_error());
  $i = 0;
  $ary_time = array();
  $ary_temp = array();
  $ary_time_g = array();
  $ary_temp_g = array();
  $ary_real_hour = array();
//   $ary_real_temp = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ary_time[$i] = $row["schedule_time"];
    $ary_temp[$i] = $row["program_temperature"];
    $i++;
  }
 
  // korekce graf
  $ary_time_g[0] = $ary_time[0];
  $ary_temp_g[0] = $ary_temp[0];
  
  $help = 0;
  $end_pos = 0;
  for($i=1;$i<sizeof($ary_time);$i++) {
    $ary_time_g[$i+$help] = $ary_time[$i];
    $ary_time_g[$i+$help+1] = $ary_time[$i];  
    $ary_temp_g[$i+$help] = $ary_temp[$i-1];
    $ary_temp_g[$i+$help+1] = $ary_temp[$i];
    $help++;
    $end_pos = $i+$help+1;
  }
  
  if (sizeof($ary_time)==1) {
    $ary_time_g[1] = $ary_time[0];
    $ary_temp_g[1] = $ary_temp[0];
  } 
  


  // Count last value
  $ary_time_g[$end_pos] = date("H:i:s",strtotime('23:59:59'));
  $ary_temp_g[$end_pos] = $ary_temp_g[$end_pos-1];

  /* Create the pData object */
  $myData = new pData();     
  for($i=0;$i<sizeof($ary_time_g);$i++) {
 	$myData->AddPoints($ary_temp_g[$i],"Probe 3");
	
	$myData->AddPoints($ary_temp_g[$i]+2,"Probe 2");
	$hour = date("H",strtotime($ary_time_g[$i]));
	$minute = date("i",strtotime($ary_time_g[$i]));
	$hour_minute = $hour + ($minute/60);
	$myData->AddPoints($hour_minute,"Probe 1");
  }
  
  

  $max_requested = max($ary_temp_g);

  $myData->setAxisName(0,"Doba");
  $myData->setAxisXY(0,AXIS_X);
  $myData->setAxisPosition(0,AXIS_POSITION_BOTTOM);

  
  /* Create the Y axis and the binded series */

  $myData->setSerieOnAxis("Probe 3",1);
 $myData->setAxisName(1,"Teplota");
 $myData->setAxisXY(1,AXIS_Y);
 $myData->setAxisUnit(1,"°");
 $myData->setAxisPosition(1,AXIS_POSITION_LEFT);

 // Setting probe 2 axis
 $myData->setSerieOnAxis("Probe 2",1);
//   $myData->setSerieOnAxis("Probe 4",1);
 
 /* Create the 1st scatter chart binding */
 $myData->setScatterSerie("Probe 1","Probe 3",0);
 $myData->setScatterSerieDescription(0,"Požadovaná teplota");
 $myData->setScatterSerieTicks(0,0);
 $myData->setScatterSerieColor(0,array("R"=>255,"G"=>0,"B"=>0));

  /* Create the 2nd scatter chart binding */
//  $myData->setScatterSerie("RealTime","Probe 4",2);
 $myData->setScatterSerieDescription(2,"Real temperature");
 $myData->setScatterSerieTicks(2,0);
 $myData->setScatterSerieColor(2,array("R"=>0,"G"=>0,"B"=>255));

  
  /* Create the pChart object */
  $myPicture = new pImage(700,400,$myData);

  /* Draw the background */
  $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
  $myPicture->drawFilledRectangle(0,0,700,400,$Settings);

  /* Overlay with a gradient */
  $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
  $myPicture->drawGradientArea(0,0,700,400,DIRECTION_VERTICAL,$Settings);
  $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

  /* Write the picture title */ 
  $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>13));
  $myPicture->drawText(10,18, $zone_name."  |  Požadovaná teplota | ".$day."",array("R"=>255,"G"=>255,"B"=>255));
  
  //$myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>13));
  //$myPicture->drawText(10,17, $zone_name."  |  Požadovaná vs. reálná teplota ",array("R"=>255,"G"=>255,"B"=>255))

  /* Add a border to the picture */
  $myPicture->drawRectangle(0,0,799,399,array("R"=>0,"G"=>0,"B"=>0));

  /* Set the default font */
  $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
  
  /* Set the graph area */
  $myPicture->setGraphArea(50,50,650,350);

  /* Create the Scatter chart object */
  $myScatter = new pScatter($myPicture,$myData);

  /* Draw the scale */
//   $ScaleSettings = array("Mode"=>SCALE_MODE_START0);
//  $myScatter->drawScatterScale(); 

/* Draw the scale */ 
  $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>24,"Rows"=>24,"RowHeight"=>1),1=>array("Min"=>0,"Max"=>$max_requested),2=>array("Min"=>0,"Max"=>30));
  $ScaleSettings = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"DrawSubTicks"=>TRUE);	
  $myScatter->drawScatterScale($ScaleSettings); 

 
  

  /* Turn on shadow computing */
  $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

  /* Draw a scatter plot chart */
  $myScatter->drawScatterLineChart();

  /* Draw the legend */
  $myScatter->drawScatterLegend(20,380,array("Mode"=>LEGEND_HORIZONTAL,"Style"=>LEGEND_NOBORDER));

  /* Render the picture (choose the best way) */
  $myPicture->autoOutput("pic/$out_file.png");
  

 
}



?>
