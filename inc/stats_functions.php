<?php

function calc_month_compsuption() {
	$string = "";
    $power = 10; // 10KW vykon kotle
    $price_for_kwh = 2;
    $TUV_plus_other = 1200;
    
    $sql = "SELECT MONTH(cc_timestamp) as month, YEAR(cc_timestamp) as year, 
	    SUM(IF(cc_command & 1, 1,0) = 1) as starts, SUM(IF(cc_command & 1, 1,0) = 0) as stops, 
	    COUNT(cc_command & 1) as total FROM commands_and_control GROUP BY  MONTH(cc_timestamp);";
    $result = mysql_query($sql) or die(mysql_error());
    $i = 0;
    $ary_months = array();
    $ary_total = array();
    $ary_starts = array();
    $ary_hours_a_month = array();
    $ary_month_price = array();
    $ary_month_kwh = array();
    $ary_month_price_plus_TUV = array();
    $celkem = 0;
    $celkem_kwh = 0;
    
    
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $ary_months[$i] = $row["month"];
      $ary_years[$i] = $row["year"];
      $ary_total[$i] = $row["total"];
      $ary_starts[$i] = $row["starts"];
      $ary_hours_a_month[$i] =  ($ary_starts[$i]/$ary_total[$i]*24*cal_days_in_month(CAL_GREGORIAN,$ary_months[$i], $ary_years[$i]));
      $ary_month_kwh[$i] = $ary_hours_a_month[$i]*$power;
      $ary_month_price[$i] = $ary_month_kwh[$i]*$price_for_kwh*0.8;
      $ary_month_price_plus_TUV[$i] =  $ary_month_price[$i]+$TUV_plus_other;
      
      $string .= "<strong>".$ary_months[$i]."</strong>: ".round($ary_hours_a_month[$i],1)." Hodin. KWh/měsíc: <strong>".round($ary_month_kwh[$i],1)."</strong>,  <strong>".round($ary_month_price[$i],1)."</strong> Kč/měsíc, CENA+TUV+ostatni:<strong>".round($ary_month_price_plus_TUV[$i],1)."</strong> Kč/měsíc<br>";
      $celkem += $ary_month_price_plus_TUV[$i]; 
      $celkem_kwh += $ary_month_kwh[$i]; 
      
      $i++;
    
    }
    $string .= "<br>Celkem za rok: <strong>".round($celkem,2)."</strong>, Celkem za rok KWh: <strong>".round($celkem_kwh,2)."</strong>";
    return $string;
}


?>