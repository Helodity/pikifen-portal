<?php
	session_start();
	include '../includes/database.php';

	$valid_request = true;
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "GET") {
		$valid_request = false;
	}

	//Ensure the pack ID is set
	if(!isset($_GET['id'])){
		$valid_request = false;
	}

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		$valid_request = false;
	}

    if(!$valid_request) { 
        var_dump(http_response_code(400));
        return;
    }


	$packID = $_GET['id'];
    $accountID = $_SESSION['accountID'];
    $reason = $_GET['reason'] ?? " ";

    $stmt = $conn->prepare("INSERT INTO reports (reporter_id, pack_id, reason) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $accountID, $packID, $reason);
    $stmt->execute();

?>
