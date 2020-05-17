	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT
	RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league, 
	player AS name,
	COUNT(*) AS games,
	SUM(COALESCE(vic,0)) AS wins,
	SUM(COALESCE(B.dp2,0)) + SUM(COALESCE(C.dp2,0)) AS dp,
	SUBSTR(Name,Locate('E',Name),3) AS season
	FROM
	(
		SELECT id, name FROM game UNION 
		SELECT id, name FROM games_ongoing
	)G
	JOIN
	(
	        SELECT winner AS player, id FROM game UNION
	        SELECT second, id FROM game UNION
	        SELECT third, id FROM game UNION 
	        SELECT player1, id FROM games_ongoing UNION 
	        SELECT player2, id FROM games_ongoing UNION
	        SELECT player3, id FROM games_ongoing
	        ) players ON players.id = G.id
	LEFT JOIN
	(
	        SELECT winner, id, 1 AS vic FROM game
	        UNION 
	        SELECT second, id, 1 FROM game WHERE winnerScore = secondScore
	        UNION 
	        SELECT third, id, 1 FROM game WHERE winnerScore = thirdScore  
	) A ON A.id = G.id AND A.winner = player
	LEFT JOIN
	(
	        SELECT second, id, winnerScore - secondScore AS dp2 FROM game 
	) B ON B.id = G.id AND B.second = player
	LEFT JOIN
	(
	        SELECT third, id, winnerScore - thirdScore AS dp2 FROM game 
	) C ON C.id = G.id AND C.third = player
	WHERE 
NAME LIKE '%$season%'
AND NAME LIKE 'Liga AoJ%'
GROUP BY player, RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4))
ORDER BY 1 ASC, 4 DESC, 5 ASC";
	
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
