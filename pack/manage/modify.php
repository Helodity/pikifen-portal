<?php
	session_start();
	include '../../includes/globals.php';
	include '../../includes/functions.php';
	include_once '../../includes/database.php';
	
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
	
	if(!isset($_POST['existingID'])){
		//Trying to modify an unset pack.
		header("Location:". $SITE_ROOT);
		die();
	}
	
	$accountID = $_SESSION['accountID'];
	$packID = $_POST['existingID'];

	if(!can_modify_pack($accountID, $packID)) {
		//Trying to modify an unowned pack. Return to manage page
		header("Location:". $SITE_ROOT . "/pack/manage/?id=" . $packID);
		die();
	}
	
	$newName = htmlspecialchars($_POST['name']);
	$newDescription = htmlspecialchars($_POST['description']);
	$newTags = htmlspecialchars($_POST['tags']);
	$setPublic = isset($_POST['public']) ? 1 : 0;

	replace_pack_tags($packID, $newTags);

	$stmt = $conn->prepare("UPDATE packs SET name = ?, description = ?, public = ? WHERE id = ?");
	$stmt->bind_param("ssii", $newName, $newDescription, $setPublic, $packID);
	$stmt->execute();

	header("Location: ../manage?id=" . $packID);

?>
