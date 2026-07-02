<?php
//Defines $servername, $dbusername, $dbpassword, and $dbname
include 'database_credentials.php';

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

enum PERMISSIONS: int {
	//Can upload new packs. Set false for banned users.
	case UPLOAD_PACKS = 1;
	//Can modify other user's packs. Admin stuff.
	case MODIFY_OTHERS = 2;
	//Can modify other users' permissions. Admin stuff.
	case MODIFY_USERS = 4;
}

function get_favorite_count(int $packID) : int {
	global $conn;

	$stmt = $conn->prepare("SELECT Count(id) AS count FROM favorites WHERE pack_id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
	$result = $stmt->get_result();

	$count = ($result->fetch_assoc())['count'];

	return isset($count) ? $count : 0;
}

function has_favorite(int $accountID, int  $packID) : bool {
	global $conn;
	$stmt = $conn->prepare("SELECT * FROM favorites WHERE pack_id = ? AND account_id = ?");
	$stmt->bind_param("ii", $packID, $accountID);
	$stmt->execute();
	$result = $stmt->get_result();

	return mysqli_num_rows($result) > 0;
}

function account_exists(int $accountID) : bool {
	global $conn;

	$stmt = $conn->prepare("SELECT permissions FROM accounts WHERE id = ?");
	$stmt->bind_param("i", $accountID);
	$stmt->execute();
	$result = $stmt->get_result();

	return mysqli_num_rows($result) > 0;
}


function get_username(int $id) : string {
	global $conn;
	$stmt = $conn->prepare("SELECT username FROM accounts WHERE id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$result = mysqli_fetch_assoc($stmt->get_result());

	return $result['username'];
}

function get_pack_info(int $packID) {
	global $conn;
	$stmt = $conn->prepare("SELECT * FROM packs WHERE id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
	return mysqli_fetch_assoc($stmt->get_result());
}


function account_has_permission(int $accountID, PERMISSIONS $permission) : bool {
	global $conn;

	$stmt = $conn->prepare("SELECT permissions FROM accounts WHERE id = ?");
	$stmt->bind_param("i", $accountID);
	$stmt->execute();
	$result = $stmt->get_result();

	//Return false if the account doesn't exist.
	if(mysqli_num_rows($result) == 0) {
		return false;
	}

	$accountPerms = ($result->fetch_assoc())['permissions'];
	
	//Bitwise AND to check for permission.
	return $accountPerms & $permission->value;
}

function account_owns_pack(int $accountID, int $packID) : bool {
	global $conn;

	//Get the selected pack
	$stmt = $conn->prepare("SELECT * FROM packs WHERE id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
	$result = $stmt->get_result();

	if(mysqli_num_rows($result) == 0) {
		//Pack does not exist. We don't own it.
		return false;
	}
	$pack = mysqli_fetch_assoc($result);
	
	//Check if the owner is the given account.
	$packOwner = $pack['account_id'];
	return $accountID === $packOwner;
}

function replace_pack_tags(int $packID, string $newPackTags) {
	global $conn;
	//Delete the old tags
	$stmt = $conn->prepare("DELETE FROM pack_tags WHERE pack_id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();

	//Add new tags
	$stmt = $conn->prepare("INSERT INTO pack_tags (pack_id, tag) VALUES (?, ?)");
	$tagList = explode(';', $newPackTags);
	for($i = 0; $i < count($tagList); $i++) {
		$tagList[$i] = trim($tagList[$i]);

		$stmt->bind_param("is", $packID, $tagList[$i]);
		$stmt->execute();
	}
}

function get_pack_tag_string(int $packID) {
	global $conn;
	$stmt = $conn->prepare("SELECT tag FROM pack_tags WHERE pack_id = ?");
	$stmt->bind_param("i", $packID);
	$stmt->execute();
	$result = $stmt->get_result();

	$output = $result->fetch_assoc()['tag'];
	while($row = $result->fetch_assoc()){
		$output = $output . "; " . $row['tag'];
	}
	return $output;
}

function can_modify_pack(int $accountID, int $packID) : bool {
	//If you own the pack, you can delete it.
	if(account_owns_pack($accountID, $packID)) return true;
	
	//If you have permission to delete other people's packs, you can delete it.
	if(account_has_permission($accountID, PERMISSIONS::MODIFY_OTHERS)) return true;
	
	//You cannot delete the pack.
	return false;
}


?>
