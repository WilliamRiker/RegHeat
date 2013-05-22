 <?php
 
 
/*****************************************************************************************************************************
* <Třída CStats_DB pro dolovani teplot,.... z DB,
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
******************************************************************************************************************************/
 
class Stats_DB  {
	var $m_Count;
	function Get_Sensor_Zone_Names() {
		$sql_zn="SELECT zone_name, zone_sensor FROM zones";
		$result_zn = mysql_query($sql_zn) or die(mysql_error());
		while ($row = mysql_fetch_assoc($result_zn)) {
            $ret[$row["zone_sensor"]] = $row["zone_name"];
        }
		return ($ret);
    }

    function Get_Ary_Of_Temp() {
		$temperatures = "";
		$filter = "";
		$rows = "";
		for ($i=1;$i<$this->m_Count;$i++) {
			$temperatures = $temperatures."AVG(temp_".$i.") as temp_".$i.",";
			$filter = $filter."temp_".$i."!=999";
			if ($i<$this->m_Count-1) $filter = $filter." AND ";
		}
		$sql="SELECT ".$temperatures." DATE(temp_stamp) as theday FROM temperature WHERE temp_id mod 60 = 0 AND ".$filter." GROUP BY theday";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());
		$string = "";
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$string = $string."".date("Y-m-d-H:i:s", strtotime($row["theday"])).",".$row["temp_1"].",".$row["temp_2"].",".$row["temp_3"].",".$row["temp_4"].",".$row["temp_5"].",".$row["temp_6"].",".$row["temp_7"].",".$row["temp_8"].",".$row["temp_9"].'\\\n';
		}
		return ($string);	
	}
	
	function Get_Ary_Of_Months_Temp() {
		$temperatures = "";
		$filter = "";
		$rows = "";
		for ($i=1;$i<$this->m_Count;$i++) {
			$temperatures = $temperatures."temp_".$i." as temp_".$i.",";
			$filter = $filter."temp_".$i."!=999";
			if ($i<$this->m_Count-1) $filter = $filter." AND ";
		}
		$sql="SELECT ".$temperatures." temp_stamp as theday FROM temperature WHERE temp_id mod 20 = 0 AND ".$filter." AND temp_stamp >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());
		$string = "";
		
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$string .= date("Y-m-d-H:i:s", strtotime($row["theday"])).",".$row["temp_1"].",".$row["temp_2"].",".$row["temp_3"].",".$row["temp_4"].",".$row["temp_5"].",".$row["temp_6"].",".$row["temp_7"].",".$row["temp_8"].",".$row["temp_9"].'\\\n';
		}
		return ($string);	
	}
	
	function Draw_Graph($ary) {
		?>
		<div class="stats_checkbox">
		 <strong>Vyberte zóny: </strong>
		 <input type=checkbox id="0" checked onClick="change(this)"><label>Obývací pokoj</label>
		 <input type=checkbox id="1" onClick="change(this)"><label>Tech. mist</label>
		 <input type=checkbox id="2" onClick="change(this)"><label>Koupelna</label>
		 <input type=checkbox id="3" onClick="change(this)"><label>Chodba</label>
		 <input type=checkbox id="4" onClick="change(this)"><label>Exteriér V</label>
		 <input type=checkbox id="5" onClick="change(this)"><label>Pokojík</label>
		 <input type=checkbox id="6" onClick="change(this)"><label>Ložnice</label>
		 <input type=checkbox id="7" onClick="change(this)"><label>Kuchyň</label>
		 <input type=checkbox id="8" onClick="change(this)"><label>Exteriér Z</label>
		</div>


		<div id="div_g">
			<div id="graf_temp" style="width:970px; height:450px;">
			</div>
		</div>

		<script type="text/javascript">
			g = new Dygraph(
					document.getElementById("graf_temp"),
					<?php echo '"'.$ary.'",';?>
					{
					axisLabelColor: '#000000',
					colors: ['#fff100', '#939289', '#0bf4ec', '#472a00', '#23b02d', '#4169e1', '#ff0000', '#ed0ada', '#337d00'],
					xLabelHeight: 13,
					yLabelWidth: 10,
					xlabel: 'Datum',
					ylabel: 'Teplota &deg;C',
					//drawPoints: true, // co bod to tecka
					animatedZooms: true,
					fillGraph: true, // vybarveny graf
					highlightCircleSize: 4, // velikost oznaceneho bodu
					// showRangeSelector: true,
					labels: [ "Date", "Obývací pokoj","Tech. mist", "Koupelna", "Chodba", "Exteriér V", "Pokojík", "Ložnice", "Kuchyň", "Exteriér Z" ],
					yAxisLabelWidth: 25,
					axisLabelFontSize: 10,
					labelsDivStyles: { border: '0px solid black' },
					legend: 'always',
					visibility: [true, false, false, false, false, false, false, false, false],
					strokeWidth: 1.5,
					includeZero: false
					}
			);
		
			function change(el) {
				g.setVisibility(parseInt(el.id), el.checked);
				setStatus();
			}  
		</script>
<?php
	}
  }
 ?>