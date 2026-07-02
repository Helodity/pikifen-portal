<?php
	$PAGE_NAME = "Login";
	include '../includes/header.php'; 
?>
<form action="submit.php" method="post">

<p class='form-label'>Username:</p><p><input class='text-input' type='text' name='username'></p>
<p class='form-label'>Password:</p><p><input class='text-input' type='password' name='password'></p>
<input class='button-main' type='submit' value='Login!'>
<?php if(isset($_SESSION['errors']['login'])) {echo '<p class="error-text">' . $_SESSION['errors']['login'] . '</p>';} ?>
<p class="form-footer"> New? Create an account <u><a href='../createAccount'>here!</a></u></p>
</form>

<?php 
	$_SESSION['errors'] = [];
	include '../includes/footer.php'; 
?>
