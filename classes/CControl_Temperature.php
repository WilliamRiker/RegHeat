 <?php
 
 
/*****************************************************************************************************************************
* <Třída Control_Temperature pro kontrolu teploty, ovládání termoelektrických pohonů>
* Copyright (C) <2012> <Václav Bobek>
*
* Tento program je svobodný software: můžete jej šířit a upravovat podle ustanovení Obecné veřejné licence GNU 
* (GNU General Public Licence), vydávané Free Software Foundation a to buď podle 3. verze této Licence, 
* nebo (podle vašeho uvážení) kterékoli pozdější verze.
*
* Tento program je rozšiřován v naději, že bude užitečný, avšak BEZ JAKÉKOLIV ZÁRUKY. Neposkytují se ani odvozené 
* záruky PRODEJNOSTI anebo VHODNOSTI PRO URČITÝ ÚČEL. Další podrobnosti hledejte v Obecné veřejné licenci GNU.
*
* Kopii Obecné veřejné licence GNU jste měli obdržet spolu s tímto programem. Pokud se tak nestalo, 
* najdete ji zde: <http://www.gnu.org/licenses/>.
*
******************************************************************************************************************************/
 
 class Control_Temperature   {
    var $speed_ary;
    var $log;
    var $manul_control_deleted, $switched_to_auto, $get_actual_home_mode, $get_actual_home_mode_name;
    var $ary_cmd;
    var $day;
    var $set_kotel;
    function Control_Temperature ($speed_ary,$print_log) {
      $this->speed_ary=$speed_ary;
      $this->print_log = $print_log;
      $this->manul_control_deleted = delete_old_manual_control();
      $this->switched_to_auto = switch_to_auto_if_manual_mode_is_past();
      $this->get_actual_home_mode = get_actual_home_mode();
      $this->get_actual_home_mode_name = get_home_mode_name($this->get_actual_home_mode);
      $this->ary_cmd = str_split(strrev(get_last_rele_command()));
      $this->day = ($this->get_actual_home_mode == 4 ? 8 : date("N"));
      $this->set_kotel = $this->ary_cmd[0];
      $this->ary_cmd[7] = '0'; // S // SR... CMD
      $this->ary_cmd[6] = '0'; // R
    }
    function SBS() { $this->ary_cmd[0] = 1; }
    function SBR() { $this->ary_cmd[0] = 0; }
    function Get_Ary_Cmd() {
      print_r($this->ary_cmd);
    }
    function Print_Model() {
      if ($this->print_log) {
		print_r($this->speed_ary);
		echo "\nManual_Control_Deleted: ".$this->manul_control_deleted."";
		echo "\nSwitched to auto if manual mode is past: ".$this->switched_to_auto."";
		echo "\nActual home mode:".$this->get_actual_home_mode."";
		echo "\nActual home mode name:".$this->get_actual_home_mode_name."";
		print_r($this->ary_cmd);
		echo "\nDay: ".$this->day;
		echo "SET Kotel: ".$this->set_kotel."";
		echo "\n";
      }
    }
    function Run_Hysteresis_Calculation() {
		if ($this->get_actual_home_mode != 1) {
			foreach ($this->speed_ary as $ary => $res) {
				if ($res['zone_status'] == 1) {
					$program_id = get_actual_program($ary,$this->day);
					$requested_temp = get_actual_program_temperature($program_id);
					$hysteresis = get_actual_program_hysteresis($program_id);
					echo "\n===Zone: ".$res['zone_name'].",ZID:".$ary."===\n";
					echo "ACT TEMP: ".$res['zone_temp']."\n"."";
					echo "RQST TEMP:".$requested_temp.", HYST:".$hysteresis."\n";
					echo "Zone OUTpR: ".$res['zone_output_rele']."\n"."";
					if ($res['zone_temp'] >= ($requested_temp)) {
						echo "RESULT--------------->SRR";
						$this->ary_cmd[$res['zone_output_rele']] = '0';
					} 
					elseif ($res['zone_temp'] <= ($requested_temp-$hysteresis)) {
						if ($res['zone_trend'] < -0.55) { 
							$this->ary_cmd[$res['zone_output_rele']] = '0';
							echo "\nRESULT--------------->SRR---VETRANI";
						} else {
							$this->ary_cmd[$res['zone_output_rele']] = '1';
							if ($res['zone_adc_divs'] <= ZONE_ADC_LEVEL_TO_ON) {
								echo "OTEVIRAM: ".$res['zone_adc_divs'];
								$this->set_kotel++;
							} 
							echo "RESULT--------------->SRS";
						}  
					} else {
						echo "\nRESULT--------------->OK, OLD settings";
					}
					echo "\n===============================\n";
				}
			}
		} else {
			echo "Letni Rezim, topeni vypnuto....";
			$this->ary_cmd = array_fill(0, 9, 0);
		}
    } // End Run_Hysteresis_Calculation
    function Transform_Ary_For_SDS() {
		$key = array_keys($this->ary_cmd, "1");
		$count_on = sizeof($key);
		if ($count_on == 0) {
			$this->ary_cmd[0] = '0';
		} else {    
			if (($this->ary_cmd[0] == '1') && (sizeof($key) == 1)) 
				$this->ary_cmd[0] = '1'; 
			else if (($this->ary_cmd[1] == '1') && (sizeof($key) == 1)) 
				$this->ary_cmd[0] = '0'; 
			else 
				if ($this->set_kotel >= 1) {
					$this->ary_cmd[0] = '1';
				} else {
					print "Kotel vypnut, protoze se otevira hlavice";
					$this->ary_cmd[0] = '0';
				}
		}
		if (!in_array('1',$this->ary_cmd)) {
			for($i=0;$i<10;$i++) {
				$this->ary_cmd[$i] = '1'; 
			}
		}
		echo "PO vyhodnoceni";
		$this->ary_cmd = array_reverse($this->ary_cmd);
		print_r($this->ary_cmd);
		}
		function Send_ADC_GET_Query_To_SDS() {
		$response = file_get_contents(SDS_ADDRESS."sdscep?p=0&sys141=10");
		echo $response;
		}
  }
 ?>