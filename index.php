<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Liga AoJ</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/dashboard/">

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
  </head>

  <body>
	<?php

	// Port you need to connect with MariaDB
	$DB_HOST = '127.0.0.1:3306';
	$UName = 'ubuntu';
	$Pass = 'bw5hf4za3s.';
	$DB_Name = 'bgo';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	?>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Liga AoJ</a>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item" style="font-size: 10px">
                Actualizado em 
<?php
	$sql = "SELECT * FROM scraper_log order by 1 DESC LIMIT 1;";
	$query = mysqli_query($dbconnect, $sql)
	   or die (mysqli_error($dbconnect));

	$row = mysqli_fetch_array($query);
	echo "{$row['log_date']}";
?>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="#">
                  <span data-feather="home"></span>
                  E09 <span class="sr-only">(current)</span>
                </a>
              </li>
            </ul>
          </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Tabela de Pontuações</h1>
          </div>
          <h3>Divisão 1</h3>
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>Jogador</th>
                  <th>Vitórias</th>
                  <th>DP</th>
                  <th>Jogos</th>
                </tr>
              </thead>
              <tbody>
<?php

	$sql = "SELECT 
	player,
	COUNT(*) AS games,
	SUM(COALESCE(vic,0)) AS wins,
	SUM(COALESCE(B.dp2,0)) + SUM(COALESCE(C.dp2,0)) AS DP
	FROM
	game G
	JOIN
	(
	        SELECT winner AS player, id FROM game 
	        UNION
	        SELECT second, id FROM game
	        UNION
	        SELECT third, id FROM game
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
NAME LIKE '%E09%'
AND NAME LIKE '%D01%'
GROUP BY player
ORDER BY 3 DESC, 4 ASC";

$query = mysqli_query($dbconnect, $sql)
   or die (mysqli_error($dbconnect));

while ($row = mysqli_fetch_array($query)) {
  echo
   "<tr>
    <td>{$row['player']}</td>
    <td>{$row['wins']}</td>
    <td>{$row['DP']}</td>
    <td>{$row['games']}</td>
   </tr>";
}
?>
              </tbody>
            </table>
          </div>
          <h3>Divisão 2</h3>
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>Jogador</th>
                  <th>Vitórias</th>
                  <th>DP</th>
                  <th>Jogos</th>
                </tr>
              </thead>
              <tbody>
<?php

	$sql = "SELECT 
	player,
	COUNT(*) AS games,
	SUM(COALESCE(vic,0)) AS wins,
	SUM(COALESCE(B.dp2,0)) + SUM(COALESCE(C.dp2,0)) AS DP
	FROM
	game G
	JOIN
	(
	        SELECT winner AS player, id FROM game 
	        UNION
	        SELECT second, id FROM game
	        UNION
	        SELECT third, id FROM game
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
NAME LIKE '%E09%'
AND NAME LIKE '%D02%'
GROUP BY player
ORDER BY 3 DESC, 4 ASC";

$query = mysqli_query($dbconnect, $sql)
   or die (mysqli_error($dbconnect));

while ($row = mysqli_fetch_array($query)) {
  echo
   "<tr>
    <td>{$row['player']}</td>
    <td>{$row['wins']}</td>
    <td>{$row['DP']}</td>
    <td>{$row['games']}</td>
   </tr>";
}
?>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
