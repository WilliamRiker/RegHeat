<?php

// Include of schedule_functions tables, changes and so on
require_once(ROOT_PATH.'/inc/stats_functions.php');


?>

<h2>Statistiky - topení</h2>

<h3>Průměrný poměr zapnutí kotle</h3>
<img src="grafy/pic/stats/normalize.png">


<h3>Zapnutí kotle od půlnoci</h3>
<img src="grafy/pic/stats/on_off_during_period.png">
<?php

//calc_month_compsuption();
echo get_key_in_production_table('calc_month_compsuption');


?>


