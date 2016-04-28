<?php

 // Connect to database
 $DB_NAME = 'ajax';
 $DB_HOST = 'CENSORED';
 $DB_USER = 'CENSORED';
 $DB_PASS = 'CENSORED';
 
 $return = "";
 
 $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 if (mysqli_connect_errno()) {
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 } /*else {
 	print "Connected to database\n";
 }*/
 
 // Validate and store user input
 $term = strip_tags(substr($_POST['search_term'],0, 100));
 $term = mysqli_escape_string($term);

 //Query database and print results
 if ($stmt = $db->prepare("SELECT name,phone FROM directory WHERE ". 
 "name LIKE ? OR phone LIKE ? ORDER BY NAME ASC")) {
 	
 	$stmt->bind_param("ss", $term, $term);
 	$stmt->execute();
 	
 	$result = $stmt->get_result();
 	$return = '';
 	
 	if (mysqli_num_rows($result) == 0) {
 		$return .= "No matches!<br/>";
 	} else {
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$return .= sprintf("<b>%s</b> %s<br/>", $row['name'], $row['phone']);
		}
	}
 	
 	$stmt->close();
 } else {
 	printf("Preparation failed: %s\n", mysqli_error($db));
 	exit();
 }
 
 //Print results
 
?>