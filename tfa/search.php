<?php
/* Web page for accessing MySQL database of TFA issues and articles.
  August 4, 2014

  
  Parameters:
   +------------------------
   |volume - the volumes in which to search
   |	limited to a dynamically obtained list of volumes available in the database
   |	<select> menu (limit to one volume at a time)
   |issue - the issues in which to search
   |	usable once volume is selected
   |	limited to a dynamically obtained list of issues available within selected volume
   |	<select> menu
   +------------------------
   		--> Can add more instances of these two fields to include more vol/issue
   			search pairs
  	type - item types to include in results
  		limited to hard-coded(?) list of types (article, ltte, feature, etc.)
  		checkboxes (with an "all" option?)
   +------------------------
   |keyword - the search term(s)
   |	text field --> simple, naïve search using SQL's 'LIKE' command
   |	no initial support for advanced options (wildcards, quotes, AND/OR, etc.)
   |fields - fields of the item (title, author, etc.) in which to search
   |	limited to title, author, content (checkboxes)
   +------------------------
   		--> Can add more instances of these two fields to include more search
   		    terms

   	Response:
   	Will hopefully include 'order by' options and controls
   	
   	<div id="results">
   		<table>
   			<tr><!--eight data points-->
   				<th>ID</th>
   				<th>Author</th>
   				<th>Title</th>
   				<th>Page</th>
   				<th>Words</th>
   				<th>Pages</th>
   				<th>Type</th>
   				<th>Section</th>
   				<th>View<th>
   					<!--Consists of links to tfa_view.php that pass in article ID-->
   			</tr>
   			<tr>
   				
   			</tr>
   		</table>
   	</div>
 */
 
 /* * * * * * * * * * * *
  * 0. DECLARATIONS
  * $_DEFAULTS_INIT: settings when search page is loaded
  * $_DEFAULTS_ALL: settings for searching all database records
  * $_TYPES: (hard-coded) lists applicable item types (regardless of availability)
  * $_FIELDS: (hard-coded) lists searchable fields (may become dynamic later?)
  * * * * * * * * * * * */
 $_DEFAULTS_INIT = array(
 	'volume' => 'all', 'issue' => 'all',
 	'type' => array(), 
 	'fields' => array(),
 	'keyword' => ''
 );
 $_DEFAULTS_ALL = array(
 	'volume' => 'all', 'issue' => 'all',
 	'type' => array('ltte', 'article', 'editorial', 'column', 'feature'), 
 	'fields' => array('title', 'author', 'content')
 );
 $_TYPES= array(
 	'ltte' => 'LTTE', 
 	'article' => 'article', 
 	'column' => 'column', 
 	'feature' => 'feature', 
 	'editorial' => 'editorial'
 );
 $_FIELDS = array(
 	'title' => 'title', 
 	'author' => 'author', 
 	'content' => 'content'
 );
 
 /* * * * * * * * * * * *
  * 1. MAIN
  * $db: read-only connection to the TFA database
  * $available_issues: lists available volumes and issues within each volume
  * * * * * * * * * * * */
 $db = new mysqli('cyl.ddns.net', 'tfa_readonly', 'amendment19', 'tfa');
 if (mysqli_connect_errno()) { 
 	//CLASSY ERROR HANDLING GOES HERE
 	printf("Connect failed: %s\n", mysqli_connect_error());
 	exit();
 }
 
 //get availability information
 $available_issues = array();
 $avaiable_ids = array();
 if (!$issues = $db->query("SELECT issue_id,volume,issue FROM issues")) {
 	//CLASSY ERROR HANDLING GOES HERE
 	printf("Availability retrieval failed: %s\n", mysqli_error($db));
 	exit();
 } else {
 	while ($row = $issues->fetch_assoc()) {
 		$v = $row['volume'];
 		if (! array_key_exists($v, $available_issues)) {
 			$available_issues[$v] = array();
 		}
 		$available_issues[$v][] = $row['issue'];
 		$available_ids[] = $row['issue_id'];
 	}
 }
 
 //form validation
 function validate_form() {	
 	$errors = array();
 	
 	//retrieve posts
 	$v = $_POST('volume'); $i = $_POST('issue');
 	$t = $_POST('type'); $f = $_POST('fields');
 	
 	//location
 	if (! $v || ! $i) {
 		$errors[] = "Please specify a search location";
 	} elseif ($i == 'all' && $v != 'all')
	    $errors[] = "Please specify a valid search location";
 	} elseif (! array_key_exists($v, $available_issues) || ! in_array($i, $available_issues[$v]))
 		$errors[] = "Specified search location is not available";
 	}
	//type (an array)
	if (empty($t)) {
		$errors[] = "Please specify an item type";
	} else {	
		foreach ($t as $type) {
			if (! in_array($type, $_TYPES)) {
				$errors[] = "Please specify a valid item type";
				break;
			}
		}
	}
	//fields (an array)
	if (empty($f)) {
		$errors[] = "Please specify a search field";
	} else {
		foreach ($t as $type) {
			if (! in_array($type, $_TYPES)) {
				$errors[] = "Please specify a valid search field";
				break;
			}
		}
	}
	
 	return $errors;
 }
 
 //process form (query database, sort and display results)
 function process_form() {	
	$v = $_POST['volume']; $i = $_POST['issue'];
	
	$keyword = $_POST['keyword']; //unsanitary
	$type = implode(',',$_POST['type']);
	$fields = implode(',',$_POST['fields']);
	
	//compute location string -- I really, really hate this method...
    $location = '';
    if ($i == 'all') {
    	if ($v == 'all') {
    		$location = implode('_content,',$available_ids);
    		$location .= '_content';
    	} else {
    		foreach ($location[$v] as $issue) {
    			$location .= str_pad($v,2,'0',STR_PAD_LEFT)
    						.str_pad($issue,2,'0',STR_PAD_LEFT)
    						.'_content,';
    		}
    		$location = substr($location, 0, -1); //clip extra comma off
    	}
    } else {
    	$location .= str_pad($v,2,'0',STR_PAD_LEFT)
    				.str_pad($i,2,'0',STR_PAD_LEFT)
    				.'_content';
    }
 	 
 	//9 fields: ID, Author, Title, Page, Words, Pages, Type, Section
	if ($stmt = $db->prepare("SELECT article_id, author, title, page, wlen, plen, type, sec_title FROM ? WHERE type=? AND ? LIKE ?")) {
		$stmt->bind_param("ssss", $location, $type, $fields, $keyword);
		$stmt->execute();
		
		/* Processing and output logic goes here
		 * Add logic or placeholder for view link (view.php?id= ...)
		 * may need auxiliary function to print <td>s or <tr>s
		 */
		
		$stmt->close();
	} else {
		printf("Query failed: %s", mysqli_error($db));
		exit(1);
	}
 }
 
?>
 