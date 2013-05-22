<?php


function show_real_requested_temp_graph($zone,$day) {
    echo '<img src="grafy/pic/zone_'.$zone.'_day'.$day.'.png" alt="Požadovaná vs. skutečná teplota" title="Požadovaná vs. skutečná teplota"/>';
  
}

function show_requested_temp_graph($zone,$day) {
    echo '<img src="grafy/pic/zone_'.$zone.'_day'.$day.'.png" alt="Požadovaná teplota" title="Požadovaná teplota"/>';
  
}
?>