	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$player=$_GET['name'];

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$sql = "SELECT 
	COUNT(*) AS played,
	SUM(CASE WHEN winner = '$player' THEN 1 ELSE 0 END) AS first,
	SUM(CASE WHEN second = '$player' THEN 1 ELSE 0 END) AS second,
	SUM(CASE WHEN third = '$player' THEN 1 ELSE 0 END) AS third,
	SUM(aggression)/COUNT(*) AS aggressions,
	SUM(wars)/COUNT(*) AS wars,
	SUM(actions)/COUNT(*) AS actions,
	SUM(paidActions)/SUM(takeActions) AS actionsPerCard
FROM game G
JOIN
(
	SELECT 
		PA.game_id,
		RA.action_player,
		SUM(CASE WHEN action_type = 'political' AND action_subtype = 'aggression' THEN 1.0 ELSE 0.0 END) AS aggression,
		SUM(CASE WHEN action_type = 'civil' THEN 1.0 ELSE 0.0 END) AS actions,
		SUM(CASE WHEN action_type = 'civil' AND action_subtype = 'take card' THEN action_spent + 0.0 ELSE 0.0 END) AS paidActions,
		SUM(CASE WHEN action_type = 'civil' AND action_subtype = 'take card' THEN 1.0 ELSE 0.0 END) AS takeActions		
	FROM game_parsed_actions PA
	JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
	WHERE action_player = '$player'
	GROUP BY PA.game_id, RA.action_player
) actions ON actions.game_id = G.id AND 
	(action_player = winner OR 
	 action_player = second OR
	 action_player = third)
LEFT JOIN 
	(SELECT 
		game_id,
		COUNT(*) wars
	FROM wars
		WHERE attacker = '$player' 
		AND type = 'war'
	GROUP BY game_id 
	)W
	ON W.game_id = G.id 
WHERE
name LIKE 'Liga AoJ%'";
	
$query = mysqli_query($dbconnect, $sql)
   or die (mysqli_error($dbconnect));

//Initialize array variable
$dbdata = array();

//Fetch into associative array
while ( $row = $query->fetch_assoc())  {
	$dbdata[]=$row;
}

echo "{";
echo '"player":"'.$player.'","stats":';

//Print array in JSON format
echo json_encode($dbdata[0]);
echo ",";

echo '"wars":';
$sql = "SELECT 
		W.type
FROM game G
JOIN wars W on G.id = W.game_id 
WHERE
G.name LIKE 'Liga AoJ%'
AND W.attacker = '$player'";
	
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
echo ",";

echo '"leaders":';
$sql = "SELECT 
		PA.action_text AS name,
		L.age,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1 ELSE 0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
JOIN leaders L ON L.leader = PA.action_text
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = '$player'
AND PA.action_subtype = 'elect leader'
GROUP BY PA.action_text, L.age
ORDER BY age ASC, choice DESC";
	
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
echo ",";


echo '"wonders":';
$sql = "SELECT 
		PA.action_text AS name,
		W.age,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1 ELSE 0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
JOIN wonders W ON W.wonder = PA.action_text
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = '$player'
AND PA.action_subtype = 'complete wonder'
GROUP BY PA.action_text, W.age
ORDER BY age ASC, choice DESC";
	
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
echo ",";

echo '"topCards":';
$sql = "SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1 ELSE 0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
LEFT JOIN action_cards C ON C.card = PA.action_text 
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = '$player'
AND PA.action_subtype = 'take card'
AND C.card IS NULL 	
GROUP BY PA.action_text
ORDER BY choice DESC";
	
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
echo ",";

echo '"topCards3":';
$sql = "SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1 ELSE 0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = '$player'
AND PA.action_subtype = 'take card'
AND PA.action_spent = 3
GROUP BY PA.action_text
ORDER BY choice DESC";
	
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
echo ",";

echo '"memorableGames":';
$sql = "SELECT * FROM (
SELECT 
	id, 
	'winner' as position, 
	winnerScore as points,
	winnerScore - thirdScore AS DP, 
	'Biggest Victory' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND winner = '$player' ORDER BY DP DESC LIMIT 1
) A
UNION 
SELECT * FROM (
SELECT 
	id, 
	'winner' as position, 
	winnerScore as points,
	CASE WHEN winnerScore - secondScore = 0 THEN winnerScore - thirdScore ELSE winnerScore - secondScore END AS DP, 
	'Closest Victory' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND winner = '$player' ORDER BY DP ASC LIMIT 1
) A
UNION 
SELECT * FROM (
SELECT 
	id, 
	CASE WHEN second = '$player' THEN 'second' ELSE 'third' END as position, 
	CASE WHEN second = '$player' THEN secondScore ELSE thirdScore END as points,
	CASE WHEN second = '$player' THEN secondScore ELSE thirdScore END - winnerScore AS DP, 
	'Worst Loss' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND (second = '$player' OR third = '$player') ORDER BY DP ASC LIMIT 1
) A
UNION 
SELECT * FROM (
SELECT 
	id, 
	CASE WHEN second = '$player' THEN 'second' ELSE 'third' END as position, 
	CASE WHEN second = '$player' THEN secondScore ELSE thirdScore END as points,
	CASE WHEN second = '$player' THEN secondScore ELSE thirdScore END - winnerScore AS DP, 
	'Closest Loss' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND (second = '$player' OR third = '$player') 
AND CASE WHEN second = '$player' THEN secondScore ELSE thirdScore END - winnerScore <> 0 ORDER BY DP DESC LIMIT 1
) A
UNION 
SELECT * FROM (
SELECT 
	id, 
	CASE WHEN winner = '$player' THEN 'winner' WHEN second = '$player' THEN 'second' ELSE 'third' END as position, 
	CASE WHEN winner = '$player' THEN winnerScore WHEN second = '$player' THEN secondScore ELSE thirdScore END  as points,
	CASE WHEN winner = '$player' THEN winnerScore - secondScore WHEN second = '$player' THEN secondScore - winnerScore ELSE thirdScore - winnerScore END  AS DP, 
	'Most Points' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND (second = '$player' OR third = '$player' OR winner = '$player') ORDER BY points DESC LIMIT 1
) A
UNION 
SELECT * FROM (
SELECT 
	id, 
	CASE WHEN winner = '$player' THEN 'winner' WHEN second = '$player' THEN 'second' ELSE 'third' END as position, 
	CASE WHEN winner = '$player' THEN winnerScore WHEN second = '$player' THEN secondScore ELSE thirdScore END  as points,
	CASE WHEN winner = '$player' THEN winnerScore - secondScore WHEN second = '$player' THEN secondScore - winnerScore ELSE thirdScore - winnerScore END  AS DP, 
	'Least Points' AS why
FROM game WHERE name LIKE 'Liga AoJ%' AND (second = '$player' OR third = '$player' OR winner = '$player') ORDER BY points ASC LIMIT 1
) A";
	
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
echo "}";

?>


