<?php
  if (isset($_GET['zoneIN']) && isset($_GET['zoneOUT'])) {
    $zoneIN = $_GET['zoneIN'];
    $zoneOUT = $_GET['zoneOUT'];
  } else {
    $zoneIN = 1;
    $zoneOUT = 8;
  }

?>


<h3>Závislost vnitřní teploty v místnosti na teplotě venkovní</h3>
<?php
echo "<img src=\"grafy/pic/INTERVAL_24-INzone_$zoneIN-OUTzone_$zoneOUT.png\">";
?>

<?php
if (get_zone_status($zoneIN)) {
?>
<h3>Požadovaná vs. skutečná teplota v místnosti</h3>
<?php
echo "<img src=\"grafy/pic/requested_vs_real_zone_$zoneIN.png\">";
}
?>