<?php

function error_text($error) {
  echo '<p class=error>'.$error.'</p>';
}

function succ_text($error) {
  echo '<p class=success>'.$error.'</p>';
}


function check_time_format($time){
	if (preg_match ("/^([0-2]{1})([0-9]{1}):([0-5]{1})([0-9]{1})$/", $time, $parts)){
		$exploded=explode(':',$time,2);
		if($exploded[0]>23) return false;
		else return true;
	}
	else return false;
}


?>