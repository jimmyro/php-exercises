<?php

$DB_NAME = 'test';
$DB_HOST = 'localhost';
$DB_USER = 'cylindrus';
$DB_PASS = 'w2gBTjer0';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$query = 'SELECT * FROM info WHERE age > 21';
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		echo stripslashes($row['username']);	
	}
} else {
	echo 'NO RESULTS';	
}

mysqli_close($mysqli);

?>