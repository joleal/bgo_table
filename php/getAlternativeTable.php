	<?php

	include 'config.php';

	$dbconnect=mysqli_connect($DB_HOST, $UName, $Pass, $DB_Name);

	$season=$_GET['season'];

	if ($dbconnect->connect_error) {
	  die("Database connection failed: " . $dbconnect->connect_error);
	}
	
	$stmt = $dbconnect->prepare(

	"SELECT
	*
	FROM
	game G
	WHERE 
NAME LIKE '%' + ? + '%'
AND NAME LIKE 'Liga AoJ%'");

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
