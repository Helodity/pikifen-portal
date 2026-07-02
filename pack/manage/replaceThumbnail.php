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
	
	if(!isset($_POST['packID'])){
		//Trying to modify an unset pack.
		header("Location:". $SITE_ROOT);
		die();
	}
	$packID = $_POST['packID'];

	//File validation
	try {
		//Ensure the account is not banned
		$accID = $_SESSION['accountID'];
		$query = "SELECT permissions FROM accounts where id = $accID";
		$result = $conn->query($query);
		
		if(!account_has_permission($accID, PERMISSIONS::UPLOAD_PACKS)) {
			throw new RuntimeException('Account is banned.');
		}
		if(!can_modify_pack($accID, $packID)) {
			throw new RuntimeException('Forbidden.');
		}

		// Check $_FILES['upfile']['error'] value.
		if (
			!isset($_FILES['thumbnail']['error']) ||
			is_array($_FILES['thumbnail']['error'])
		) {
			throw new RuntimeException('Invalid parameters.');
		}

		switch ($_FILES['thumbnail']['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException('No file sent.');
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException('Exceeded filesize limit.');
			default:
				throw new RuntimeException('Unknown error.');
		}

		// 5 Megabyte limit
		if ($_FILES['thumbnail']['size'] > 5000000) {
			throw new RuntimeException('Thumbnails cannot exceed 5 Megabytes!');
		}

		$validExtensions = 
		array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        );

		// Verify file extension
		$finfo = new finfo(FILEINFO_MIME_TYPE);

		if(FALSE === $ext = array_search(
			$finfo->file($_FILES['thumbnail']['tmp_name']), 
			$validExtensions, 
			true
		)) {
			throw new RuntimeException('Invalid file extension.');
		}

		$target_dir = $LOCAL_ROOT . "/packs/" . $packID  . "/";
		move_uploaded_file(
			$_FILES["thumbnail"]["tmp_name"], 
			$target_dir . "thumbnail.png"
		);
		


	} catch (RuntimeException $e) {
		$_SESSION['errors']['thumbnailUpload'] = $e->getMessage();
	} catch (ValueError $e) {
		$_SESSION['errors']['thumbnailUpload'] = "File is corrupted.";
	}
	header("Location: ../manage?id=" . $packID);

?>
