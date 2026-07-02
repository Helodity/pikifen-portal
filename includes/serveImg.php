<?php
include_once 'globals.php';

$imgURL = $_GET['img'];
$imgSize = $_GET['size'] ?? 125;

$ext = 'png';

$srcImgLocalUrl = $LOCAL_ROOT . $imgURL . '.' . $ext;
$srcThumbLocalURL = $LOCAL_ROOT . $imgURL.'_thumb_' . $imgSize . '.' . $ext;

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

	$percent = $imgSize / $width;

	$newWidth = $width * $percent;
	$newHeight = $height * $percent;


	//Thumbnail exists, serve it.
	$srcImg = imagecreatefrompng($srcImgLocalUrl);
	$img = imagecreatetruecolor($newWidth, $newHeight);

	imagefill($img,0,0,0x7fff0000);

	// Resize
	imagecopyresampled($img, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);



}


header('Content-Type: image/'.$ext);

imagesavealpha($img, true);
imagepng($img);

?>

