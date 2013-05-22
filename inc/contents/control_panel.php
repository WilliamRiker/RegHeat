<?php
$day = date("N");

speed_change();

//$ary_current_adc_trend = get_current_adc_trend();
$ary_speed_dial = get_speed_dial_array();
//print_r($ary_speed_dial);

/***********************************************************
* $ary_speed_dial_[$zone][$parametr]
*			  param = ['adc']...ADC zone_head_adc
*				  ['zone_status']	 
*
*
***********************************************************/
?>
<div id="stat">
<!-- Zona 1  -->
<div id="bedroom">
<?php echo get_temp_color($ary_speed_dial[1]['zone_temp'],1,8);
?> (<?php get_css_temp_trend($ary_speed_dial[1]['zone_trend']);?>)<?php 
get_change_manual_control_link(1);

?>
</div>

<!-- Zona 2  -->
<div id="livingroom">
<?php echo get_temp_color($ary_speed_dial[2]['zone_temp'],2,8);
?> (<?php get_css_temp_trend($ary_speed_dial[2]['zone_trend']);?>)<?php 
get_change_manual_control_link(2);

?>
</div>

<!-- Zona 3  -->
<div id="bathroom">
<?php echo get_temp_color($ary_speed_dial[3]['zone_temp'],3,9);
?> (<?php get_css_temp_trend($ary_speed_dial[3]['zone_trend']);?>)<?php 
get_change_manual_control_link(3);
get_change_manual_control_link(10);
?>

</div>

<!-- Zona 4  -->
<div id="techroom">
<?php 
echo get_temp_color($ary_speed_dial[4]['zone_temp'],4,9);
?><p><?php get_css_temp_trend($ary_speed_dial[4]['zone_trend']);?></p>
</div>

<!-- Zona 5  -->
<div id="kitchen">
<?php echo get_temp_color($ary_speed_dial[5]['zone_temp'],5,9);
?> (<?php get_css_temp_trend($ary_speed_dial[5]['zone_trend']);?>)<?php 
get_change_manual_control_link(5);

?>
</div>

<!-- Zona 6  -->
<div id="hall">
<?php echo get_temp_color($ary_speed_dial[6]['zone_temp'],6,9);
?><p><?php get_css_temp_trend($ary_speed_dial[6]['zone_trend']);?></p>

</div>

<!-- Zona 7  -->
<div id="kidsroom">
<?php 
echo get_temp_color($ary_speed_dial[7]['zone_temp'],7,9);
?> (<?php get_css_temp_trend($ary_speed_dial[7]['zone_trend']);?>)<?php 
get_change_manual_control_link(7);

?>
</div>

<!-- Zona 8 Vychod  -->
<div id="outdoor_east">
<?php echo get_temp_color($ary_speed_dial[8]['zone_temp'],8,9)?>
<p><?php get_css_temp_trend($ary_speed_dial[8]['zone_trend']);?></p>
</div>

<!-- Zona 9 Zapad  -->
<div id="outdoor_west">
<?php echo get_temp_color($ary_speed_dial[9]['zone_temp'],9,8)?>
<p><?php get_css_temp_trend($ary_speed_dial[9]['zone_trend']);?></p>
</div>


<?php //			      get_actual_zone_css_heating_color($zone,$divs,$trend,$active) ?>
<!-- Heating 1 Bedroom  -->
<div id="heating_bedroom" <?php echo get_actual_zone_css_heating_color(1,$ary_speed_dial[1]['zone_adc_divs'],$ary_speed_dial[1]['zone_adc_trend'],$ary_speed_dial[1]['zone_status'])?>>
</div>

<!-- Heating 2 Livingroom  -->
<div id="heating_livingroom" <?php echo get_actual_zone_css_heating_color(2,$ary_speed_dial[2]['zone_adc_divs'],$ary_speed_dial[2]['zone_adc_trend'],$ary_speed_dial[2]['zone_status'])?>>
</div>


<!-- Heating 5 Kitchen  -->
<div id="heating_kitchen" <?php echo get_actual_zone_css_heating_color(5,$ary_speed_dial[5]['zone_adc_divs'],$ary_speed_dial[5]['zone_adc_trend'],$ary_speed_dial[5]['zone_status'])?>>
</div>

<!-- Heating 7 Kidsroom  -->
<div id="heating_kidsroom" <?php echo get_actual_zone_css_heating_color(7,$ary_speed_dial[7]['zone_adc_divs'],$ary_speed_dial[7]['zone_adc_trend'],$ary_speed_dial[7]['zone_status'])?>>
</div>

<!-- Heating 3 Bathroom  -->
<div id="heating_bathroom" <?php echo get_actual_zone_css_heating_color(3,$ary_speed_dial[3]['zone_adc_divs'],$ary_speed_dial[3]['zone_adc_trend'],$ary_speed_dial[3]['zone_status'])?>>
</div>
<!-- Heating 10 Bedroom INFRA -->
<div id="heating_bathroom_infra" <?php echo get_actual_zone_css_heating_color(10,$ary_speed_dial[10]['zone_adc_divs'],$ary_speed_dial[10]['zone_adc_trend'],0)?>>
</div>


<!-- Furnance 4 Techroom  -->
<div id="furnance_techroom" <?php echo get_actual_zone_css_heating_color(4,$ary_speed_dial[4]['zone_adc_divs'],$ary_speed_dial[1]['zone_adc_trend'],$ary_speed_dial[4]['zone_status'])?>>
</div>


</div> <!-- END stat  -->

<div id="summary">
<h2>Přehled</h2>
<div>
  <p><strong>Režim domácnosti:</strong> <a href="index.php?id=home_mode"><?=get_home_mode_name(get_actual_home_mode());?></a></p>
</div>
<h2>Rychlá volba</h2>
<div>
  <ul class="speed_dial">
  <?php
    $sql = "SELECT zone_id, zone_name, zone_output_rele FROM zones WHERE zone_status = 1 ORDER BY zone_name ASC";
    $result = mysql_query($sql) or die(mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $zone = $row['zone_id'];
      $zone_name = $row['zone_name'];
  
      if (get_actual_zone_heating_status($zone)) {
			echo "<li><a href=\"index.php?id=control_panel&amp;action=RR&amp;zone=$zone\" class=\"bulb_on\">$zone_name</a></li>";
      } else {
			echo "<li><a href=\"index.php?id=control_panel&amp;action=RS&amp;zone=$zone\" class=\"bulb_off\">$zone_name</a></li>";
      }
    }
 ?>
 </ul>
 </div>
 <h2>Příkaz SDS</h2>
 <div>
 <?php
    echo get_last_rele_command();
  ?>
  </div>
</div>