<?php 

require_once('../inc/constants.php');

/**************************************
* DEPRICATED
* Send query to uSDS for send values
* from ADC
***************************************/
$response = file_get_contents(SDS_ADDRESS."sdscep?p=0&sys141=10");
echo $response;
?>