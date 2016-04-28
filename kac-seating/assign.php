<?php

/* PSEUDOCODE

Given seating table and student table, how do we assign seats?

Restrictions:
StuCo front left
Prefects front center
Freshmen in mezzanine
Handicapped students?

Considerations:
Doesn't the template really only need 850 seat ID slots and empty name slots?
Select only ID instead of all seat details during assignment?

0. Open database connection.
	CV boilerplate code.
	Provides databse in $db object.
*/

 $DB_HOST = "localhost";
 $DB_USER = "CENSORED";
 $DB_PASS = "CENSORED";
 $DB_NAME = "seating";
 
 $data = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 if (mysqli_connect_errno()) {
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 } else {
 	print "\nConnected to database\n";
 }
 
 //test assignment
 $jobname = "test1";
 $students_list = "students_temp";
 
 new_job($data, $jobname);
 assign_seats($data, $jobname, $students_list, 
 	"LENGTH(row)=2 ORDER BY id;", 
 	"form='II' ORDER BY last");
 	
 $data->close();

/*
1. Clone the template table (fixed seat fields and open student fields).
	Require a 'job name' for naming the clone
		CREATE TABLE $jobname LIKE seating.seats_template;
		INSERT $jobname SELECT * FROM seating.seats_template;
*/

function new_job($db, $jobname) {
	
	//prepare 
	/*if ($stmt1 = $db->prepare("CREATE TABLE ? LIKE seats_template")
		&& $stmt2 = $db->prepare("INSERT ? SELECT * FROM seats_template")) {
		$stmt1->bind_param("s", $jobname);  $stmt2->bind_param("s", $jobname);
		$stmt1->execute();					$stmt2->execute();
		$stmt1->close();					$stmt2->close();
	 } else {
		printf("Failed to create new job: \n%s\n", mysqli_error($db));
		exit(1);
	 }*/
	 if (!$db->query("create table $jobname like seats_template") 
	  	|| !$db->query("insert $jobname select * from seats_template")) {
	  	print("---FAILED: new_job($jobname)---");
	 	die(mysqli_error($db));
	 }
}

/*
2. Freshman seating: mezzanine with no other restrictions.
	Note: Freshman seating begins at ID #625. (get dynamically?)
		(get whole mezzanine) SELECT * FROM seating.seats WHERE LENGTH(row)=2 ORDER BY id;
	Select and return all freshmen alphabetically.  Require a student $list as input.
		(get freshmen) SELECT * FROM $list WHERE form='II' ORDER BY last;
	Iterate through results, updating the seat at each ID with the student info.
		Check that there are not too few seats
		Extract $first, $last, and $form
		(update) UPDATE $jobname SET first=$first, last=$last, form=$form WHERE id=[begin at 625]
*/

//generic assignment function
function assign_seats($db, $jobname, $student_list, $seats_where, $students_where) {
	if ($student_selection = $db->prepare("SELECT last,first,form FROM ? WHERE " . $students_where)
	    && $seat_selection = $db->prepare("SELECT row,section,number,id FROM ? WHERE " . $seats_where)) {
		
		$student_selection->bind_param("s", $student_list);
		$seat_selection->bind_param("s", $jobname);
		
		$student_selection->execute();
		$seat_selection->execute();
		
		$last = ""; $first = ""; $form = ""; 
		$row = ""; $section = ""; $number = ""; $id = "";
		$student_selection->bind_result($last, $first, $form);
		$seat_selection->bind_result($row, $section, $number, $id);
		
		/* Fetching and assignment logic */
		while($student_selection->fetch()) {
			$seat_selection->fetch();
			
			print str_pad(sprintf("%s %s (%s)", $first, $last, $form), 40, " ", STR_PAD_RIGHT)
				. "| " . sprintf("%s %s %s (ID: %s)", $section, $row, $number, $id);
			
			//printf("UPDATE %s SET first=%s, last=%s, form=%s WHERE id=%s", $jobname, $first, $last, $id);
			//$db->query("UPDATE $jobname SET first=$first, last=$last, form=$form WHERE id=$id");
		}
		
		$student_selection->close();
		$seat_selection->close();
	} else {
		printf("---FAILED: assign_seats($jobname, $student_list)--- \n%s\n", mysqli_error($db));
		exit(1);
	}
}

/*
3. StuCo seating: front left (row B, wing L) and then front center (row A, wing C from the right)
	Front left seats are ID #28-34 and front center seats are ID #1-10.
		(get front left/cent) SELECT * FROM seating.seats WHERE (row='B' AND section='L') OR (row='A' AND section='C') ORDER BY id DESC;
	Select and return all StuCo members alphabetically.
		(get stuco) SELECT * FROM $list WHERE student_note='stuco' ORDER BY last;
	Iterate through results, adding first to front left then to front center (from the LEFT side)
		(update) UPDATE $jobname SET first=$first, last=$last, form=$form WHERE id=$id;

4. Prefect seating: 
	Choose EMPTY front center seats and return sorted by ID.
		(get front cent all) SELECT * FROM seating.seats WHERE section='C' AND last=NULL ORDER BY id;
	Select and return all prefects alphabetically
		(get prefects) SELECT * FROM $list WHERE student_note LIKE 'prefect%' ORDER BY last;
	Iterate through results, adding one by one
		UPDATE $jobname SET first=$first, last=$last, form=$form WHERE id=$id;

5. Regular seating: 
	Choose EMPTY main house seats and return sorted by ID.
		(get house all) SELECT * FROM seating.seats WHERE LENGTH(row)=1 AND last=NULL ORDER BY id;
	Choose students who have not been assigned yet, order by form and then by last name.
		SELECT * FROM $jobname WHERE last=NULL ORDER BY form,last;
	Iterate through results, adding one by one
		UPDATE $jobname SET first=$first, last=$last, form=$form WHERE id=$id;
		
6. 


*/

?>
	