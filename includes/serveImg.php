<?php
include_once 'globals.php';

$imgURL = $_GET['img'];
$imgSize = $_GET['size'] ?? 125;

$ext = 'png';
$outext = $_GET['ext'] ?? 'webp';
$outQuality = $_GET['quality'] ?? 50;

switch ($outext) {
	case 'webp':
		//Doesn't exist on local build (linux moment)
		if(!function_exists('imagewebp')){
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
$srcThumbLocalURL = $LOCAL_ROOT . $imgURL.'_thumb_' . $imgSize . '.' . $ext;

clearstatcache();
// Check if file exists
if (!file_exists($srcImgLocalUrl))
{
	die('Unable to process the requested file.');
}

if(!is_numeric($imgSize)){
	die('Invalid image size');
}


// Check if a thumb already exists, otherwise create a thumb
if (file_exists($srcThumbLocalURL))
{
	//Thumbnail exists, serve it.
	$img = imagecreatefrompng($srcThumbLocalURL);
}
else
{
	//Thumbnail doesn't exist, make it.
	list($width, $height) = getimagesize($srcImgLocalUrl);

	$percent = $imgSize / max($width, $height);

	$newWidth = $width * $percent;
	$newHeight = $height * $percent;


	//Thumbnail exists, serve it.
	$srcImg = imagecreatefrompng($srcImgLocalUrl);
	$img = imagecreatetruecolor($newWidth, $newHeight);

	imagefill($img,0,0,0x7fff0000);

	// Resize
	imagecopyresampled($img, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);



}


header('Content-Type: image/'.$outext);

imagesavealpha($img, true);

switch ($outext) {
	case 'webp':
		imagewebp($img, null, $outQuality);
	case 'png':
		imagepng($img);
		break;
	case 'jpg':
	case 'jpeg':
		imagejpeg($img);
		break;
}

?>

