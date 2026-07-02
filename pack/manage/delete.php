<?php
	include_once '../../includes/database.php';
	include '../../includes/functions.php';
		
		
	header('Content-Type: text/plain; charset=utf-8');
	session_start();
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "GET") {
		header("Location: ../../");
		die();
	}

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location: ../../login");
		die();
	}
	$accountID = $_SESSION['accountID'];
	$packID = $_GET['id'];
	
	//Ensure the user has permissions
	if(!can_modify_pack($accountID, $packID)) {
		header("Location: ../../");
		die();
	}
				
	//Remove the pack from the database
	$stmt = $conn->prepare("DELETE FROM packs WHERE id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
		
	$target_dir = "../../packs/" . $packID;
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
		
	//Return to the main page
	header("Location: ../../");

?>
