<?php

function delete_manual_control_change($change_id) {
    $sql = 'DELETE FROM manual_control WHERE mc_id = '.$change_id.'';
    $result=mysql_query($sql) or die(mysql_error());
    if ($result) {
	succ_text('Manuální nastavení bylo úspěšně vymazáno');
	$is_ok = true;
    } else { 
      error_text('Došlo k chybě při mazání manuálního nastevení z databáze'); 
    }
  
}


function delete_old_manual_control() {
  $sql = 'DELETE FROM manual_control WHERE mc_due_date < CURRENT_TIMESTAMP';
      $result=mysql_query($sql) or die(mysql_error());
  if ($result) {
	echo 'staré záznamy ručního nastavení byly úspěšně smazány';
	return 1;
  } else { 
	echo 'Nepovedlo se smazat ruční nastavení záznamů'; 
	return 0;
  }

}


function delete_all_old_manual_control() {
  $sql = 'DELETE FROM manual_control';
      $result=mysql_query($sql) or die(mysql_error());
  if ($result) {
	echo 'staré záznamy ručního nastavení byly úspěšně smazány';
	//update_reset_holiday();
	return 1;
  } else { 
	echo 'Nepovedlo se smazat ruční nastavení záznamů'; 
	return 0;
  }

}

?>