<?php

session_start();

require_once("functions.php");

if (isset($_POST['like_post_id']) && !empty($_POST['like_post_id'])) {
    $managerForLikes = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
    $queryLikesCount = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId($_POST['like_post_id'])]);

    $namespace = returnNamespace();

    $rowsLikesCount = checkForQueryError($managerForLikes, $namespace, $queryLikesCount);

    foreach ($rowsLikesCount as $value) {
        $likesCount = $value->likes_count;
    }

    // casting to int to do math operations and then cast back to STRING type

    $newLikesCount = intval($likesCount);
    $newLikesCount++;
    $newLikesCount = strval($newLikesCount);


    $key_words = explode("&", $_SERVER['QUERY_STRING']);
    $key_words[8] = strval("likes_count=" . $newLikesCount);

    $newQueryString = implode("&", $key_words);

    $bulkForLikes = new MongoDB\Driver\BulkWrite;

    $bulkForLikes->update(['_id' => new MongoDB\BSON\ObjectId($_POST['like_post_id'])], ['$set' => ['likes_count' => $newLikesCount]]);

    try {
        $resultForLikes = $managerForLikes->executeBulkWrite($namespace, $bulkForLikes);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error: " . $e->getMessage());
    }

    header("location: single-blog.php?" . $newQueryString);
}