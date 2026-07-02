<?php
	session_start();
	include '../../includes/globals.php';
	include '../../includes/functions.php';
	include_once '../../includes/database.php';

	header('Content-Type: text/plain; charset=utf-8');
	//Checks we can't even show errors for.
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		//If the method wasnt set right, return to the home page
		header("Location: ". $SITE_ROOT);
		die();
	}
	
	if(!isset($_SESSION['accountID'])){
		//Require the user to be logged in.
		//Can't get here normally without being logged in, so redirect to home page.
		header("Location:". $SITE_ROOT);
		die();
	}
	
	if(!isset($_POST['versionID']) || !is_numeric($_POST['versionID'])){
		//Trying to modify an unset pack.
		header("Location:". $SITE_ROOT);
		die();
	}
	$versionID = $_POST['versionID'];

	//Get packID from version
	$stmt = $conn->prepare("SELECT pack_id, engine_version, pack_version FROM pack_versions where id = ?");
	$stmt->bind_param("i", $versionID);
	$stmt->execute();

	$result = $stmt->get_result();

	$row = $result->fetch_assoc();
	$packID = $row['pack_id'];
	$engineVersion = $row['engine_version'];
	$packVersion = $row['pack_version'];

	if(!isset($packID)) {
		//Could not find pack this version belongs to.
		header("Location:". $SITE_ROOT);
		die();
	}

	//Validation based on PHP's website
	try {
		//Ensure the account is not banned
		//Banned accounts have their packs deleted, so they dont need access.
		$accID = $_SESSION['accountID'];
		
		if(!can_modify_pack($accID, $packID)) {
			throw new RuntimeException('Forbidden.');
		}

		//Remove the pack from the database
		$stmt = $conn->prepare("DELETE FROM pack_versions WHERE id = ?");
		$stmt->bind_param("i", $versionID);
		$stmt->execute();

		//Delete the file off disk
		$target_dir = "../../packs/" . $packID . "/";
		unlink($target_dir . getPackFilename($packID, $packVersion, $engineVersion));



	} catch (RuntimeException $e) {
		$_SESSION['errors']['deleteVersion'] = $e->getMessage();
	}
	header("Location: ../manage?id=" . $packID);

?>
