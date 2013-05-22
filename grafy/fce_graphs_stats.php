<?php

 /*
     Example21 : Playing with background
 */

 // Standard inclusions   

 /* pChart library inclusions */
 include("../pChart2.1.3/class/pData.class.php");
 include("../pChart2.1.3/class/pDraw.class.php");
 include("../pChart2.1.3/class/pImage.class.php");
 
 include('../inc/constants.php');
 
/***********************************************
* render_heating_graph($interval[HOURS],$out_file[File.png])
*
*
************************************************/
function render_heating_graph($interval) {
    /* Create and populate the pData object */ 
    $MyData = new pData();   
    $sql = "SELECT MONTH(cc_timestamp) as month, 
	    SUM(IF(cc_command & 1, 1,0) = 1) as starts, SUM(IF(cc_command & 1, 1,0) = 0) as stops, 
	    COUNT(cc_command & 1) as total FROM commands_and_control GROUP BY MONTH(cc_timestamp);";
    $result = mysql_query($sql) or die(mysql_error());
    $i = 0;
    $ary_months = array();
    $ary_stops = array();
    $ary_starts = array();
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $ary_months[$i] = $row["month"];
      $ary_stops[$i] = $row["stops"];
      $ary_starts[$i] = $row["starts"];
    
      $i++;
    }
       
    $MyData->setAxisName(0,"zapnuti kotle"); 
    
    $MyData->addPoints($ary_starts,"Kotel zapnut"); 
    $MyData->addPoints($ary_stops,"Kotel vypnut"); 
    $MyData->addPoints($ary_months,"Labels"); 
    
    $MyData->setSerieDescription("Labels","Months"); 
    $MyData->setPalette("Kotel vypnut",array("R"=>0,"G"=>16,"B"=>170));
    $MyData->setPalette("Kotel zapnut",array("R"=>187,"G"=>0,"B"=>0));
    $MyData->setAbscissa("Labels"); 

    /* Normalize the data series to 100% */ 
    $MyData->normalize(100,"%"); 

    /* Create the pChart object */ 
    $myPicture = new pImage(900,330,$MyData); 
    $myPicture->drawGradientArea(0,0,900,330,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100)); 
    $myPicture->drawGradientArea(0,0,900,330,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));

    /* Set the default font properties */ 
    $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>7)); 

    /* Draw the scale and the chart */  
    $myPicture->setGraphArea(60,20,880,280); 
     
    $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
    $ScaleSettings  = array("Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries,"DrawSubTicks"=>TRUE,"DrawArrows"=>TRUE,"ArrowSize"=>6);
    $myPicture->drawScale($ScaleSettings);
    $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>40)); 
    $myPicture->drawStackedBarChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO,"Rounded"=>FALSE,"Surrounding"=>60)); 
    $myPicture->setShadow(FALSE); 

    /* Write the chart legend */  
    $myPicture->drawLegend(680,310,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

    /* Render the picture (choose the best way) */ 
    $myPicture->autoOutput("pic/stats/normalize.png"); 
}



/***********************************************
* render_temp_graph($interval)
*
*
************************************************/
function render_heating_graph_on_off($interval) {
    /* Create and populate the pData object */ 
    $MyData = new pData();   
    
    $sql = "SELECT cc_timestamp, HOUR(cc_timestamp) as hour, MINUTE(cc_timestamp) as minute, cc_command & 1 as ignition FROM commands_and_control WHERE (cc_timestamp >= DATE_SUB(NOW(),INTERVAL 24 HOUR));";
    $result = mysql_query($sql) or die(mysql_error());
    $i = 0;
    $ary_real_hour = array();
    $ary_ignition = array();
    $ary_real_hour1 = array();
    $ary_ignition1 = array();
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$ary_real_hour[$i] = $row["hour"] + ($row["minute"]/60);
	//$ary_real_hour[$i] = $row["cc_timestamp"];
	$ary_ignition[$i] = $row["ignition"];
	$i++;
    }
    
    $j = 0;
    for($i=0;$i<sizeof($ary_real_hour)-1;$i++) {
	if ($ary_ignition[$i] xor $ary_ignition[$i+1] == 1) {
	  $ary_real_hour1[$j] = $ary_real_hour[$i];
	  $ary_real_hour1[$j+1] = $ary_real_hour[$i+1];
	  $ary_ignition1[$j] = $ary_ignition[$i];
	  $ary_ignition1[$j+1] = $ary_ignition[$i+1];
	  $j++;
	  echo $j."RH:".$ary_real_hour1[$j]." ---- RV:".$ary_ignition1[$i]."\n";
	  echo ($j+1)."RH:".$ary_real_hour1[$j+1]." ---- RV:".$ary_ignition1[$i+1]."\n";
	}
     
     }
   
    
    $MyData->addPoints($ary_ignition1,"Probe 1"); 
    //$MyData->addPoints(array(13,12,15,18,15,10),"Probe 2"); 
    $MyData->setAxisName(0,"Temperatures"); 
    $MyData->addPoints($ary_real_hour1,"Labels"); 
    $MyData->setSerieDescription("Labels","Months"); 
    $MyData->setAbscissa("Labels"); 

    /* Create the pChart object */ 
    $myPicture = new pImage(700,230,$MyData); 

    /* Turn of AAliasing */ 
    $myPicture->Antialias = FALSE; 

    /* Draw the border */ 
    $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0)); 

    $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6)); 

    /* Define the chart area */ 
    $myPicture->setGraphArea(60,30,650,190); 

    /* Draw the scale */ 
    $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
    $myPicture->drawScale($scaleSettings); 

    /* Draw the step chart */ 
    $myPicture->drawStepChart(); 

    /* Write the chart legend */ 
    $myPicture->drawLegend(590,17,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

    /* Render the picture (choose the best way) */ 
    $myPicture->autoOutput(STAT_GRAPHS_PATH."on_off_during_period.png"); 
}

function pok1() {
 /* Create your dataset object */  
 $myData = new pData();  
  
 /* Add data in your dataset */  
 $myData->addPoints(array(1,3,4,3,5,1,1,4)); 

 /* Create a pChart object and associate your dataset */  
 $myPicture = new pImage(700,230,$myData); 

 /* Choose a nice font */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>11)); 

 /* Define the boundaries of the graph area */ 
 $myPicture->setGraphArea(60,40,670,190); 

 /* Draw the scale, keep everything automatic */  
 $myPicture->drawScale(); 

 /* Draw the scale, keep everything automatic */  
 $myPicture->drawSplineChart(); 

 /* Render the picture (choose the best way) */ 
 $myPicture->autoOutput("pic/stats/on_off_during_period.png"); 
}

?>
