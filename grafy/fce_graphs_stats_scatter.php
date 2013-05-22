<?php

/* CAT:Scatter chart */

 /* pChart library inclusions */
//   include("../pChart2.1.3/class/pData.class.php");
//   include("../pChart2.1.3/class/pDraw.class.php");
//   include("../pChart2.1.3/class/pImage.class.php");
include("../pChart2.1.3/class/pScatter.class.php");



 
 
function render_on_off_scatter_graph() {
  // Load request temperature
 
  $sql = "SELECT cc_timestamp, HOUR(cc_timestamp) as hour, MINUTE(cc_timestamp) as minute, cc_command & 1 as ignition FROM commands_and_control
	  WHERE ((DAY(cc_timestamp) = DAYOFMONTH(CURRENT_DATE)) AND (MONTH(cc_timestamp) = MONTH(CURRENT_DATE)) 
	  AND (YEAR(cc_timestamp) = YEAR(CURRENT_DATE)));";
    $result = mysql_query($sql) or die(mysql_error());
    $i = 0;
    $ary_real_hour = array();
    $ary_ignition = array();
    $ary_real_hour1 = array();
    $ary_ignition1 = array();
    $ary_real_hour1c = array();
    $ary_ignition1c = array();   
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$ary_real_hour[$i] = $row["hour"] + ($row["minute"]/60);
	//$ary_real_hour[$i] = $row["cc_timestamp"];
	$ary_ignition[$i] = $row["ignition"];
	$i++;
    }
    
    $j = 1;
   
   $ary_real_hour1[0] =  $ary_real_hour[0];
   $ary_ignition1[0] =  $ary_ignition[0];  
    for($i=0;$i<sizeof($ary_real_hour)-1;$i++) {
	if ($ary_ignition[$i] xor $ary_ignition[$i+1] == 1) {
	  $ary_real_hour1[$j] = $ary_real_hour[$i];
	  $ary_ignition1[$j] = $ary_ignition[$i];
	  $ary_real_hour1[$j+1] = $ary_real_hour[$i+1];
	  $ary_ignition1[$j+1] = $ary_ignition[$i+1];
	 // $ary_ignition1[$j+1] = $ary_ignition[$i+1];
	 
	// echo $j."RH:".$ary_real_hour1[$j]." ---- RV:".$ary_ignition1[$j]."\n";
	// echo ($j+1)."RH:".$ary_real_hour1[$j+1]." ---- RV:".$ary_ignition1[$j+1]."\n";
	  $j+=2;
	  
	}
     }
  
 /* Create the pData object */
  $myData = new pData();     
  for($i=0;$i<sizeof($ary_real_hour1);$i++) {
 	$myData->AddPoints($ary_ignition1[$i],"Probe 3");
	
	$myData->AddPoints($ary_ignition1[$i],"Probe 2");
	
	$myData->AddPoints($ary_real_hour1[$i],"Probe 1");
  }
  
  



  $myData->setAxisName(0,"T");
  $myData->setAxisXY(0,AXIS_X);
  $myData->setAxisPosition(0,AXIS_POSITION_BOTTOM);

  
  /* Create the Y axis and the binded series */

  $myData->setSerieOnAxis("Probe 3",1);
 $myData->setAxisName(1,"Zapnutí");
 $myData->setAxisXY(1,AXIS_Y);
 $myData->setAxisUnit(1,"");
 $myData->setAxisPosition(1,AXIS_POSITION_LEFT);

 // Setting probe 2 axis
 $myData->setSerieOnAxis("Probe 2",1);
//   $myData->setSerieOnAxis("Probe 4",1);
 
 /* Create the 1st scatter chart binding */
 $myData->setScatterSerie("Probe 1","Probe 3",0);
 $myData->setScatterSerieDescription(0,"Zapnutí kotle");
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
  $myPicture->drawText(10,18,"Zapnutí  kotle | Dnes",array("R"=>255,"G"=>255,"B"=>255));

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
  $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>24,"Rows"=>24,"RowHeight"=>1),1=>array("Min"=>0,"Max"=>2),2=>array("Min"=>0,"Max"=>30));
  $ScaleSettings = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"DrawSubTicks"=>TRUE);	
  $myScatter->drawScatterScale($ScaleSettings); 

 
  

  /* Turn on shadow computing */
  $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

  /* Draw a scatter plot chart */
  $myScatter->drawScatterLineChart();

  /* Draw the legend */
  $myScatter->drawScatterLegend(20,380,array("Mode"=>LEGEND_HORIZONTAL,"Style"=>LEGEND_NOBORDER));

  /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pic/stats/on_off_during_period.png"); 
  
  
}
