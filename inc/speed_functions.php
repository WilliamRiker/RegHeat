<?php
function speed_change() {
  $res = 0;
  if (isset($_GET['zone']) && isset($_GET['action'])) {
    $send_cmd = 0;
    $action = $_GET['action'];
    $zone = $_GET['zone'];
    $low_program = get_lowest_program_id();
    if ($zone == 10) 
		$high_program = get_highest_program_id();
    else
		$high_program = get_2nd_highest_program_id();
    $date_from = date('Y-m-d H:i:s');
    $date_to = date('Y-m-d H:i:s',strtotime('+1 hour'));
    
    if ($action == "RS") {
      add_manual_control_change($zone, $high_program, $date_from, $date_to);
      $send_cmd = 1;
    }  
    if ($action == "RR") {
      add_manual_control_change($zone, $low_program, $date_from, $date_to);
      $send_cmd = 1;
    }  
    return $send_cmd;
  }
}  
?>