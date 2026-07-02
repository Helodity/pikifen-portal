<?php
	include "includes/globals.php";
	session_start();

	unset($_SESSION['accountID']);
	
	header("Location:" . $SITE_ROOT);
?>
