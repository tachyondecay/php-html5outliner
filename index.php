<?php
namespace HTML5Outliner;

set_include_path(get_include_path() . PATH_SEPARATOR . 'HTML5Outliner');

require('Outline.php');
require('Section.php');
require('Heading.php');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>HTML5 Outliner</title>

	<style>
		body {
			font-family: Verdana, Arial, sans-serif;
			font-size: 1.2em;
		}

		h1, p {
			text-align: center;
		}
	</style>
</head>
<body>

<?php

if(isset($_POST['html'])) {
	$outline = Outline::loadHTML($_POST['html']);

	echo $outline->getHeadings();
?>
	<p><a href="/">Return to form</a></p>
<?php
} else {
?>
	<header>
		<h1>HTML5Outliner</h1>
	</header>

	<h1>Input <abbr title="hypertext markup language">HTML</abbr>:</h1>
	<form method="post">
		<p><textarea cols="100" rows="20" name="html"></textarea></p>
		<p><input type="submit" value="Submit"/></p>
 	</form>

 	<footer>
 		<p><a href="https://github.com/tachyondecay/html5outliner/">Fork me on GitHub</a></p>
 	</footer>
<?php
}
?>