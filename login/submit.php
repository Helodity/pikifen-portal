<?php
	session_start();
	require '../includes/database.php';
	
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		$conn->close();
		header("Location: ../pikifen");
		die();
	}

	$username = htmlspecialchars($_POST['username']) ?? '';
	$password = htmlspecialchars($_POST['password']) ?? '';

	$stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();

	$targetAccount = $result->fetch_assoc();
	if(mysqli_num_rows($result) == 0 || !password_verify($password, $targetAccount['password'])) {
		//Incorrect combo
		$_SESSION['errors']['login'] = "Invalid Username password combo";
		$conn->close();
		header("Location: ../login");
		die();
	}

	//Correct info, login and redirect
	$_SESSION['accountID'] = $targetAccount['id'];

	header("Location: ../");

?>
