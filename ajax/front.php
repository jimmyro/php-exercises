<html>
	<head>AJAX BAFFLES ME
		 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		 <script type="text/javascript">
		 	$(document).ready(function() {
		 		$('#search_results').slideUp();
		 		$('#search_button').click(function(e) {
		 			e.preventDefault();
		 			ajax_search();
		 		});
		 		$('#search_term').keyup(function(e) {
		 			e.preventDefault();
		 			ajax_search();
		 		});
		 	});
		 	
		 	function ajax_search() {
		 		$('#search_results').show()
		 		var search_val = $('#search_term').val();
		 		$.post("./find.php", {search_term : search_val}, function(data) {
		 			if (data.length > 0) {
		 				$('#search_results').html(data);
		 			}
		 		});
		 	}
		 </script>
	</head>
	<body>
		<h1>Scour our phone directory.</h1>
		<form id="searchform" method="post">
			<div>
				<label for="search_term">Search name/phone</label> 
				<input type="text" name="search_term" id="search_term" /> 
				<input type="submit" value="search" id="search_button" /> 
			</div>
		</form>
		<div id="search_results"></div>
	</body>
</html>