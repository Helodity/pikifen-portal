<?php
    include_once 'includes/functions.php';
    include_once 'includes/database.php';

    //Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location:" . $SITE_ROOT);
		die();
	}
	$accountID = $_SESSION['accountID'];
	
	//Ensure the user has permissions
	if(!account_has_permission($accountID, PERMISSIONS::MODIFY_OTHERS)) {
		header("Location:" . $SITE_ROOT);
		die();
	}

    $stmt = $conn->prepare("SELECT * FROM reports");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h1>THIS PAGE IS A WIP</h1>";

    while ($row = $result->fetch_assoc()) {
        var_dump($row);
    }

?>