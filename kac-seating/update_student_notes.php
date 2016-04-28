 <?php
 
 /*   Context: PHP / MySQL
      Update student roles in student list
  */
  
 /* Settings */
 
 $DB_NAME = 'seating';
 $DB_HOST = 'localhost';
 $DB_USER = 'CENSORED';
 $DB_PASS = 'CENSORED';
 
 $notes = array(
 	// CENSORED
 
 /* Initialization */
 
 $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 if (mysqli_connect_errno()) {
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 } else {
 	print "Connected to database\n";
 }
 
 foreach ($notes as $student => $note) {
 
	$first_name_qualifier = (count(split(",", $student)) == 1) ? ""
		: "AND first='" . split(",", $student)[1] ."'";
		
	$query = "UPDATE students_temp SET student_note='" . $note . "' "
		   . "WHERE last LIKE '%" . split(",", $student)[0] . "' "
		   . $first_name_qualifier;
			 
	print($query."\n");
	
	$db->query($query);
 }
 
 $db->close();
?>