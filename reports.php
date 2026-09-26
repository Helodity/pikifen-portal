<?php
	session_start();
    include 'includes/functions.php';
    include_once 'includes/database.php';

    //Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location: ../login");
		die();
	}
	$accountID = $_SESSION['accountID'];
	
	//Ensure the user has permissions
	if(!account_has_permission($accountID, PERMISSIONS::MODIFY_OTHERS)) {
		header("Location: ../");
		die();
	}

    echo "its wip"

?>