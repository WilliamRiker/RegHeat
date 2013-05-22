<?php

// Include of schedule_functions tables, changes and so on
include(ROOT_PATH.'/inc/home_mode_functions.php');



?>

<h2>Režim domácnosti</h2>
<br>
<?php

if ((isset($_POST['save_home_mode']))) {
  //echo $_POST['home_mode'];
  save_home_mode();
} 

if ((isset($_POST['save_home_mode_advance']))) {
  save_home_mode_advanced();
} 


echo "<p>Aktuální režim domácnosti:".get_home_mode_name(get_actual_home_mode())."</p>";

  change_home_mode();
  change_home_mode_more_settings();

  ?>


