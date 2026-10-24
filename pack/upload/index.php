<?php
	$PAGE_NAME = "Upload";
	include '../../includes/header.php';

	if(!isset($_SESSION['accountID'])) {
		header("Location:". $SITE_ROOT ."/login");
		die();
	}
?>



<form action="submit.php" method="post" enctype="multipart/form-data">
	<label for="pack" >
	<div class='upload-header'>
		<h1>Upload a pack file</h1>
		<img src="../../img/upload.svg" style='height: 150px'>
		<p class="form-footer">The site will automatically grab its details.</p>
		<?php if(isset($_SESSION['errors']['packUpload'])) {echo '<p class="error-text">' . $_SESSION['errors']['packUpload'] . '</p>';} ?>
	</div>
	</label>
	<input type="file" name="pack" id="pack" onchange="this.form.submit()">
</form>

<?php 
	//Clear login errors so they dont reappear.
	$_SESSION['errors'] = [];
	include '../../includes/footer.php';
?>
