<?php
	if(!defined("VALID_ACCESS")) {
		echo "NO DIRECT ACCESS";
		header("Location: ../index.php");
	}

	//TESTING CREDENTIALS
	//define("DB_HOST", "localhost");
	//define("DB_USER", "irc");
	//define("DB_PASS", "irc");
	//define("DB_NAME", "irc");

	//ACTUAL CREDENTIALS
	define("DB_HOST", "localhost");
	define("DB_USER", "roboteam_irc");
	define("DB_PASS", "PlBmile4DmrC");
	define("DB_NAME", "roboteam_irc");
?>