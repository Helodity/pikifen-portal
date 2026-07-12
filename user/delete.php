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
	if(!account_has_permission($accountID, PERMISSIONS::MODIFY_USERS) && $targetID != $accountID) {
		header("Location: ../");
		die();
	}

    //Cache this account's packs to delete them from storage
	$stmt = $conn->prepare("SELECT * FROM packs WHERE account_id = ?");
	$stmt->bind_param("i", $targetID);
	$stmt->execute();
	$packsToDelete = $stmt->get_result();

	//Remove the account from the database. This will cascade packs, versions, tags, and favorites,
	$stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
	$stmt->bind_param("i", $targetID);
	$stmt->execute();

	//Delete all pack data from storage.
	while ($row = $packsToDelete->fetch_assoc()) {
		$target_dir = "packs/" . $row["id"];
		//Clear directory
		$it = new RecursiveDirectoryIterator($target_dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
					 RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
			if ($file->isDir()){
				rmdir($file->getPathname());
			} else {
				unlink($file->getPathname());
			}
		}
		//Delete the directory
		rmdir($target_dir);
	}

    unset($_SESSION['accountID']);
	header("Location: ../");

?>
