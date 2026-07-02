<?php
session_start();
include_once "database.php";
include_once "globals.php";
include_once "functions.php";
$PAGE_NAME = isset($PAGE_NAME) ? $PAGE_NAME : "ERROR: PAGE NAME NOT SET";

$header_info = [
		["name" => "Login <img src='$SITE_ROOT/img/user.svg' class='button-header-image'>", "link" => "login"],
	];
if(isset($_SESSION['accountID'])) {
	$id = $_SESSION['accountID'];
	$header_info = [
		["name" => get_username($id) . " <img src='$SITE_ROOT/img/user.svg' class='button-header-image'>", "link" => "user?id=" . $id],
		["name" => "Upload <img src='$SITE_ROOT/img/upload.svg' class='button-header-image'>", "link" => "pack/upload"],
		["name" => "Logout <img src='$SITE_ROOT/img/logout.svg' class='button-header-image'>", "link" => "logout.php"],
	];
}

$search_placeholder = isset($_GET['query']) ? $_GET['query'] : "";

?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta name="viewport" charset="utf-8" content="width=device-width, initial-scale=1">
		<title><?= $PAGE_NAME?> - <?= $SITE_NAME ?></title>
		<link href='<?= $SITE_ROOT ?>/includes/main_style.css' type="text/css" rel="stylesheet" />
		<link href="style.css" rel="stylesheet" />
		<link rel="icon" type="image/x-icon" href='<?= $SITE_ROOT ?>/img/icon.png'>
	</head>
	<body>
		<div class="header">
			<div class="header-title">
				<a class='header-logo-anchor' href="<?= $SITE_ROOT ?>"><img src='<?= $SITE_ROOT ?>/img/icon.png' class='header-logo'></a>
				<div class="header-text">
					<a class="header-text-site-name" href="<?= $SITE_ROOT ?>"><?= $SITE_NAME ?></a>
					<p class="header-text-page-name" style="font-size: var(--font_size_small);"><?= $PAGE_NAME ?></p>
				</div>
			</div>
			<div class="header-search">
				<form action="<?= $SITE_ROOT ?>/search.php" class="header-form">
					<input class="header-search-input" type="text" size=1 placeholder="Search packs..." value="<?= $search_placeholder ?>" name="query"></input>
					<button type="submit" style='display: none;'>Go!</button>
					<button type="submit" name="random" value="true" class="button-header no-text-on-small-screen header-form-item">I'm feeling lucky <img src='<?= $SITE_ROOT?>/img/random.svg' class='button-header-image'></button>
				</form>
			</div>
			<div class="header-button-group">
				<?php //Needs to be a single line to avoid whitespace getting added ?>
				<?php foreach($header_info as $btn_info) { ?><button class="button-header no-text-on-small-screen" onclick="location.href ='<?= $SITE_ROOT . '/' . $btn_info["link"]?>'"><?=$btn_info["name"]?></button><?php } ?>
			</div>
		</div>
		<div class="bg"></div>
		<main>
			<div class="content">
