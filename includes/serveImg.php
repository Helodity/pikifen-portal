<?php
include_once 'globals.php';

$imgURL = $_GET['img'];
$imgSize = $_GET['size'] ?? 125;

$ext = 'png';
$outext = $_GET['ext'] ?? 'webp';
$outQuality = $_GET['quality'] ?? 50;

if(!is_numeric($imgSize)){
	die('Invalid image size');
}

switch ($outext) {
	case 'webp':
		//Doesn't exist on local build (linux moment)
		if(!function_exists('imagewebp')){
			$outext = 'png';
		}
	case 'avif':
		//Doesn't exist on local build (linux moment)
		if(!function_exists('imageavif')){
			$outext = 'png';
		}
	case 'png':
	case 'jpg':
	case 'jpeg':
		//Empty, but prevents dying
		break;

	default:
		die('Invalid Extension');
		break;
}


$srcImgLocalUrl = $LOCAL_ROOT . $imgURL . '.' . $ext;

$cacheDir = $LOCAL_ROOT . '/cache' . dirname($imgURL);
mkdir($cacheDir, 0777, true);

$srcCacheLocalURL = $LOCAL_ROOT . '/cache' . $imgURL . '_thumb_' . $imgSize . '_' . $outQuality . '.' . $outext;

clearstatcache();
// Check if file exists
if (!file_exists($srcImgLocalUrl))
{
	die('Unable to process the requested file.');
}

if(!file_exists($srcCacheLocalURL)) {
	//Thumbnail doesn't exist, make it.
	list($width, $height) = getimagesize($srcImgLocalUrl);

	$percent = $imgSize / max($width, $height);

	$newWidth = (int)($width * $percent);
	$newHeight = (int)($height * $percent);


	//Thumbnail exists, serve it.
	$srcImg = imagecreatefrompng($srcImgLocalUrl);
	$img = imagecreatetruecolor($newWidth, $newHeight);

	imagefill($img,0,0,0x7fff0000);

	// Resize
	imagecopyresampled($img, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

	//Save image to cache
	imagesavealpha($img, true);
	switch ($outext) {
		case 'webp':
			imagewebp($img, $srcCacheLocalURL, $outQuality);
			break;
		case 'avif':
			imageavif($img, $srcCacheLocalURL, $outQuality);
			break;
		case 'png':
			imagepng($img, $srcCacheLocalURL);
			break;
		case 'jpg':
		case 'jpeg':
			imagejpeg($img, $srcCacheLocalURL);
			break;
	}
} else {
	//Serve Cached Thumbnail
	switch ($outext) {
		case 'webp':
			$img = imagecreatefromwebp($srcCacheLocalURL);
			break;
		case 'avif':
			$img = imagecreatefromavif($srcCacheLocalURL);
			break;
		case 'png':
			$img = imagecreatefrompng($srcCacheLocalURL);
			break;
		case 'jpg':
		case 'jpeg':
			$img = imagecreatefromjpeg($srcCacheLocalURL);
			break;
	}
}



header('Content-Type: image/'.$outext);

imagesavealpha($img, true);
switch ($outext) {
	case 'webp':
		imagewebp($img, null, $outQuality);
		break;
	case 'avif':
		imageavif($img, null, $outQuality);
		break;
	case 'png':
		imagepng($img);
		break;
	case 'jpg':
	case 'jpeg':
		imagejpeg($img);
		break;
}

?>

