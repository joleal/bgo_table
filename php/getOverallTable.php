	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$p1=$_GET['p1'];
	$p2=$_GET['p2'];
	$p3=$_GET['p3'];

	$count = 0;
	$filter = "";
	$countFilter = "";

	if($p1){
		$count = $count + 1;
	  $filter = $filter . "'" . $p1 . "',";
	}

	if($p2){
		$count = $count + 1;
	  $filter = $filter . "'" . $p2 . "',";
	}

	if($p3){
		$count = $count + 1;
	  $filter = $filter . "'" . $p3 . "',";
	}

	if($count > 0){
		$filter = "AND player IN (" . $filter . "'')";
		$countFilter = "HAVING COUNT(*) = " . $count;
	}
	

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT 
	player,
	jogos,
	vitorias,
	vitorias/jogos AS percVic,
	DPs,
	DPs/jogos AS DPporJogo,
	totEp,
	COALESCE(champ, 0) AS champ,
	COALESCE(EpD1, 0) AS epocasD1,
	trueSkill
FROM
(
SELECT 
	A.player,
	COUNT(A.name) AS jogos, 
	SUM(CASE WHEN DP = 0 THEN 1 ELSE 0 END) AS Vitorias,
	SUM(DP) AS DPs,
	MAX(D1.epocasD1) AS EpD1,
	MAX(D1.champ) AS Champ,
	MAX(B.totEp) AS totEp,
	PP.mu - 3*PP.sigma AS trueSkill
FROM
(
	SELECT winner AS player, 0 AS DP, name FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION ALL
	SELECT second AS player, winnerScore - secondScore AS DP, name FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION ALL
	SELECT third AS player, winnerScore - thirdScore AS DP, name FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$'
) A
LEFT JOIN
	players PP ON PP.player = A.player
INNER JOIN 
(
	SELECT name
	FROM
	 (SELECT name, winner AS player FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION ALL
	 SELECT name, second FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION ALL
	 SELECT name, third FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$'
	 ) A
	 WHERE 1 = 1
	 " . $filter . " 
	GROUP BY name
	" . $countFilter . "
) F ON F.name = A.name
LEFT JOIN
(
	SELECT 
		EpocasD1.player, 
		count(*) epocasD1,
		SUM(CASE WHEN A.player IS NOT NULL THEN 1 ELSE 0 END) champ 
	FROM
		(
		SELECT player1 AS player,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' UNION
		SELECT player2,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' UNION
		SELECT player3,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' 
		) EpocasD1 
	LEFT JOIN
		(
			SELECT 
				player, season, RANK() OVER(PARTITION BY season ORDER BY V DESC, DP ASC) rank
			FROM
			(
				SELECT 
					player, season, SUM(CASE WHEN DP = 0 THEN 1 ELSE 0 END) AS V, SUM(DP) AS DP, COUNT(*) AS games
				FROM
					(SELECT winner AS player, 0 AS DP, SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' UNION ALL
					SELECT second,winnerScore - secondScore, SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' UNION ALL
					SELECT third,winnerScore - thirdScore, SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE name LIKE 'Liga Aoj E%D01%' 
					) sub
				GROUP BY player, season
			) ab
		) A ON 
			A.player = EpocasD1.player AND 
			A.season = EpocasD1.season AND
			A.rank = 1
	WHERE EpocasD1.season <> 'E01'
	GROUP BY EpocasD1.player
) D1 ON D1.player = A.player
JOIN
(
	SELECT player, COUNT(*) totEp FROM (
	SELECT player1 AS player,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION
	SELECT player2,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$' UNION
	SELECT player3,SUBSTR(Name,Locate('E',Name),3) AS season FROM game WHERE LEFT(NAME,12) REGEXP '^Liga Aoj E[0-9]{2}$')
	A GROUP BY player 
) B
	ON B.player = A.player
WHERE 
	COALESCE(A.player,'') <>''
GROUP BY 
A.player
) B
ORDER BY 
	champ DESC,
	epocasD1 DESC,
	vitorias DESC
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
	echo json_encode($dbdata);;
?>
