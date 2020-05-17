	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT * FROM scraper_log order by 1 DESC LIMIT 1;";
	$query = mysqli_query($dbconnect, $sql)
	   or die (mysqli_error($dbconnect));

	$row = mysqli_fetch_array($query);
	echo "{$row['log_date']}";
?>