<?php
	$PAGE_NAME = "Search Results";
	include 'includes/header.php';
	
	$searchQuery = strtolower($_GET['query']) ?? '';

	$stmt = $conn->prepare(
		"SELECT packs.id, accounts.username, pack_tags.tag, COUNT(favorites.id) AS favorite_amount FROM packs
		LEFT JOIN favorites
			ON packs.id = favorites.pack_id
		JOIN accounts
			ON packs.account_id = accounts.id
		LEFT JOIN pack_tags
			ON packs.id = pack_tags.pack_id
		WHERE
			(accounts.username LIKE ? OR
			packs.name LIKE ? OR
			pack_tags.tag LIKE ?)
			AND packs.public = TRUE
		GROUP BY packs.id ORDER BY favorite_amount DESC");

	$searchQuery = "%" . $searchQuery . "%";

	$stmt->bind_param(
		"sss",
		$searchQuery,
		$searchQuery,
		$searchQuery
	);
	if(isset($_GET['random'])) {
		//If we clicked random, get a random pack!
		$stmt = $conn->prepare("SELECT * FROM packs ORDER BY RAND() LIMIT 1;");
	}
	$stmt->execute();
	$result = $stmt->get_result();
	//One result, load it directly.
	if(mysqli_num_rows($result) == 1) {
		$pack = mysqli_fetch_assoc($result);
		header("Location: pack/?id=" . $pack['id']);
		die();
	}
?>
<div class='list-root'>
<?php
	$entryID = 0;
	while ($row = $result->fetch_assoc()) {
		make_list_item($row['id'], $entryID);
		$entryID += 1;
	}
?>
</div>
<?php include "includes/footer.php" ?>
