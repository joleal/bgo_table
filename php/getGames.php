	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT
		id,
		idF,
		name,
		round,
		RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS division,
		player1,
		player2,
		player3,
		winner, winnerScore, second, secondScore, third, thirdScore
	FROM
	(
		SELECT id, 'F' AS idF, name, player1, player2, player3, round,
		winner, winnerScore, second, secondScore, third, thirdScore
		  FROM game UNION 
		SELECT id, 'O', name, player1, player2, player3, round, '','','','','','' FROM games_ongoing
	)G
	WHERE
		NAME LIKE '%$season%'
		AND NAME LIKE 'Liga AoJ%' 
	ORDER BY 3 ASC, 2 ASC";
	
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
