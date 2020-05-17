	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "
	SELECT 
		DISTINCT
		player
	FROM
		(
		SELECT player1 AS player FROM game WHERE name LIKE 'Liga Aoj%' UNION 
		SELECT player2 FROM game WHERE name LIKE 'Liga Aoj%D01%' UNION
		SELECT player3 FROM game WHERE name LIKE 'Liga Aoj%D01%' 
		) Ps
	WHERE player is not null
	ORDER by 1
;
";
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
