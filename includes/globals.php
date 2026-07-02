<?php
//Contains definition for $PATH_FROM_ROOT
include "deployment_globals.php";

$SITE_NAME = "Pikifen Portal";
$SITE_ROOT = 'https://' . $_SERVER['HTTP_HOST'] . $PATH_FROM_ROOT; //Use when outputting HTML;
$LOCAL_ROOT = $_SERVER['DOCUMENT_ROOT'] . $PATH_FROM_ROOT; //Use when processing

$NEWEST_ENGINE_VERSION_STR = "1.1.1";
?>
