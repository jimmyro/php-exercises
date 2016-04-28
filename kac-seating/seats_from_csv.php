 <?php
 
 /*   Context: PHP / MySQL
      Import list of students from CSV file to MySQL database.
  */
  
 /* Settings */
 
 $DB_NAME = 'seating';
 $DB_HOST = 'localhost';
 $DB_USER = 'CENSORED';
 $DB_PASS = 'CENSORED';
 
 $CSV_PATH = __DIR__."/students.csv";
 
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

 for ($i = 0; $i < floor($total/3); $i++) {

	//csv: last, first, form (3 fields)

 	$last = $data[3 * $i + 0];
 	$first = $data[3 * $i + 1];
 	$form = $data[3 * $i + 2];
 	
 	print("Processing entry for $last\n");
 	
 	if ($stmt = $db->prepare("INSERT INTO students_test (last,first,form) VALUES (?,?,?)")) {
		$stmt->bind_param("sss", 
			$last, $first, $form
		);
		$stmt->execute();
		$stmt->close();
	 } else {
		printf("Failed to add new student: \n%s\n", mysqli_error($db));
		exit(1);
	 }
 }
 
 $db->close();
 fclose($csv);
?>