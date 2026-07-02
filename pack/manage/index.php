<?php
	include '../../includes/functions.php';
	include_once '../../includes/database.php';

	//If the method wasnt set right, return to the main page
	if($_SERVER["REQUEST_METHOD"] != "GET") {
		$conn->close();
		header("Location: ../../");
		die();
	}
	$packID = $_GET['id'];

	//Get the selected pack
	$stmt = $conn->prepare("SELECT * FROM packs WHERE id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
	$result = $stmt->get_result();

	//Pack does not exist. Return to home page
	if(mysqli_num_rows($result) == 0) {
		$conn->close();
		header("Location: ../../");
		die();
	}

	$pack = mysqli_fetch_assoc($result);
	
	
	$packName = $pack['name'];
	$makerID = $pack['account_id'];
	$isPublic = $pack['public'];
	$packDescription = $pack['description'];
	$packTags = get_pack_tag_string($packID);
	$makerName = get_username($makerID);


	$PAGE_NAME = "Editing " . $packName;
	include '../../includes/header.php';
	
	$isOwner = 				isset($_SESSION['accountID']) ? account_owns_pack($_SESSION['accountID'], $packID): false;
	$hasModifyPermissions = isset($_SESSION['accountID']) ? can_modify_pack($_SESSION['accountID'], $packID): false;

	//Cannot modify this pack. go back to the pack's main page
	if(!$hasModifyPermissions) {
		$conn->close();
		header("Location: ../?id=" . $packID);
		die();
	}
?>
<header>
<h1><?= $packName ?></h1>
<?php if(!$isOwner) { ?>
<h3 class="error-text" style='text-align: center'>You do not own this pack, be careful!</h3>
<?php } ?>
</header>

<div class='pack-root'>
	<div class='pack-left'>
		<div class="gallery-image-parent">
			<img class="pack-icon" src="<?= get_thumbnail($packID); ?>">
		</div>
		<?php if($isOwner) { ?>
			<form action="replaceThumbnail.php" method="post" enctype="multipart/form-data">
				<label for="thumbnail" class="button-main">Change Thumbnail</label>
				<input type="file" name="thumbnail" id="thumbnail" onchange="this.form.submit()">
				<?php if(isset($_SESSION['errors']['thumbnailUpload'])) { ?>
					<p class="error-text"><?=$_SESSION['errors']['thumbnailUpload']?></p>
				<?php } ?>
				<input type="hidden" name="packID" value="<?= $packID ?>">
			</form>
		<?php } ?>
		<button class="button-main" onclick="location.href='../?id=<?= $packID ?>'">Go Back</button>
	</div>
	
	<div class='pack-right'>
		<form action="modify.php" method="post">
			<p class="form-label">Name: </p>
			<input class='text-input' type='text' name='name' value='<?= $packName ?>'>
			<br><br>
			<p class="form-label">Description: </p>
			<textarea class='text-input' style='width:90%; height: 64px;' type='text' name='description' ><?= $packDescription ?></textarea>
			<br><br>
			<p class="form-label">Tags: </p>
			<textarea class='text-input' style='width:90%; height: 64px;' type='text' name='tags' placeholder='Separate with semicolons (Leaders; Areas; Hazard)'><?= $packTags ?></textarea>
			<br><br>
			<p>Make pack public
			<input type="checkbox" style="margin: 0; width:24px; height:24px;" name="public" value="1" <?php if ($isPublic) {echo "checked";} ?>></p>
			<br>
			<input class='button-main' type='submit' value='Save Changes'>
			<input type="hidden" name="existingID" value="<?=$packID?>">
		</form>


		<h2>Manage Versions</h2>
		<table style="width:100%; text-align:center;">
			<?php
				$stmt = $conn->prepare("SELECT * FROM pack_versions WHERE pack_id = ? ORDER BY pack_version DESC");
				$stmt->bind_param("i", $packID);
				$stmt->execute();
				$version_query = $stmt->get_result();
			?>
			<tr>
			<th>Pack Version</th>
			<th>Pikifen Version<th>
			<th>
			<?php if($isOwner) { ?>
			<form action="uploadNewVersion.php" method="post" enctype="multipart/form-data">
			<label for="pack" class="button-main">Upload new version</label>
			<input type="file" name="pack" id="pack" onchange="this.form.submit()">
			<?php if(isset($_SESSION['errors']['packUpload'])) { ?>
				<p class="error-text"><?=$_SESSION['errors']['packUpload']?></p>
				<?php } ?>
				<input type="hidden" name="packID" value="<?= $packID ?>">
				</form>
				<?php } ?>
				</th>
				</tr>
				<?php while ($row = $version_query->fetch_assoc()) {
					$engineVersion = $row['engine_version'];
					$packVersion = $row['pack_version'];
					$versionID = $row['id'];
					$downloadPath = $SITE_ROOT . '/packs/' . $packID . '/' . getPackFilename($packID, $packVersion, $engineVersion);
					?>
					<tr>
					<td>V <?= int_to_version($packVersion) ?></td>
					<td>V <?= int_to_version($engineVersion) ?><td>
					<td>
						<form action="deleteVersion.php" method="post">
							<button class="button-warning" type='submit'>Delete</button>
							<input type="hidden" name="versionID" value="<?=$versionID?>">
						</form>
						</td>
					</tr>
			<?php } ?>
		</table>


		<h1 style="color: var(--error_color);">Danger Zone</h1>
		<button class="button-warning" onclick="location.href='delete.php?id=<?= $packID ?>'">Delete Pack</button>
		<p class='error-text' style='display: inline'>This will delete ALL versions of the pack and remove it from <?php if(!$isOwner) { echo "their"; } else { echo "your";} ?> account!</p>


			<br>
	</div>
</div>

<?php 
$_SESSION['errors'] = [];
include '../../includes/footer.php';
?>
