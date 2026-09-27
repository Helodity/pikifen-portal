<?php
	include "includes/globals.php";

	unset($_SESSION['accountID']);
	
	header("Location:" . $SITE_ROOT);
?>
