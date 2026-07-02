<?php
include_once 'globals.php';

function move_up_directory(string $basepath, int $depth) {
	$text = $basepath;
	
	for($i = 0; $i < $depth; $i++) {
		$text = "../" . $text;
	}
	$text = "'" . $text . "'";
	return $text;
	
}

function version_to_int(string $version): int {
	$versionDivided = explode('.', $version);
				
	//Convert from M.m.r to MMmmrr
	return 10000 * $versionDivided[0] + 100 * $versionDivided[1] + $versionDivided[2];
}

function int_to_version(int $version): string {
	$majorVersion = floor($version / 10000);
	
	$minorVersion = floor(($version - ($majorVersion * 10000)) / 100);
	$revisionVersion = $version - $majorVersion * 10000 - $minorVersion * 100;
	return $majorVersion . "." . $minorVersion . "." . $revisionVersion;
}

function get_thumbnail(int $packID, int $size = 100): string {
	global $SITE_ROOT;
	global $LOCAL_ROOT;
	//file_exists is fucking dumb and HATES working with the site_root system
	//Sooooooo yeah we gotta use __DIR__ and then if THAT exists switch to
	//$SITE_ROOT. Its dumb and i hate it.
	$localthumbnailPath = $LOCAL_ROOT . '/packs/' . $packID . '/thumbnail.png';
	clearstatcache();
	if(file_exists($localthumbnailPath)) {
		return $SITE_ROOT . '/includes/serveImg.php?img=/packs/' . $packID . '/thumbnail&size=' . $size;
	}
	return $SITE_ROOT . '/img/default_thumbnail.png';
}

function make_gallery(string $title, $queryResult) {
	echo "<div class='gallery-root'>";
		echo "<h3 class='gallery-header'>" . $title . "</h3>";

		echo "<div class='gallery-contents'>";
			$entryCount = 0;
			while ($row = $queryResult->fetch_assoc()) {
				make_gallery_item($row['id'], $entryCount);
				$entryCount += 1;
			}
		echo "</div>";
	echo "</div>";
}


function make_gallery_item(int $packID, int $entryID) {
	global $SITE_ROOT;
	$thumbnailImg = get_thumbnail($packID);
	$packInfo = get_pack_info($packID);
	$delay = 0.3 + ($entryID % 2) * 0.2;
	echo '<a href="' . $SITE_ROOT . '/pack?id='. $packID .'" class="gallery-item-root" style="animation: gallery-item-dropdown 0.5s cubic-bezier(0.65, 1.33, 0.7, 1.13) ' . $delay . 's backwards">';
	echo '<div class="gallery-image-parent"><img class="pack-icon" loading="lazy" src="'. $thumbnailImg. '"></div>';
	echo '<p class="gallery-entry-name">' . $packInfo['name'] .'</p>';
	echo '</a>';
}

function make_list_item(int $packID, int $entryID) {
    global $SITE_ROOT;

	include_once "database.php";

	$packInfo = get_pack_info($packID);
	$delay = 0.1 + ($entryID % 2) * 0.2;

	$thumbnailImg = get_thumbnail($packID);
    echo '<a href="' . $SITE_ROOT . '/pack?id='. $packID .'" class="list-item-root" style="animation: list-item-dropdown 0.5s cubic-bezier(0.65, 1.33, 0.7, 1.13) ' . $delay . 's backwards">';
		echo '<div class="list-image-parent"><img class="pack-icon" loading="lazy" src="'. $thumbnailImg. '"></div>';
		echo '<div class="list-contents">';
			echo '<div style="width:100%; display:flex"><h3 class="list-entry-name">' . $packInfo['name'] . '</h3><p class="list-entry-maker"> By ' . get_username($packInfo['account_id']) . '</p></div>';
			echo '<p class="list-entry-description">' . $packInfo['description'] . '</p>';
		echo '</div>';
	echo '</a>';
}

enum RECENCY {
	case NEWER_VERSION;
	case CURRENT_VERSION;
	case OLDER_MINOR;
	case OLDER_MAJOR;	
}

function getOutdatedType($engineVersionInt) {
	global $NEWEST_ENGINE_VERSION_STR;
	$currentVersionInt = version_to_int($NEWEST_ENGINE_VERSION_STR);
	
	if($engineVersionInt == $currentVersionInt) {
		return RECENCY::CURRENT_VERSION;
	}
	if($engineVersionInt > $currentVersionInt) {
		return RECENCY::NEWER_VERSION;
	}
	if($currentVersionInt - $engineVersionInt < 100) {
		return RECENCY::OLDER_MINOR;
	} 
	return RECENCY::OLDER_MAJOR;
}

function getPackFilename($packID, $packVersion, $engineVersion) {
	return "pack_" . $packID . "_" . int_to_version($packVersion) . "+" . int_to_version($engineVersion) . ".zip";
}


?>
