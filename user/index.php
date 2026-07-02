<?php
	include_once '../includes/functions.php';
	include_once '../includes/database.php';

	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "GET") {
		$conn->close();
		header("Location: ../");
		die();
	}
	
	if(!isset($_GET['id'])) {
		$conn->close();
		header("Location: ../");
		die();
	}
	
	$accountID = $_GET['id'];

	//Get the user's account
	$stmt = $conn->prepare("SELECT username FROM accounts WHERE id = ?");
	$stmt->bind_param("i", $accountID);
	$stmt->execute();
	$result = $stmt->get_result();

	//User does not exist. Return to home page
	if(mysqli_num_rows($result) == 0) {
		$conn->close();
		header("Location: ../");
		die();
	}

	$username = get_username($accountID);
	$PAGE_NAME = $username. "'s page";
	//Session is started here. 
	include '../includes/header.php';
	
	$ownAccount = isset($_SESSION['accountID']) ? ($_SESSION['accountID'] == $accountID) : false;
?>
<header>
	<h1><?= $username ?></h1>
</header>

<?php
	if($ownAccount) {
		$query = "SELECT * FROM packs WHERE account_id = ?";
	} else {
		$query = "SELECT * FROM packs WHERE account_id = ? AND public = TRUE";
	}
	$stmt = $conn->prepare($query);
	$stmt->bind_param("i", $accountID);
	$stmt->execute();
	$result = $stmt->get_result();

	make_gallery($username . "'s Packs", $result)
?>

<?php if(isset($_SESSION['accountID']) && account_has_permission($_SESSION['accountID'], PERMISSIONS::MODIFY_USERS)) { ?>

<h2>Manage User</h2>
<h3>Modify permissions</h3>
<form action="modify.php" method="post">
	<input type="checkbox" name="permissions[]" value="1" 
	<?php if(account_has_permission($accountID, PERMISSIONS::UPLOAD_PACKS)) {echo "checked";} ?>>Upload Packs<br>
	<input type="checkbox" name="permissions[]" value="2" 
	<?php if(account_has_permission($accountID, PERMISSIONS::MODIFY_OTHERS)) {echo "checked";} ?>>Modify Unowned Packs<br>
	<input type="checkbox" name="permissions[]" value="4" 
	<?php if(account_has_permission($accountID, PERMISSIONS::MODIFY_USERS)) {echo "checked";} ?>>Modify Users<br>
	
	<input class="button-main" type="submit" value="Submit">
	
	<input type='hidden' name="userID" value="<?= $accountID ?>">
</form>
<br>
<?php if(!$ownAccount) { ?>
<form action="ban.php" method="post">
<button type="submit" class="button-warning">Ban User</button>
<input type='hidden' name="userID" value="<?= $accountID ?>">
</form>
<p class="error-text">This will delete everything uploaded by this user, and prevent them from uploading anything new!</p>

<?php }} ?>



<?php include "../includes/footer.php" ?>
