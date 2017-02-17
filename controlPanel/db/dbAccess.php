<?php
	define("VALID_ACCESS", TRUE);
	include_once("credentials.php");

	function openConnection() { 
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
		if(!$connection) {
			echo("Connection failed: ".mysqli_connect_error());
		}
		
		return $connection;
	}

	function closeConnection($connection) {
		mysqli_close($connection);
	}

	function executeQuery($connection, $sql) {
		$result = mysqli_query($connection, $sql);
		
		// if(!$result) {
		// 	echo mysqli_error($connection);
		// }
		
		return $result;
	}
?>