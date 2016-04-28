 <?php
 
 /*   Context: PHP / MySQL
      Import course catalog information from CSV file to  MySQL database.
  */
  
 /* Settings */
 
 $DB_NAME = 'greensheet';
 $DB_HOST = 'localhost';
 $DB_USER = 'CENSORED';
 $DB_PASS = 'CENSORED';
 
 $CSV_PATH = __DIR__."/kac_data.csv";
 
 /* Initialization */
 
 $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 if (mysqli_connect_errno()) {
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 } else {
 	print "Connected to database\n";
 }
 
 $csv = fopen($CSV_PATH, "r");
 if ($csv != null) { print "Loaded CSV\n"; } 
 else { print "Error: Could not load CSV\n"; exit(1); }

 $data = fgetcsv($csv); $total = count($data); 
 	//note to self: fgetcsv is basically glorified split()
 print "Got CSV data ($total parts) \n";

 /* Write to database */

 for ($i = 0; $i < floor($total/4); $i++) {
\
 	$subject = $data[7 * $i + 0];
 	$id = $data[7 * $i + 1];
 	$name = $data[7 * $i + 2];
 	$description = $data[7 * $i + 3]; //use substr for testing purposes
 	$term = $data[7 * $i + 4];
 	$form = $data[7 * $i + 5];
 	$prereq = $data[7 * $i + 6];
 	
 	//printf("<p>%s %s: %s, %s %s prereq %s: %s</p>\n", $subject, $id, $name, $form, $term, $prereq, $description);
 	print("Processing entry for $id\n");
 	
 	if ($stmt = $db->prepare("INSERT INTO coursecat VALUES (?,?,?,?,?,?,?)")) {
		$stmt->bind_param("sssssss", 
			$id, $subject, $name, $term, $form, $prereq, $description
		);
		$stmt->execute();
		$stmt->close();
	 } else {
		printf("Failed to add new entry to catalog: \n%s\n", mysqli_error($db));
		exit(1);
	 }
 }
 
 $db->close();
 fclose($csv);
?>