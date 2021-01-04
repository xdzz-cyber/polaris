<?php
session_start();

require_once("count_num_pages.php");
require_once("functions.php");

$managerForAll = new MongoDB\Driver\Manager(getEnvVars(true)[0]);


if (isset($_GET['category'])) {
    $queryForAll = new MongoDB\Driver\Query(['category' => $_GET['category']]);
} else {
    $queryForAll = new MongoDB\Driver\Query([]);
}


$countForAll = 0;

$namespace = returnNamespace();

$rowsForAll = checkForQueryError($managerForAll, $namespace, $queryForAll);


foreach ($rowsForAll as $value) {
    $countForAll++;
}

$limitForAll = 2;

if ($countForAll < 2) {
    $limitForAll = 1;
}

if ($countForAll == 0) {
    $limitForAll = 0;
}


$skipForAll = $countForAll - $limitForAll;


$queryLastPosts = new MongoDB\Driver\Query([], ["limit" => $limitForAll, "skip" => $skipForAll]);

if (isset($_SESSION['isNews']) || isset($_GET['news'])) {

    $rowsLastPosts = checkForQueryError($managerForAll, "usersDB.news_posts", $queryLastPosts);

} else {

    $rowsLastPosts = checkForQueryError($managerForAll, "usersDB.user_posts", $queryLastPosts); // Query to fetch usernames to recent post and then show them along with post data that we fetch above

    $queryLastUsernames = new MongoDB\Driver\Query([], ["limit" => $limitForAll, "skip" => $countAllUsers - $limitForAll]);

    $rowsLastUsernames = checkForQueryError($managerForAll, "usersDB.users", $queryLastUsernames);

    $usernames = [];

    foreach ($rowsLastUsernames as $value2) {
        $usernames[] = array("username" => $value2->name);
    }

}


$recentPosts = [];

$i = 0;
if (isset($_SESSION['isNews']) || isset($_GET['news'])) {
    foreach ($rowsLastPosts as $value) {
        $recentPosts[] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "postDate" => $value->postDate, "post_photo" => $value->post_photo, "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count);
    }
} else {
    foreach ($rowsLastPosts as $value) {
        $recentPosts[] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "postDate" => $value->postDate, "user_photo" => $value->user_photo, "user_id" => $value->user_id, "username" => $usernames[$i++]["username"], "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count);
    }
}

if (isset($_SESSION['search_input']) && !empty($_SESSION['search_input'])) {
    $queryForSearch = new MongoDB\Driver\Query(["post_tags" => new MongoDB\BSON\Regex("{$_SESSION['search_input']}")]);


    $namespace2 = returnNamespace();

    $rowsForSearch = checkForQueryError($managerForAll, $namespace2, $queryForSearch);

    $countForSearch = 0;

    foreach ($rowsForSearch as $value) {
        $countForSearch++;
    }
}


$_SESSION['countForSearch'] = $countForSearch;
$_SESSION['countForAll'] = $countForAll;
$_SESSION['recentPosts'] = $recentPosts;
$_SESSION['limitForAll'] = $limitForAll;