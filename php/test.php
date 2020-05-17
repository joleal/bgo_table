	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];
	$prev_season = 'E' . (intval(substr($season,1,2))-1);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT 
			Epocas.player, 
			Epocas.season,
			A.league,
			SUM(CASE WHEN A.player IS NOT NULL THEN 1 ELSE 0 END) champ 
		FROM
			(
			SELECT player1 AS player,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj%' UNION
			SELECT player2,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj%' UNION
			SELECT player3,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj%' 
			) Epocas 
		INNER JOIN
			(
				SELECT 
					player, league, season, RANK() OVER(PARTITION BY league, season ORDER BY V DESC, DP ASC) rank
				FROM
				(
					SELECT 
						player, season, league, SUM(CASE WHEN DP = 0 THEN 1 ELSE 0 END) AS V, SUM(DP) AS DP, COUNT(*) AS games
					FROM
						(SELECT winner AS player, 0 AS DP, SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league 
						FROM game WHERE name LIKE 'Liga Aoj%' UNION ALL
						SELECT second,winnerScore - secondScore, SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj%' UNION ALL
						SELECT third,winnerScore - thirdScore, SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj%' 
						) sub
					GROUP BY player, season, league
				) ab
			) A ON 
				A.player = Epocas.player AND 
				A.season = Epocas.season AND
				A.league = Epocas.league AND
				A.rank = 1
		GROUP BY Epocas.player, A.league, Epocas.season
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
