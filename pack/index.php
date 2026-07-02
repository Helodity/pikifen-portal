<?php
include '../includes/functions.php';
include_once '../includes/database.php';

//If the method wasnt set right, return to the main page
if($_SERVER["REQUEST_METHOD"] != "GET") {
	$conn->close();
	header("Location: ../");
	die();
}
$packID = $_GET['id'];

//Get the selected
$stmt = $conn->prepare("SELECT * FROM packs WHERE id = ?");
$stmt->bind_param("i", $packID);
$stmt->execute();
$result = $stmt->get_result();

//Pack does not exist. Return to home page
if(mysqli_num_rows($result) == 0) {
	$conn->close();
	header("Location: ../");
	die();
}

$pack = mysqli_fetch_assoc($result);


$packName = $pack['name'];
$makerID = $pack['account_id'];
$isPublic = $pack['public'];
$packDescription = $pack['description'];

$makerName = get_username($makerID);


$PAGE_NAME = $packName;
include '../includes/header.php';

$isOwner = 				isset($_SESSION['accountID']) ? account_owns_pack($_SESSION['accountID'], $packID): false;
$hasDeletePermissions = isset($_SESSION['accountID']) ? can_modify_pack($_SESSION['accountID'], $packID): false;

$hasManageOptions = $isOwner || $hasDeletePermissions;

//Pack is private, cannot view.
if(!$isOwner && !$isPublic) {
	$conn->close();
	header("Location: ../");
	die();
}
?>
<header>
<h1><?= $packName ?></h1>
</header>


<div class='pack-root'>
<div class='pack-left'>
<div class="gallery-image-parent">
<img class="pack-icon" src="<?= get_thumbnail($packID, 200); ?>">
</div>
<p>By <a href='../user?id=<?= $makerID ?>'><?= $makerName ?></a>
<?php
if(isset($_SESSION['accountID'])) {
	echo "<button class='button-main' id='favoriteButton'  onclick=\"onFavoriteSubmit($packID)\">";
	echo has_favorite($_SESSION['accountID'], $packID) ? "Starred ★ " : "Star ☆ ";
	echo get_favorite_count($packID);
	echo "</button>";
}
?>
</p>
<?php if ($hasManageOptions) { ?>
	<button class='button-main' onclick='location.href="manage?id=<?= $packID ?>"'>Manage Pack</button>
	<?php } ?>
	</div>

	<div class='pack-right'>
	<h2><?= $packName ?></h2>
	<p><?= $packDescription ?></p>
	<br>
	<div style='display:flex'>
	<h2>Versions</h2>
	</div>
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
	<?php
	//Latest Version Download
	$target_version = version_to_int($NEWEST_ENGINE_VERSION_STR);

	$stmt = $conn->prepare("SELECT * FROM pack_versions WHERE pack_id = ? AND engine_version = ? ORDER BY pack_version DESC LIMIT 1");
	$stmt->bind_param("ii", $packID, $target_version);
	$stmt->execute();
	$latest_query = $stmt->get_result();

	if($row = $latest_query->fetch_assoc()) {
		$engineVersion = $row['engine_version'];
		$packVersion = $row['pack_version'];
		$downloadPath = $SITE_ROOT . '/packs/' . $packID . '/' . getPackFilename($packID, $packVersion, $engineVersion);
		?>
		<button class="button-main" onclick="location.href='<?= $downloadPath ?>'">Download for current version</button>
		<?php } else { ?>
			<p></p>

			<?php } ?>
			</th>
			</tr>
			<?php while ($row = $version_query->fetch_assoc()) {
				$engineVersion = $row['engine_version'];
				$packVersion = $row['pack_version'];
				$downloadPath = $SITE_ROOT . '/packs/' . $packID . '/' . getPackFilename($packID, $packVersion, $engineVersion);
				?>
				<tr>
				<td>V <?= int_to_version($packVersion) ?></td>
				<td>V <?= int_to_version($engineVersion) ?><td>
				<td><button class="button-main" onclick="location.href='<?= $downloadPath ?>'">Download</button></td>

				</tr>
				<?php } ?>
				</table>
				</div>
				</div>

				<?php
				$_SESSION['errors'] = [];
				include '../includes/footer.php';
				?>
