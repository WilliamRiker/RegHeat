<?php
	require_once(ROOT_PATH.'/classes/CStats_DB_Get.php');
	

?>
<h2>Roční statistiky teplot</h2>
<?php
$graf = new Stats_DB();
$graf->m_Count = 10;
//echo "---".$graf->Get_Ary_Of_Temp();

//print_r($graf->Get_Sensor_Zone_Names());
//$ary = $graf->Get_Sensor_Zone_Names();
//echo "-----".$ary[2]."-------";
//$graf->Draw_Graph($graf->Get_Ary_Of_Temp());

//echo "fewewfew:".get_key_in_production_table("years_temperatures");
$graf->Draw_Graph(get_key_in_production_table('years_temperatures'));
//$pom = $graf->Get_Ary_Of_Temp();
//echo '"'.$pom.'",';
?>
