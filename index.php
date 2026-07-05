<?php
	$PAGE_NAME = "Home";
	include 'includes/header.php';
?>
<header>
	<h1>Welcome to Pikifen Portal!</h1>
</header>
<?php
	$query = "SELECT * FROM packs WHERE public = TRUE ORDER BY id DESC LIMIT 12";
	$result = $conn->query($query);
	make_gallery("Newest Packs", $result);
?>

<?php	
	$query = "SELECT packs.id, packs.public, COUNT(favorites.id) AS favorite_amount
	FROM packs
	JOIN favorites ON packs.id = favorites.pack_id AND packs.public = TRUE
	GROUP BY packs.id ORDER BY favorite_amount DESC LIMIT 12";

	$result = $conn->query($query);
	make_gallery("Most Popular", $result);
?>

<?php 
	if(isset($_SESSION['accountID'])) {
		$accountID = $_SESSION['accountID'];
		$stmt = $conn->prepare("SELECT pack_id AS id FROM favorites WHERE account_id = ?");
		$stmt->bind_param("i", $accountID);
		$stmt->execute();
		$result = $stmt->get_result();
		
		make_gallery("Your Favorites", $result);
	} 
?>

<?php include __DIR__ . '/includes/footer.php';  ?>
