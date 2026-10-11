<?php
	$PAGE_NAME = 'Reports';
	include 'includes/header.php';

	//Require the user to be logged in
	if(!isset($_SESSION['accountID'])){
		header("Location:" . $SITE_ROOT);
		die();
	}
	$accountID = $_SESSION['accountID'];
	
	//Ensure the user has permissions
	if(!account_has_permission($accountID, PERMISSIONS::MODIFY_OTHERS)) {
		header("Location:" . $SITE_ROOT);
		die();
	}

    $stmt = $conn->prepare("SELECT * FROM reports");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h1>THIS PAGE IS A WIP</h1>";

	?>
    <table>
		<?php while ($row = $result->fetch_assoc()) {?>
			<tr>
				<td><?=$row["id"]?></td>
				<td><?=$row["pack_id"]?></td>
				<td><?=$row["reason"]?></td>
			</tr>

		<?php } ?>
	</table>
	<?php

	include 'includes/footer.php';

?>