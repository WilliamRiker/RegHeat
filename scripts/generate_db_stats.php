<?php
/********************************************************************************************************************
* <Skript pro generovani hodnot do produkcni tabulky z duvodu urychleni vykreslovani>
* Copyright (C) <2013> <Václav Bobek>
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
require_once('../classes/CStats_DB_Get.php');
require_once('../inc/insert_functions.php');
require_once('../inc/stats_functions.php');


$graf = new Stats_DB();
$graf->m_Count = 10;

echo $graf->Get_Ary_Of_Months_Temp();

update_key_in_production_table('years_temperatures',$graf->Get_Ary_Of_Temp());
update_key_in_production_table('months_temperatures',$graf->Get_Ary_Of_Months_Temp());
update_key_in_production_table('calc_month_compsuption',calc_month_compsuption());