<?php
include '../includes/functions.php';
include_once '../includes/database.php';


$versionID = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM pack_versions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $versionID);
$stmt->execute();
$latest_query = $stmt->get_result();

if($row = $latest_query->fetch_assoc()) {
    $engineVersion = $row['engine_version'];
    $packVersion = $row['pack_version'];
    $packID = $row['pack_id'];
    $downloadPath = $LOCAL_ROOT . '/packs/' . $packID . '/' . getPackFilename($packID, $packVersion, $engineVersion);

    $stmt = $conn->prepare("SELECT * FROM packs WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $packID);
    $stmt->execute();
    $latest_query = $stmt->get_result();
    if($row = $latest_query->fetch_assoc()) {
        $packName = htmlspecialchars_decode($row['name']);
        $fileName = $packName . ' ' . int_to_version($packVersion) . " for Pikifen " . int_to_version($engineVersion);

        header('Content-Disposition: attachment; filename="' . $fileName . '.zip"');
        readfile($downloadPath);
    }
}



?>