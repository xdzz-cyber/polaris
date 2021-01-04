<?php

require_once("oauthLogin.php");


if (isset($_COOKIE['_id'])) {
    setcookie("_id", "", time() - 7200, "/");
}

if (isset($_COOKIE['name'])) {
    setcookie("name", "", time() - 7200, "/");
}

if (isset($_COOKIE['email'])) {
    setcookie("email", "", time() - 7200, "/");
}

if (isset($_COOKIE['photo'])) {
    setcookie("photo", "", time() - 7200, "/");
}

$_SESSION = array();
session_destroy();

header("location: ../index.php");