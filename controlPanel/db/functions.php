<?php
include_once("../globalFunctions.php");
include_once("../sendEmail.php");
include_once("dbAccess.php");
define("keyStr", 'i08Fa13kGSie983F');

$function = "";
extract($_REQUEST);

#START REQUEST HANDLING =======================================================================================

callFunctionIfValidRequest('countRecords', array('tableName'), array('columnName', 'selector'));
callFunctionIfValidRequest('userExists', array('email', 'password'), null);
callFunctionIfValidRequest('getUser', null, array('email', 'userId'));
callFunctionIfValidRequest('getMenus', array('levelId'), null);
callFunctionIfValidRequest('registerUser', array('userName', 'contactNo', 'email', 'password'), array('faxNo'));
// callFunctionIfValidRequest('getValidationCode', array('userName', 'contactNo', 'email', 'userId'), null);
callFunctionIfValidRequest('validateUser', array('code', 'email'), null);
callFunctionIfValidRequest('getCompetition', null, array('competitionId'));
callFunctionIfValidRequest('registerTeam', array('teamInfo', 'participants', 'userId'), null);
callFunctionIfValidRequest('getTeams', null, array('email', 'userId', 'teamId'));
callFunctionIfValidRequest('getTeamMembers', null, array('teamId'));
callFunctionIfValidRequest('getPayments', null, array('paymentId', 'userId', 'orderBy'));
callFunctionIfValidRequest('getTeamPaid', array('paymentId'), null);
callFunctionIfValidRequest('countTeams', null, array('columnName', 'selector'));
callFunctionIfValidRequest('updatePaymentStatus', array('paymentId', 'statusId'), null);
callFunctionIfValidRequest('updateTeamStatus', array('teamId', 'statusId'), array('earlyBirdStatus'));
callFunctionIfValidRequest('getTeamsForCompetition', array('competitionId'), array('columnName', 'selector'));

#END REQUEST HANDLING =========================================================================================

#START UTILITY FUNCTIONS ======================================================================================

#To check if the variable names exists in the global scope
function variablesExists($args) {
	foreach ($args as $key => $value) {
		global $$value;

		if(!isset($$value)) {
			return false;
		}
	}

	return true;
}

#To create variable with default value of "" if not exists
function createVariablesIfNotExists($args) {
	foreach ($args as $key => $value) {
		global $$value;

		if(!isset($$value)) {
			$$value = "";
		}
	}
}

#To print a message when an operation failed
function failedOperation($message) {
	if(!isset($message)) {
		$message = 'Invalid request';
	}
	echo json_encode(array('success' => false, 'message' => $message));
}

#To call the function if all rules are fulfilled
function callFunctionIfValidRequest($functionName, $requiredVariables, $optionalVariables) {
	global $function;

	if($function == $functionName) {
		$params = array();

		if(isset ($requiredVariables)) {
			if(!variablesExists($requiredVariables)) {
				failedOperation('Not enough parameter');
				return false;
			}
			else {
				$params = $requiredVariables;
			}
		}

		if(isset ($optionalVariables)) {
			createVariablesIfNotExists($optionalVariables);
			$params = array_merge($params, $optionalVariables);
		}

		$returnedValue = $functionName($params);

		if(isset($returnedValue)) {
			echo json_encode($returnedValue);
		}
	}
}

#END UTILITY FUNCTIONS ========================================================================================

#START BUSINESS LOGIC FUNCTIONS ===============================================================================

#To retrieve number of rows in a table
function countRecords($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT COUNT(*) AS recordCount FROM $tableName";

	if($connection = openConnection()) {
		if($columnName != '') {
			$sql .= " WHERE $columnName = ?";

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $selector);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_array($rows)) {
						$result["recordCount"] = $row["recordCount"];
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while($row = mysqli_fetch_array($rows)) {
				$result["recordCount"] = $row["recordCount"];
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To check if user exists with the credentials
function userExists($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT COUNT(*) AS userCount FROM USERS WHERE email = ? AND password = AES_ENCRYPT(?, UNHEX(SHA2('".keyStr."', 256)))";

	if($connection = openConnection()) {
		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "ss", $email, $password);

			if(mysqli_stmt_execute($stmt)) {
				$rows = mysqli_stmt_get_result($stmt);

				$result["userExists"] = false;
				while ($row = mysqli_fetch_array($rows)) {
					if($row["userCount"] == 1) {
						$result["userExists"] = true;
					}
				}

				$result["success"] = true;
			}
			else {
				failedOperation("Failed to execute prepared statement");
			}

			mysqli_stmt_close($stmt);
		}
		else {
			failedOperation("Failed to prepare statement");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the user
function getUser($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT userId, userName, contactNo, faxNo, email, levelId, statusId FROM USERS";

	$filter = "";

	if($email != "") {
		$filter = $email;
		$sql .= " WHERE email = ?";
	}
	else if($userId != "") {
		$filter = $userId;
		$sql .= " WHERE userId = ?";
	}

	if($connection = openConnection()) {
		if($filter != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $filter);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get menu respective to the users' access level
function getMenus($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT * FROM SIDEBAR_LINKS A JOIN SIDEBAR_LINKS_ACCESS_LEVEL B ON A.linkId = B.linkId WHERE levelId = ?";

	if($connection = openConnection()) {
		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $levelId);

			if(mysqli_stmt_execute($stmt)) {
				$rows = mysqli_stmt_get_result($stmt);

				while ($row = mysqli_fetch_array($rows)) {
					array_push($result, $row);
				}

				$result["success"] = true;
			}
			else {
				failedOperation("Failed to execute prepared statement");
			}

			mysqli_stmt_close($stmt);
		}
		else {
			failedOperation("Failed to prepare statement");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To register the user
function registerUser($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;

	if($connection = openConnection()) {
		mysqli_autocommit($connection, false);

		$sql = "INSERT INTO USERS VALUES (null, ?, ?, ?, ?, AES_ENCRYPT(?, UNHEX(SHA2('".keyStr."', 256))), 3, DEFAULT)";

		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "sssss", $userName, $contactNo, $faxNo, $email, $password);
			$queryResult1 = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}

		$sql = "INSERT INTO VALIDATION_CODE VALUES (null, ?, ?)";
		$randomString = generateRandomString();

		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "ss", mysqli_insert_id($connection), $randomString);
			$queryResult2 = mysqli_stmt_execute($stmt);
			mysqli_stmt_close($stmt);
		}

		if($queryResult1 == 1 && $queryResult2 == 1) { //If both query successfully executed
			mysqli_commit($connection);
			$result['success'] = true;

			sendRegistrationEmail($userName, $email, $randomString);
		}
		else {
			mysqli_rollback($connection);
			failedOperation("Failed to execute queries");
		}
		
		mysqli_autocommit($connection, true);
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To retrieve the validation code of the user
// function getValidationCode($args) {
// 	foreach ($args as $key => $value) {
// 		global $$value;
// 	}

// 	$result["code"] = "";
// 	$result["success"] = false;

// 	$sql = "SELECT code FROM VALIDATION_CODE A JOIN USERS B ON A.userId = B.userId WHERE A.userId = ? AND userName = ? AND contactNo = ? AND email = ?";

// 	if($connection = openConnection()) {
// 		if ($stmt = mysqli_prepare($connection, $sql)) {
// 			mysqli_stmt_bind_param($stmt, "ssss", $userId, $userName, $contactNo, $email);

// 			if(mysqli_stmt_execute($stmt)) {
// 				$rows = mysqli_stmt_get_result($stmt);

// 				while ($row = mysqli_fetch_assoc($rows)) {
// 					$result["code"] = $row["code"];
// 				}

// 				$result["success"] = true;
// 			}
// 			else {
// 				failedOperation("Failed to execute prepared statement");
// 			}

// 			mysqli_stmt_close($stmt);
// 		}
// 		else {
// 			failedOperation("Failed to prepare statement");
// 		}

// 		closeConnection($connection);

// 		if($result["success"]) {
// 			return $result;
// 		}
// 	}
// 	else {
// 		failedOperation("Cannot connect to database");
// 	}
// }

#To validate user
function validateUser($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;

	if($connection = openConnection()) {
		mysqli_autocommit($connection, false);

		$sql = "SELECT A.userId FROM VALIDATION_CODE A JOIN USERS B ON A.userId = B.userId WHERE email = ? AND code = ?";
		$recordCount = 0;
		$userId = "";

		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "ss", $email, $code);
			$queryResult1 = mysqli_stmt_execute($stmt);
			$rows = mysqli_stmt_get_result($stmt);
			$recordCount = mysqli_num_rows($rows);

			while ($row = mysqli_fetch_assoc($rows)) {
				$userId = $row["userId"];
			}

			mysqli_stmt_close($stmt);
		}

		if($recordCount == 1) {
			$sql = "DELETE FROM VALIDATION_CODE WHERE userId = ?";
			$randomString = generateRandomString();

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $userId);
				$queryResult2 = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			}

			$sql = "UPDATE USERS SET statusId = 'VU' WHERE userId = ?";
			$randomString = generateRandomString();

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $userId);
				$queryResult3 = mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			}

			if($queryResult1 == 1 && $queryResult2 == 1 && $queryResult3 == 1) {
				mysqli_commit($connection);
				$result['success'] = true;
			}
			else {
				mysqli_rollback($connection);
				failedOperation("Failed to execute queries");
			}
		}
		else {
			failedOperation("Invalid code");
		}

		mysqli_autocommit($connection, true);
		closeConnection($connection);

		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the competition
function getCompetition($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT * FROM COMPETITIONS";

	if($connection = openConnection()) {
		if($competitionId != '') {
			$sql .= " WHERE competitionId = ?";

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $competitionId);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To register team and participants
function registerTeam($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;

	if($connection = openConnection()) {
		mysqli_autocommit($connection, false);

		$querySuccess = array();

		// START INSERT TEAM ###############################################################################################################
		$sql = "INSERT INTO TEAM VALUES (null, ?, ?, ?, DEFAULT, DEFAULT, ?)";

		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "ssss", $teamInfo["teamName"], $teamInfo["institution"], $teamInfo["competitionId"], $userId);
			array_push($querySuccess, mysqli_stmt_execute($stmt));
			mysqli_stmt_close($stmt);
		}

		$teamId = mysqli_insert_id($connection);
		// END INSERT TEAM #################################################################################################################

		// START INSERT PARTICIPANTS #######################################################################################################
		$participantIds = array();

		foreach ($participants as $key => $participant) {
			$sql = "SELECT * FROM PARTICIPANTS WHERE icNumber = ?";

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $participant["icNumber"]);
				array_push($querySuccess, mysqli_stmt_execute($stmt));
				$rows = mysqli_stmt_get_result($stmt);
				$recordCount = mysqli_num_rows($rows);

				if($recordCount == 1) {
					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($participantIds, $row["participantId"]);
					}
				}

				mysqli_stmt_close($stmt);

				if($recordCount == 0) {
					$sql = "INSERT INTO PARTICIPANTS VALUES (null, ?, ?, ?)";

					if ($stmt = mysqli_prepare($connection, $sql)) {
						mysqli_stmt_bind_param($stmt, "sss", $participant["icNumber"], $participant["name"], $participant["tShirtSize"]);
						array_push($querySuccess, mysqli_stmt_execute($stmt));
						mysqli_stmt_close($stmt);

						array_push($participantIds, mysqli_insert_id($connection));
					}
				}
			}
		}
		// END INSERT PARTICIPANTS #########################################################################################################

		// START INSERT TEAM_FORMATION #####################################################################################################
		foreach ($participantIds as $key => $id) {
			$sql = "INSERT INTO TEAM_FORMATION VALUES (null, ?, ?)";

			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "ss", $teamId, $id);
				array_push($querySuccess, mysqli_stmt_execute($stmt));
				mysqli_stmt_close($stmt);
			}
		}
		// END INSERT TEAM_FORMATION #######################################################################################################

		$allQueriesExecuted = true;

		foreach ($querySuccess as $key => $value) {
			if($value == false) {
				$allQueriesExecuted = false;
			}
		}

		if($allQueriesExecuted) {
			mysqli_commit($connection);
			$result['success'] = true;
		}
		else {
			mysqli_rollback($connection);
			failedOperation("Failed to execute queries");
		}

		mysqli_autocommit($connection, true);
		closeConnection($connection);

		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the teams
function getTeams($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT teamId, teamName, institution, competitionId, earlyBird, A.statusId AS teamStatus, A.userId, userName, contactNo, faxNo, email";
	$sql .= " FROM TEAM A JOIN USERS B ON A.userId = B.userId";

	$filter = "";

	if($email != "") {
		$filter = $email;
		$sql .= " WHERE email = ?";
	}
	else if($userId != "") {
		$filter = $userId;
		$sql .= " WHERE A.userId = ?";
	}
	else if($teamId != "") {
		$filter = $teamId;
		$sql .= " WHERE teamId = ?";
	}

	$sql .= " ORDER BY A.userId, teamId";

	if($connection = openConnection()) {
		if($filter != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $filter);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the teammates
function getTeamMembers($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT teamFormationId, teamId, A.participantId, icNumber, participantName, tShirtSize";
	$sql .= " FROM TEAM_FORMATION A JOIN PARTICIPANTS B ON A.participantId = B.participantId";

	$filter = "";

	if($teamId != "") {
		$filter = $teamId;
		$sql .= " WHERE teamId = ?";
	}

	$sql .= " ORDER BY teamId, participantId";

	if($connection = openConnection()) {
		if($filter != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $filter);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the payment info
function getPayments($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT paymentId, paymentFrom, paymentDate, paymentAmount, refNo, fileId, A.statusId AS paymentStatus, A.userId, userName, contactNo, faxNo, email, levelId, B.statusId AS userStatus";
	$sql .= " FROM PAYMENT A JOIN USERS B ON A.userId = B.userId";

	$filter = "";

	if($paymentId != "") {
		$filter = $paymentId;
		$sql .= " WHERE paymentId = ?";
	}
	else if($userId != "") {
		$filter = $userId;
		$sql .= " WHERE A.userId = ?";
	}

	if($orderBy == "") {
		$orderBy = "paymentId";
	}

	$sql .= " ORDER BY ?";

	if($connection = openConnection()) {
		if($filter != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "ss", $filter, $orderBy);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $orderBy);

				if(mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}

					$result["success"] = true;
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get the teams paid in the same payment
function getTeamPaid($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT * FROM TEAM_REGISTRATION WHERE paymentId = ?";

	if($connection = openConnection()) {
		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "s", $paymentId);

			if($result["success"] = mysqli_stmt_execute($stmt)) {
				$rows = mysqli_stmt_get_result($stmt);

				while ($row = mysqli_fetch_assoc($rows)) {
					array_push($result, $row);
				}
			}
			else {
				failedOperation("Failed to execute prepared statement");
			}

			mysqli_stmt_close($stmt);
		}
		else {
			failedOperation("Failed to prepare statement");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To retrieve number of teams in each category
function countTeams($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT A.competitionId, competitionName, COUNT(*) AS recordCount FROM TEAM A JOIN COMPETITIONS B ON A.competitionId = B.competitionId";

	if($columnName != "") {
		$sql .= " WHERE $columnName = ?";
	}

	$sql .= " GROUP BY A.competitionId";

	if($connection = openConnection()) {
		if($columnName != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "s", $selector);

				if($result["success"] = mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while ($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To update payment status
function updatePaymentStatus($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "UPDATE PAYMENT SET statusId = ?, dateApproved = now() WHERE paymentId = ?";

	if($connection = openConnection()) {
		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "ss", $statusId, $paymentId);

			if(!($result["success"] = mysqli_stmt_execute($stmt))) {
				failedOperation("Failed to execute prepared statement");
			}

			mysqli_stmt_close($stmt);
		}
		else {
			failedOperation("Failed to prepare statement");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To update team status
function updateTeamStatus($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;

	if($earlyBirdStatus == "") {
		$earlyBirdStatus = "0";
	}

	$sql = "UPDATE TEAM SET statusId = ?, earlyBird = ? WHERE teamId = ?";

	if($connection = openConnection()) {
		if ($stmt = mysqli_prepare($connection, $sql)) {
			mysqli_stmt_bind_param($stmt, "sis", $statusId, $earlyBirdStatus, $teamId);

			if(!($result["success"] = mysqli_stmt_execute($stmt))) {
				failedOperation("Failed to execute prepared statement");
			}

			mysqli_stmt_close($stmt);
		}
		else {
			failedOperation("Failed to prepare statement");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#To get all the teams that compete in the specific competition
function getTeamsForCompetition($args) {
	foreach ($args as $key => $value) {
		global $$value;
	}

	$result["success"] = false;
	$sql = "SELECT A.competitionId, competitionName, maxParticipants, maxTeams, fee, firstPrize, secondPrize, thirdPrize, earlyBirdAllocations, earlyBirdDiscount, teamId, teamName, institution, earlyBird, statusId, userId FROM COMPETITIONS A JOIN TEAM B ON A.competitionId = B.competitionId WHERE A.competitionId = ?";

	if($columnName != "") {
		$sql .= " AND $columnName = ?";
	}
	else {
		$columnName = "A.competitionId";
		$sql .= " AND $columnName != ?";
		$selector = "0";
	}

	$sql .= " ORDER BY earlyBird DESC, teamId";

	if($connection = openConnection()) {
		if($columnName != '') {
			if ($stmt = mysqli_prepare($connection, $sql)) {
				mysqli_stmt_bind_param($stmt, "ss", $competitionId, $selector);

				if($result["success"] = mysqli_stmt_execute($stmt)) {
					$rows = mysqli_stmt_get_result($stmt);

					while ($row = mysqli_fetch_assoc($rows)) {
						array_push($result, $row);
					}
				}
				else {
					failedOperation("Failed to execute prepared statement");
				}

				mysqli_stmt_close($stmt);
			}
			else {
				failedOperation("Failed to prepare statement");
			}
		}
		else if($rows = executeQuery($connection, $sql)) {
			while ($row = mysqli_fetch_assoc($rows)) {
				array_push($result, $row);
			}

			$result["success"] = true;
		}
		else {
			failedOperation("Query execution failed");
		}
		
		closeConnection($connection);
		
		if($result["success"]) {
			return $result;
		}
	}
	else {
		failedOperation("Cannot connect to database");
	}
}

#END BUSINESS LOGIC FUNCTIONS =================================================================================
?>
