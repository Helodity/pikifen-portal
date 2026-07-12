<?php
	session_start();
	require '../includes/database.php';
	
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		$conn->close();
		header("Location: ../");
		die();
	}

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location: ../login");
		die();
	}
	//Make sure the account exists.
	if(!account_exists($_POST['userID'])) {
		$conn->close();
		header("Location: ../");
		die();
	}
	
	$targetID = $_POST['userID'];
	$accountID = $_SESSION['accountID'];
	
	//Ensure the user has permissions
	if(!account_has_permission($accountID, PERMISSIONS::MODIFY_USERS) || $targetID == $accountID) {
		header("Location: ../user?id=" . $targetID);
		die();
	}

	$permissions = $_POST['permissions'] ?? '';

	$newPerms = 0;
	for($i = 0; $i < count($permissions); $i++) {
		$newPerms += $permissions[$i];
	}

	$stmt = $conn->prepare("UPDATE accounts SET permissions = ? WHERE id = ?");
	$stmt->bind_param("ii", $newPerms, $targetID);
	$stmt->execute();

	header("Location: ../user?id=" . $targetID);

?>
