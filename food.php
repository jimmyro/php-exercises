<?php

/* ----------------------------
   Preset Arrays
   ---------------------------- */
   
$sweets = array('puff'		=> 'Sesame Seed Puff',
				'square'	=> 'Coconut Milk Gelatin Square',
				'cake'		=> 'Brown Sugar Cake',
				'ricemeat' 	=> 'Sweet Rice and Meat');
				
$main_dishes = array('cuke'		=> 'Braised Sea Cucumber',
					 'stomach'	=> "Sauteed Pig's Stomach",
					 'tripe'	=> 'Sauteed Tripe with Wine Sauce',
					 'taro'		=> 'Stewed Pork with Taro',
					 'giblets'	=> 'Baked Giblets with Salt',
					 'abalone' 	=> 'Abalone with Marrow and Duck Feet');

/* ----------------------------
   Main Page Logic
   ---------------------------- */
   
//IF the form is submitted THEN validate and process or display
//ELSE display
if ($_POST['_submit_check']) {
	if ($form_errors = validate_form()) {
		show_form($form_errors);
	} else {
		//submitted data is valid, so process it
		process_form();
	}
} else {
	//form wasn't submitted, so display
	show_form();
}

function show_form($errors = '') {
	//If form is submitted, get defaults from submitted parameters
	if ($_POST['_submit_check']) {
		$defaults = $_POST;
	} else {
		//otherwise, set our own defaults: medium size and yes to delivery
		$defaults = array('delivery'	=> 'yes',
						  'size'		=> 'medium');
	}
	
	//If errors were passed in, put them in $error_text (with HTML markup)
	if ($errors) {
		$error_text = '<tr><td>You need to correct the following errors:';
		$error_text .= '</td><td><ul><li>';
		$error_text .= implode('</li><li>', $errors);
		$error_text .= '</li></ul></td></tr>';
	} else {
		$error_text = '';
	}
	
//Flitting in and out of PHP mode to make displaying html tags easier
?> 
<form method="post" action="<?php print $_SERVER['PHP_SELF'] ?>">
<table>
<?php print $error_text; ?>

<tr><td>Your name:</td>
<td><?php input_text('name', $defaults) ?></td></tr>

<tr><td>Size:</td>
<td><?php input_radiocheck('radio', 'size', $defaults, 'small'); ?> Small <br/>
<?php input_radiocheck('radio', 'size', $defaults, 'medium'); ?> Medium <br/>
<?php input_radiocheck('radio', 'size', $defaults, 'large'); ?> Large
</td></tr>

<tr><td>Pick one sweet item:</td>
<td><?php input_select('sweet', $defaults, $GLOBALS['sweets']); ?>
</td></tr>

<tr><td>Pick two main dishes:</td>
<td><?php input_select('main_dish', $defaults, $GLOBALS['main_dishes'], true); ?>
</td></tr>

<tr><td>Do you want your order delivered?</td>
<td><?php input_radiocheck('checkbox','delivery', $defaults, 'yes'); ?> Yes
</td></tr>

<tr><td>Enter any special instructions.<br/>
If you want your order delivered, put your address here:</td>
<td><?php input_textarea('comments', $defaults); ?>
</td></tr>

<tr><td colspan="2" align="center">
<?php input_submit('save', 'Order'); ?>
</td></tr>

</table>
<input type="hidden" name="_submit_check" value="1"/>
</form>

<?php
//resuming main page logic
} //end of show_form()

function validate_form() {
	if (!strlen(trim($_POST['name']))) {
		$errors[] = 'Please enter your name.';
	}
	if (($_POST['size'] != 'small') && ($_POST['size'] != 'medium')
	&& ($_POST['size'] != 'large')) {
		$errors[] = 'Please select a size.';
	}
	if (! array_key_exists($_POST['sweet'], $GLOBALS['sweets'])) {
		$errors[] = 'Please select a valid sweet item.';
	}
	if (count($_POST['main_dish'] != 2)) {
		$errors[] = 'Please select exactly two main dishes.';
	} else {
		if (! (array_key_exists($_POST['main_dish'][0],$GLBOBALS['main_dishes']) && 
			  (array_key_exists($_POST['main_dish'][0],$GLBOBALS['main_dishes'])))) {
			$errors[] = 'Please select exactly two valid main dishes.';
		}
	}
	if (($_POST['delivery'] == 'yes') && (! strlen(trim($_POST['comments'])))) {
		$errors[] = 'Please enter your address for delivery.';
	}
	
	return $errors;
}

function process_form() {
	//look up the full names of the selected items
	$sweet = $GLOBALS['sweets'][ $_POST['sweet'] ];
	$main_dish_1 = $GLOBALS['main_dishes'][ $_POST['main_dish'][0] ];
	$main_dish_2 = $GLOBALS['main_dishes'][ $_POST['main_dish'][1] ];
	
	if ($_POST['delivery'] == 'yes') {
		$delivery = 'do';
	} else {
		$delivery = 'do not';
	}
	
	//build text order of the message
	$message=<<<_ORDER_
Thank you for your order, $_POST[name].
You requested the $_POST[size] size of $sweet, $main_dish_1, and $main_dish_2.
You $delivery want delivery.
_ORDER_;
	if (strlen(trim($_POST['comments']))) {
		$message .= 'Your comments: ' . $_POST['comments'];
	}
	
	//communication logic goes here
	
	print nl2br(htmlentities($message));
}

/* ----------------------------
   Helper Functions
   ---------------------------- */

//print a text box
function input_text($element_name, $values) {
	print '<input type="text" name="' . $element_name . '" value="';
	print htmlentities($values[$element_name]) . '"/>';
}

//print a submit button
function input_submit($element_name, $label) {
	print '<input type="submit" name="' . $element_name . '" value="';
	print htmlentities($label) . '"/>';
}

//print a textarea
function input_textarea($element_name, $values) {
	print '<textarea name="' . $element_name . '">';
	print htmlentities($values[$element_name]) . '</textarea>';
}

//print a radio button or checkbox
function input_radiocheck($type, $element_name, $values, $element_value) {
	print '<input type="' . $type . '" name="' . $element_name . '" value="' 
		. $element_value . '" ';
	if ($element_value == $values[$element_name]) {
		print ' checked="checked"';
	}
	print '/>';
}

//print a <select> menu
function input_select($element_name, $selected, $options, $multiple=false) {
	//print out the <select> tag
	print '<select name="' . $element_name;
	//if multiple choices are permitted, add the multiple attribute
	//and add a [] to the end of the tag name
	if ($multiple) { print '[]" multiple="multiple'; }
	print '">';
	
	//set up the list of things to be selected
	$selected_options = array();
	if ($multiple) {
		foreach ($selected[$element_name] as $value) {
			$selected_options[$value] = true;
		}
	} else {
		$selected_options[ $selected[$element_name] ] = true;
	}
	
	//print out the <option> tags
	foreach ($options as $option => $label) {
		print '<option value="' . htmlentities($option) . '"';
		if (isset($selected_options[$option])) {
			print ' selected="selected"';
		}
		print '>' . htmlentities($label) . '</option>';
	}
	print '</select>';
}

?>