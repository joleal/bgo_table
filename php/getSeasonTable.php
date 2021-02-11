	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];
	$prev_season = 'E' . substr('0'.(intval(substr($season,1,2))-1),-2);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT
	RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) as league, 
	players.player AS name,
	SUM(complete) AS games,
	COUNT(*) AS totalGames,
	SUM(COALESCE(vic,0)) AS wins,
	SUM(COALESCE(B.dp2,0)) + SUM(COALESCE(C.dp2,0)) AS dp,
	SUBSTR(Name,Locate('E',Name),3) AS season,
	CASE WHEN trophy.L = 'D01' THEN 1
		WHEN trophy.L = 'D02' THEN 2
		WHEN trophy.L = 'D03' THEN 3
		ELSE 0 END trophy,
	SUM(pts1.points) AS points1,
	SUM(pts2.points) AS points2
	FROM
	(
		SELECT id, name FROM game UNION 
		SELECT id, name FROM games_ongoing
	)G
	JOIN
	(
	        SELECT winner AS player, id, 1 as complete FROM game UNION
	        SELECT second, id, 1 as complete FROM game UNION
	        SELECT third, id, 1 as complete FROM game UNION 
	        SELECT player1, id, 0 FROM games_ongoing UNION 
	        SELECT player2, id, 0 FROM games_ongoing UNION
	        SELECT player3, id, 0 FROM games_ongoing
	        ) players ON players.id = G.id
	LEFT JOIN
	(
		SELECT winner AS player, id, 75 + winnerScore/(winnerScore + secondScore + thirdScore) * 100 AS points FROM game UNION
		SELECT second AS player, id, 25 + secondScore/(winnerScore + secondScore + thirdScore) * 100 AS points FROM game UNION
		SELECT third AS player, id, thirdScore/(winnerScore + secondScore + thirdScore) * 100 AS points FROM game 
	) pts1 ON pts1.id = G.id AND pts1.player = players.player
	LEFT JOIN
	(
		SELECT winner AS player, id, 5 AS points FROM game UNION
		SELECT second AS player, id, 2 AS points FROM game  
	) pts2 ON pts2.id = G.id AND pts2.player = players.player
	LEFT JOIN
	(
	        SELECT winner, id, 1 AS vic FROM game
	        UNION 
	        SELECT second, id, 1 FROM game WHERE winnerScore = secondScore
	        UNION 
	        SELECT third, id, 1 FROM game WHERE winnerScore = thirdScore  
	) A ON A.id = G.id AND A.winner = players.player
	LEFT JOIN
	(
	        SELECT second, id, winnerScore - secondScore AS dp2 FROM game 
	) B ON B.id = G.id AND B.second = players.player
	LEFT JOIN
	(
	        SELECT third, id, winnerScore - thirdScore AS dp2 FROM game 
	) C ON C.id = G.id AND C.third = players.player
	LEFT JOIN
	(
		SELECT 
			Epocas.player, 
			A.league AS L,
			SUM(CASE WHEN A.player IS NOT NULL THEN 1 ELSE 0 END) champ 
		FROM
			(
			SELECT player1 AS player,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj E%' UNION
			SELECT player2,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj E%' UNION
			SELECT player3,SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj E%' 
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
						FROM game WHERE name LIKE 'Liga Aoj E%' UNION ALL
						SELECT second,winnerScore - secondScore, SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj E%' UNION ALL
						SELECT third,winnerScore - thirdScore, SUBSTR(Name,Locate('E',Name),3) AS season,RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4)) AS league FROM game WHERE name LIKE 'Liga Aoj E%' 
						) sub
					GROUP BY player, season, league
				) ab
			) A ON 
				A.player = Epocas.player AND 
				A.season = Epocas.season AND
				A.league = Epocas.league AND
				A.rank = 1
		WHERE Epocas.season LIKE '%$prev_season%'
		GROUP BY Epocas.player, A.league
	) trophy ON trophy.player = players.player
	WHERE 
NAME LIKE '%$season%'
AND NAME LIKE 'Liga Aoj E%'
GROUP BY players.player, RTRIM(SUBSTR(NAME, LOCATE('D', NAME),4))
ORDER BY 1 ASC, 5 DESC, 6 ASC";
	
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
