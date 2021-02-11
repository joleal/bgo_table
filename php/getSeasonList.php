	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT DISTINCT SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE NAME LIKE 'Liga AoJ E%' 
		UNION
		SELECT DISTINCT SUBSTR(Name,Locate('E',Name),3) AS season FROM games_ongoing WHERE NAME LIKE 'Liga AoJ E%' 
		ORDER BY 1 DESC;";
	$query = mysqli_query($dbconnect, $sql)
	   or die (mysqli_error($dbconnect));

	//Initialize array variable
	$dbdata = array();

	//Fetch into associative array
	while ( $row = $query->fetch_assoc())  {
		$dbdata[]=$row;
	}

	//Print array in JSON format
	echo json_encode($dbdata);
?>
