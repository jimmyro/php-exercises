<?php

 $DB_NAME = 'ajax';
 $DB_HOST = 'CENSORED';
 $DB_USER = 'CENSORED';
 $DB_PASS = 'CENSORED';
 $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 if (mysqli_connect_errno()) {
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 } else {
 	print "Connected to database\n";
 }
 
 $data = array(
 			'Shaniqua LaRove' => '823-232-2352',
 			'Denis Diderot' => '481-516-2342',
 			'Terrence Crosley' => '723-222-9100',
 			'Dumbraysha Quanfucque' => '882-155-9292',
 			'Audio Science' => '235-235-2350',
 			'Didget Numberly' => '808-080-8080',
 			'Tom Smut' => '512-555-0111',
 			'Sara Dunbar' => '973-609-8560'
 		 );
 
foreach($data as $name => $num) {
	 if ($stmt = $db->prepare("INSERT INTO directory (name,phone) VALUES (?, ?)")) {
		$stmt->bind_param("ss", $name, $num);
		$stmt->execute();
		$stmt->close();
	 } else {
		printf("Failed to add new entry to issue table: \n%s\n", mysqli_error($db));
		exit();
	 }
}
 
 $db->close();

?>