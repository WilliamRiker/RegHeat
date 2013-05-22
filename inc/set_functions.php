<?php

function set_manual_temperature_change($zone) {
	$sql = "SELECT * from manual_control WHERE mc_zone_id=".$zone."";
	$result = mysql_query($sql) or die(mysql_error());
	$num_rows = mysql_num_rows($result);

	$ary_manual_ch = array();

	if ($num_rows==0) {
		succ_text('Režim pokoje <b>automatický</b>, v následující tabulce můžete zadat ruční změnu');
	} else {
		succ_text('Režim pokoje <b>manuální</b>, v následující tabulce můžete změnit nebo zrušit nastavení');
	}
	?>
	<form action="" method="POST" name="temp_programs">
	<table class="normal" cellpading="0" cellspacing="0">
	<tr>
	<th>Program</th><th>Datum od</th><th>Datum do</th><th>akce</th>
	</tr>
	<tr>
	<td>
	<?php
	if ($num_rows==0) {
		echo '
		'.get_program_select_box(0,0).'</td>
		<input type=hidden name="zone" value="'.$zone.'">
		<td><input type="text" name="date_from" value="'.Date("Y-m-d H:i:s", Time()).'"/></td>
		<td><input type="text" name="date_to" value="'.Date("Y-m-d H:i:s", Time()).'"/></td>
		<td><input class="submit_ch" type="submit" name="add_manual_ch" value="new" title="Upravit záznam" onClick=\'return confirm("Opravdu uložit manuální nastavení?");\'/>
		</td> ';
	
	} else {
		$i = 1;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { // begin while
			
			echo '
			'.get_program_select_box($row["mc_program_id"],0).'
			<td><input type="text" name="date_from" value="'.$row["mc_date_from"].'"/></td>
			<td><input type="text" name="date_to" value="'.$row["mc_due_date"].'"/></td>
			<td><input class="submit_ch" type="submit" name="change_manual_ch" value="'.$row["mc_id"].'" title="Upravit záznam" onClick=\'return confirm("Opravdu upravit záznam '.get_program_name($row["mc_program_id"]) .'?");\'/>
			<input class="submit_delete" type="submit" name="delete_manual_ch" value="'.$row["mc_id"].'" title="Smazat program" onClick=\'return confirm("Opravdu smazat záznam '.get_program_name($row["mc_program_id"]).'?");\'/>
			</td> ';
			$i++;
		} // end While
	}
	?>
	</tr>
	</table>
	</form>
<?php
}
?>