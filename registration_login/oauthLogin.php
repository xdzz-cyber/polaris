<?php

//require_once realpath(__DIR__ . "/vendor/autoload.php");
require_once ("../vendor/autoload.php");


$google_client = new Google_Client();

$google_client->setClientId("856905564347-o8prpsdshqod4u0pp3icce1bcue0tu10.apps.googleusercontent.com");
$google_client->setClientSecret("YbeMoy5uYkM6D9-OcEbaWZf-");
$google_client->setRedirectUri("http://localhost/registration_login/login.php");

$google_client->addScope("email");
$google_client->addScope("profile");


session_start();

