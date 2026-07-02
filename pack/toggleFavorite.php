<?php
	session_start();
	include '../includes/database.php';

	$valid_toggle = true;
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "GET") {
		$valid_toggle = false;
	}

	//Ensure the pack ID is set
	if(!isset($_GET['id'])){
		$valid_toggle = false;
	}

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		$valid_toggle = false;
	}

	if($valid_toggle) {
		$packID = $_GET['id'];
		$accountID = $_SESSION['accountID'];

		if(!has_favorite($accountID, $packID)) {
			$stmt = $conn->prepare("INSERT INTO favorites (account_id, pack_id) VALUES (?, ?)");
			$stmt->bind_param("ii", $accountID, $packID);
			$stmt->execute();
		} else {
			//Already favorited. Remove it.
			$stmt = $conn->prepare("DELETE FROM favorites WHERE account_id = ? AND pack_id = ?");
			$stmt->bind_param("ii", $accountID, $packID);
			$stmt->execute();
		}
	}
	echo has_favorite($_SESSION['accountID'], $packID) ? "Starred ★ " : "Star ☆ ";
	echo get_favorite_count($packID);
?>
