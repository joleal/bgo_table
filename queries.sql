--Player Stats
SELECT 
	COUNT(*) AS played,
	SUM(CASE WHEN winner = 'Pedro Sequeira' THEN 1 ELSE 0 END) AS first,
	SUM(CASE WHEN second = 'Pedro Sequeira' THEN 1 ELSE 0 END) AS second,
	SUM(CASE WHEN third = 'Pedro Sequeira' THEN 1 ELSE 0 END) AS third,
	SUM(aggression)/COUNT(*) AS aggressions,
	SUM(war)/COUNT(*) AS wars,
	SUM(actions)/COUNT(*) AS actions,
	SUM(paidActions)/SUM(takeActions) AS actionsPerCard
FROM game G
JOIN
(
	SELECT 
		PA.game_id,
		RA.action_player,
		SUM(CASE WHEN action_type = 'political' AND action_subtype = 'aggression' THEN 1.0 ELSE 0.0 END) AS aggression,
		SUM(CASE WHEN action_type = 'political' AND action_subtype = 'war' THEN 1.0 ELSE 0.0 END) AS war,
		SUM(CASE WHEN action_type = 'civil' THEN 1.0 ELSE 0.0 END) AS actions,
		SUM(CASE WHEN action_type = 'civil' AND action_subtype = 'take card' THEN action_spent + 0.0 ELSE 0.0 END) AS paidActions,
		SUM(CASE WHEN action_type = 'civil' AND action_subtype = 'take card' THEN 1.0 ELSE 0.0 END) AS takeActions		
	FROM game_parsed_actions PA
	JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
	GROUP BY PA.game_id, RA.action_player
) actions ON actions.game_id = G.id AND 
	(action_player = winner OR 
	 action_player = second OR
	 action_player = third)
WHERE
name LIKE 'Liga AoJ%'
AND
(winner = 'Pedro Sequeira' OR
second = 'Pedro Sequeira' OR 
third = 'Pedro Sequeira')

--Liders
SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1.0 ELSE 0.0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = 'Vítor Pires'
AND PA.action_subtype = 'elect leader'
GROUP BY PA.action_text
ORDER BY choice DESC

--Wonders
SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1.0 ELSE 0.0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = 'Vítor Pires'
AND PA.action_subtype = 'complete wonder'
GROUP BY PA.action_text
ORDER BY choice DESC

--TOP cards
SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1.0 ELSE 0.0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = 'Leal Joao'
AND PA.action_subtype = 'take card'
GROUP BY PA.action_text
ORDER BY choice DESC

--TOP cards 3
SELECT 
		PA.action_text AS name,
		COUNT(PA.action_text) AS choice, 
		SUM(CASE WHEN RA.action_player = G.winner THEN 1.0 ELSE 0.0 END) AS victory
FROM game G
JOIN game_parsed_actions PA ON G.id = PA.game_id
JOIN game_raw_actions RA ON RA.game_id = PA.game_id AND RA.action_date = PA.action_date
WHERE
G.name LIKE 'Liga AoJ%'
AND RA.action_player = 'Leal Joao'
AND PA.action_subtype = 'take card'
AND PA.action_spend = 3
GROUP BY PA.action_text
ORDER BY choice DESC

--Memorable games
SELECT * FROM (
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
) A

--
('Cultural Heritage'),
('Engineering Genius'),
('Frugality'),
('Patriotism'),
('Rich Land'),
('Stockpile'),
('Urban Growth'),
('Breakthrough'),
('Reserves'),
('Efficient Upgrade'),
('Revolutionary Idea'),
('Wave of Nationalism'),
('Endowment for the Arts'),
('Military Build-Up')