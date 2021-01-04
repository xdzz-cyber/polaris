<?php
session_start();

$manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

if (isset($_GET['category'])) {
    $params = ["category" => $_GET['category']];
} else {
    $params = [];
}

if (isset($_SESSION['isNews'])) {
    $query = new MongoDB\Driver\Query($params);
    $rows = checkForQueryError($manager, "usersDB.news_posts", $query);
} else {
    if (!empty($params)) {
        $query = new MongoDB\Driver\Query(["user_id" => $_SESSION['_id'], "category" => $_GET['category']]);
    } else {
        $query = new MongoDB\Driver\Query(["user_id" => $_SESSION['_id']]);
    }

    $rows = checkForQueryError($manager, "usersDB.user_posts", $query);
}


$countAllPages = 0;

foreach ($rows as $v) {
    $countAllPages++;
}

// for usernames

$query2 = new MongoDB\Driver\Query([]);

$rows2 = checkForQueryError($manager, "usersDB.users", $query2);

$countAllUsers = 0;

foreach ($rows2 as $v2) {
    $countAllUsers++;
}