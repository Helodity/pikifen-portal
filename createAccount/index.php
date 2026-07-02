<?php
	$PAGE_NAME = "Create Account";
	include '../includes/header.php';
?>
<form action="submit.php" method="post">
	<p class='form-label'>Email:</p><p><input class='text-input' type='text' name='email'></p>
	<?php if(isset($_SESSION['errors']['email'])) {echo '<p class="error-text">' . $_SESSION['errors']['email'] . '</p>';} ?>
	<p class='form-label'>Username:</p><p><input class='text-input' type='text' name='username'></p>
	<?php if(isset($_SESSION['errors']['username'])) {echo '<p class="error-text">' . $_SESSION['errors']['username'] . '</p>';} ?>
	<p class='form-label'>Password:</p><p><input class='text-input' type='password' name='password'></p>
	<?php if(isset($_SESSION['errors']['password'])) {echo '<p class="error-text">' . $_SESSION['errors']['password'] . '</p>';} ?>
	<p class='form-label'>Confirm Password:</p><p><input class='text-input' type='password' name='confirmPassword'></p>
	<?php if(isset($_SESSION['errors']['confirmPassword'])) {echo '<p class="error-text">' . $_SESSION['errors']['confirmPassword'] . '</p>';} ?>
	<input class='button-main' type='submit' value='Register!'>
	<p class="form-footer"> Already have an account? Log in <u><a href='../login'>here!</a></u></p>
</form>

<?php 
//Clear login errors so they dont reappear.
$_SESSION['errors'] = [];
include '../includes/footer.php';
?>
