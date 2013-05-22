<?php
	require_once(ROOT_PATH.'/classes/CStats_DB_Get.php');

?>
<h2>Měsíční statistika teplot</h2>
<?php
$graf = new Stats_DB();
//echo "---".$graf->Get_Ary_Of_Months_Temp();
$graf->m_Count = 10;
$graf->Draw_Graph(get_key_in_production_table('months_temperatures'));
?>
