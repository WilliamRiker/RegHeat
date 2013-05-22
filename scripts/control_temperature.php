<?php
/********************************************************************************************************************
* <Skript pro kontrolu teploty, ovládání termoelektrických pohonů>
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
********************************************************************************************************************/

require_once('../inc/connect.php');
require_once('../inc/constants.php');
require_once('../classes/CControl_Temperature.php');
require_once('../inc/select_functions.php');
require_once('../inc/delete_functions.php');
require_once('../inc/insert_functions.php');

//				    POLE speed_dial,	 				   Log);
$control = new Control_Temperature(get_speed_dial_array(),1);
$control->Print_Model();
$control->Run_Hysteresis_Calculation(); // Methods for hysteresis temperature control
$control->Get_Ary_Cmd();
$control->Transform_Ary_For_SDS();
$control->Send_ADC_GET_Query_To_SDS();
?>