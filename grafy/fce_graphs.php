<?php

 /*
     Example21 : Playing with background
 */

 // Standard inclusions   

 /* pChart library inclusions */
 include("../pChart2.1.3/class/pData.class.php");
 include("../pChart2.1.3/class/pDraw.class.php");
 include("../pChart2.1.3/class/pImage.class.php");
 
 
 
/***********************************************
* render_temp_graph($interval[HOURS],$out_file[File.png])
*
*
************************************************/
function render_temp_graph($interval,$zone_1, $zone_2) {
$temp_1 = get_zone_sensor($zone_1);
$temp_2 = get_zone_sensor($zone_2);
$zone_name_1 = get_zone_name($zone_1);
$zone_name_2 = get_zone_name($zone_2);

$sql = "SELECT MONTH(temp_stamp), DAY(temp_stamp), HOUR(temp_stamp) as hour, MINUTE(temp_stamp) as minute, 
	AVG(temp_".$temp_1.") as temp, AVG(temp_".$temp_2.") as temp2, temp_stamp, @temp_id:=(@temp_id + 1) AS temp_id
	FROM temperature WHERE NOT temp_id % 1 AND (temp_stamp >= DATE_SUB(NOW(),INTERVAL ".$interval." HOUR))
	AND (temp_".$temp_1." != 999) AND (temp_".$temp_2." != 999)  GROUP BY MONTH(temp_stamp), DAY(temp_stamp), HOUR(temp_stamp), 
	minute div 30";
$result = mysql_query($sql) or die(mysql_error());
$i = 0;
$ary_hour = array();
$ary_temp = array();
$ary_temp1 = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

// $ary_hour[$i] = $row["hour"].":".($row["minute"]);

  $ary_hour[$i] = strtotime($row["temp_stamp"]);
  $ary_temp[$i] = $row["temp"];
  $ary_temp1[$i] = $row["temp2"];
 // echo "ary_hour:".$ary_hour[$i]." TEMP:".$ary_temp[$i]."\n";
  $i++;
}
//echo "SIZE:".sizeof($ary_hour);


 

 /* Create and populate the pData object */
 $MyData = new pData();  
 $MyData->addPoints($ary_temp,"Probe 1");
 $MyData->addPoints($ary_temp1,"Probe 2");
 $MyData->setSerieWeight("Probe 1",0);
 $MyData->setSerieWeight("Probe 2",0);
 $MyData->setSerieTicks("Probe 2",4);
//  $MyData->setSerieTicks("Probe 1",1);
 $MyData->setAxisName(0,"Teplota [°C]");
 $MyData->setAxisUnit(0,"°");
 $MyData->addPoints($ary_hour,"Labels");
 $MyData->setSerieDescription("Labels","Months");
 $MyData->setAbscissa("Labels");
 $MyData->setXAxisDisplay(AXIS_FORMAT_TIME,"H:i"); 
 $MyData->setSerieDescription("Probe 1","vnit. teplota");
 $MyData->setSerieDescription("Probe 2","venk. teplota");
 
 /* Create the pChart object */
 $myPicture = new pImage(700,330,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Draw the background */
 $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
 $myPicture->drawFilledRectangle(0,0,700,330,$Settings);

 /* Overlay with a gradient */
 $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
 $myPicture->drawGradientArea(0,0,700,330,DIRECTION_VERTICAL,$Settings);
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,329,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the chart title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
 $myPicture->drawText(10,16,"$zone_name_1/$zone_name_2  |  interval ".$interval."h",array("FontSize"=>11,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6,"R"=>0,"G"=>0,"B"=>0));

 /* Define the chart area */
 $myPicture->setGraphArea(60,40,650,300);

 /* Draw the scale */
 $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,'LabelRotation'=>90);
 $myPicture->drawScale($scaleSettings);

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Enable shadow computing */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Draw the line chart */
 $myPicture->drawLineChart();
 $myPicture->drawPlotChart(array("DisplayValues"=>FALSE,"PlotBorder"=>TRUE,"BorderSize"=>2,"Surrounding"=>-60,"BorderAlpha"=>80));

 /* Write the chart legend */
 $myPicture->drawLegend(560,9,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL,"FontR"=>255,"FontG"=>255,"FontB"=>255));

 /* Render the picture (choose the best way) */
 //INTERVAL_24-INzone_1-OUTzone_8.png
 $myPicture->autoOutput("../grafy/pic/INTERVAL_$interval-INzone_$zone_1-OUTzone_$zone_2.png");
 
}




?>
