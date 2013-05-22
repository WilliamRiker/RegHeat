<?php
  if (isset($_GET['zone'])) {
    $zone = $_GET['zone'];
  } else {
    $zone = 1;
  }
  
  $send_cmd = 0;

?><h3>Manuální změna teploty v místnosti <?=get_zone_name($zone);?></h3><?php  
  
  if (isset($_POST['delete_manual_ch'])) {
    delete_manual_control_change($_POST['delete_manual_ch']);
    $send_cmd = 1;
  }
  if (isset($_POST['add_manual_ch'])) {
    add_manual_control_change($_POST['zone'], $_POST['select_program'],$_POST['date_from'],$_POST['date_to']);
    $send_cmd = 1;
  }
   if (isset($_POST['change_manual_ch'])) {
    update_manual_control_change($_POST['change_manual_ch'], $_POST['select_program'],$_POST['date_from'],$_POST['date_to']);
    $send_cmd = 1;
  }


set_manual_temperature_change($zone);
if ($send_cmd) 
  exec('cd '.ROOT_PATH.'/scripts && php control_temperature.php');
?>
