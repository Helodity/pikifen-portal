<?php
	session_start();
	require '../includes/database.php';
	
	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "POST") {
		$conn->close();
		header("Location: ../pikifen");
		die();
	}

	$email = htmlspecialchars($_POST['email']) ?? '';
	$username = htmlspecialchars($_POST['username']) ?? '';
	$password = htmlspecialchars($_POST['password']) ?? '';
	$confirmPassword = htmlspecialchars($_POST['confirmPassword']) ?? '';

	// BEGIN VERIFICATION

	$has_errors = false;
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['errors']['email'] = 'Invalid email address!';
		$has_errors = true;
	} else {
		//Valid email, make sure it doesn't exist tho
		$query = "SELECT email FROM accounts WHERE email = '$email'";
		$results = mysqli_num_rows($conn->query($query));
		if($results > 0) {
			$_SESSION['errors']['email'] = 'Email is already in use!';
			$has_errors = true;
		}
	}

	if(strlen($username) < 4 || strlen($username) > 16) {
		$_SESSION['errors']['username'] = 'Username must be between 4 and 16 characters';
		$has_errors = true;
	} else {
		//Valid username, make sure it doesn't exist tho
		$query = "SELECT username FROM accounts WHERE username = '$username'";
		$results = mysqli_num_rows($conn->query($query));
		if($results > 0) {
			$_SESSION['errors']['username'] = 'Username is taken!';
			$has_errors = true;
		}
	}

	if(strlen($password) < 6) {
		$_SESSION['errors']['password'] = 'Password must be at least 6 characters';
		$has_errors = true;
	}

	if($password != $confirmPassword) {
		$_SESSION['errors']['confirmPassword'] = 'Passwords do not match';
		$has_errors = true;
	}
	
	//Errors in input. Return to account creation page to display them to the user.
	if($has_errors) {
		$conn->close();
		header("Location: ../createAccount");
		die();
	}

	//If we've gotten here, all our info is valid. Add the account and log the user in!
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);
	$stmt = $conn->prepare("INSERT INTO accounts (username, password, email) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $username, $hashed_password, $email);
	$stmt->execute();
	$result = $stmt->get_result();
	
	//Get the new account and log them in.
	$stmt = $conn->prepare("SELECT username, id FROM accounts WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();

	$_SESSION['accountID'] = ($result->fetch_assoc())['id'];

	header("Location: ../");

?>
