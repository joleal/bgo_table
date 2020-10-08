	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$stmt = $dbconnect->prepare(

	"SELECT
	RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league, 
	player AS name,
	COUNT(*) AS games,
	SUM(COALESCE(vic,0)) AS wins,
	SUM(COALESCE(points,0)) AS points,
	SUBSTR(Name,Locate('E',Name),3) AS season
	FROM
	game G
	JOIN
	(
	  SELECT winner AS player, 5 AS points, 1 AS vic FROM game 
	  UNION
	  SELECT second, 2, 0 FROM game
	  UNION
	  SELECT third, 0, 0 FROM game
  ) players ON players.id = G.id
	WHERE 
NAME LIKE '%' + ? + '%'
AND NAME LIKE 'Liga AoJ%'
GROUP BY player, RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4))
ORDER BY 1 ASC, 5 DESC, 4 DESC");

$stmt->bind_param('s', $season);
	
// $query = mysqli_query($dbconnect, $sql)
//    or die (mysqli_error($dbconnect));
$stmt->execute();
$query = $stmt->get_result();

//Initialize array variable
$dbdata = array();

//Fetch into associative array
while ( $row = $query->fetch_assoc())  {
	$dbdata[]=$row;
}

//Print array in JSON format
echo json_encode($dbdata);
?>
