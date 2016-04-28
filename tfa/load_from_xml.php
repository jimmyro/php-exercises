<?php
/* PHP script for adding data from experimental TFA XML files to a MySQL database
 * July 30, 2014
 * 
 * NOTE: XML validation is not comprehensive.  Errors in formatting will cause failure and/or
 * unexpected output.
 */

/*							   *
 * 0.Initialization activities *
 * 							   */

 print "\nINITIALIZATION ACTIVITIES\n";

 $DB_NAME = 'tfa';
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
 
 $xml = simplexml_load_file(__DIR__ . '/../../datafiles/april_14.xml');
 if ($xml != null) { print "Loaded XML\n"; } 
 else { print "Error: Could not load XML"; exit(1); }
 
 /*                  	  *
  * 1. Create new tables  *
  *                 	  */
 //create issue ID from two-digit volume number and two-digit issue number
 $issue_id = str_pad($xml->meta->volume, 2, '0', STR_PAD_LEFT) . 
 			 str_pad($xml->meta->issue, 2, '0', STR_PAD_LEFT);
 			 
 print "Created issue ID $issue_id\n";

 //format SQL command to add a new issue entry to the issues index table
 if ($stmt = $db->prepare("INSERT INTO issues VALUES (?, ?, ?, ?, ?)")) {
 	$stmt->bind_param("sssss", 
 		$issue_id, 
 		$xml->meta->date, 
 		$xml->meta->volume, 
 		$xml->meta->issue, 
 		$xml->meta->plen
 	);
 	$stmt->execute();
 	$stmt->close();
 } else {
 	printf("Failed to add new entry to issue table: \n%s\n", mysqli_error($db));
 	exit(1);
 }
 
 print "Added entry for issue #$issue_id\n";
 
 //create masthead and content tables for the new issue
 $masthead = $issue_id . '_masthead';
 $content = $issue_id . '_content';
 $db->query("CREATE TABLE ".$masthead." (title VARCHAR(30), name VARCHAR(30), year TINYINT(2))")
 	or die("Failed to create new masthead table: " . mysqli_error($db));
 $db->query("CREATE TABLE ".$content." (article_id INT(8) NOT NULL AUTO_INCREMENT, author VARCHAR(30), year TINYINT(2), title VARCHAR(80), subtitle VARCHAR(200), page TINYINT(2) NOT NULL, plen TINYINT(2) NOT NULL, wlen INT(4) NOT NULL, type VARCHAR(20) NOT NULL, sec_title VARCHAR(80), sec_subtitle VARCHAR(200), content TEXT NOT NULL, PRIMARY KEY (article_id), UNIQUE (article_id))")
 	or die("Failed to create content table: " . mysqli_error($db));
 $db->query("ALTER TABLE ".$content." AUTO_INCREMENT=".$issue_id."00");

 print "Tables created for masthead ($masthead) and content ($content)\n";

 /*              *
  * 2. Masthead  *
  *              */
 print "\nMASTHEAD ENTRY\nBeginning entry on $masthead\n";
 foreach ($xml->masthead->pos as $pos) {
	 if ($stmt = $db->prepare("INSERT INTO $masthead VALUES (?, ?, ?)")) {
		$stmt->bind_param("ssi", 
			$pos['title'],
			$pos,
			$pos['year']
		);
		$stmt->execute();
		$stmt->close();
	 } else {
		printf("Failed to add new entry to $masthead: \n%s\n", mysqli_error($db));
		exit(1);
	 }
	 
	 print ".";
 }
 
 print "\nMasthead entry complete\n";
 
 /*             *
  * 3. Content  *
  *             */
  //Content table format:
  //article_id | author | year | title | subtitle | page | plen | wlen | type | sec_title | sec_subtitle | content
 print "\nCONTENT ENTRY\nBeginning entry on $content\n";
 
 $article_counter = 0;
 $items = $xml->xpath('//item'); //gets all <item> nodes, for some reason
 
 foreach ($items as $item) {
 	
 	//author and author's year
 	$article_author = $item->meta->author;
 	$article_author_year = $article_author['year'];
 	if ($article_author == '') { 
 		$article_author = 'Anonymous'; 
 		$article_author_year = null;
 	}
 	
 	//title and subtitle
 	$article_title = ($item->meta->title == '') ?
 		null : $item->meta->title;
 	$article_subtitle = ($item->meta->subtitle == '') ?
 		null : $item->meta->subtitle;
 		
 	//handle sectioned (not standalone) items
 	$parent = $item->xpath(".."); //returns the parent node, for some reason
 	$section_title = '';
 	$section_subtitle = '';
 	if ($parent[0]->getName() == 'section') {
 		$section_title = $parent[0]['title'];
 		$section_subtitle = $parent[0]['subtitle'];
 	}
 	
 	//serialize subsectioned articles
 	$article_text = '';
 	if ($item->body['sub'] == 'true') {
 		foreach ($item->body->sub as $sub) {
 			$article_text .= $sub['title'] . "\n\n" . $sub . "\n\n";
 		}
 	} else {
 		$article_text = $item->body;
 	}

	if ($stmt = $db->prepare("INSERT INTO $content (author,year,title,subtitle,page,plen,wlen,type,sec_title,sec_subtitle,content) VALUES (?,?,?,?,?,?,?,?,?,?,?)")) {
		$stmt->bind_param("sissiiissss",
			$article_author,
			$article_author_year,
			$item->meta->title,
			$item->meta->subtitle,
			$item->meta->page,
			$item->meta->page['plen'],
			$item->meta->page['wlen'],
			$item->meta->type,
			$section_title,
			$section_subtitle,
			$article_text
		);
		$stmt->execute();
		$stmt->close();
	 } else {
		printf("Failed to add new entry to $content: \n%s\n", mysqli_error($db));
		exit();
	 }
 	
 	$article_counter++;
 	print ".";
 }

 print "\nContent entry complete ($article_counter items added)\n";
 
 //Close 
 $db->close();
 print "\nProcess complete.\n";
?>