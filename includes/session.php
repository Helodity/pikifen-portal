<?php
//Automatically discard session after 1 hour
$session_duration_seconds = 3600;

//Store on the server for listed duration
ini_set('session.cookie-lifetime', $session_duration_seconds);
ini_set('session.gc_maxlifetime', $session_duration_seconds);

//Have the client remember the cookie for this long
session_set_cookie_params($session_duration_seconds);

//Start the session
session_start();

$now = time();
if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
    //Expired Session
    session_unset();
    session_destroy();
    session_start();
}

//Set the time to MURDER this session
$_SESSION['discard_after'] = $now + $session_duration_seconds;


?>