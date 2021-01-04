<?php
session_start();

require_once("../functions.php");

$bulk = new MongoDB\Driver\BulkWrite;
$manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

if (isset($_POST['comment_id'], $_POST['old_comment_count'], $_POST['mode'], $_POST['newContent']) && !empty($_POST['comment_id']) && !empty($_POST['old_comment_count']) && !empty($_POST['newContent']) && $_POST['mode'] == "update") {

    $bulk->update(['_id' => new MongoDB\BSON\ObjectId($_POST['comment_id'])], ['$set' => ['comment_content' => $_POST['newContent']]]);
    $result = $manager->executeBulkWrite("usersDB.comments", $bulk);

    header("location: ../single-blog.php?" . $_SERVER['QUERY_STRING']);
} else if (isset($_POST['comment_id'], $_POST['old_comment_count'], $_POST['mode']) && !empty($_POST['comment_id']) && !empty($_POST['old_comment_count']) && $_POST['mode'] == "delete") {

    $query = new MongoDB\Driver\Query(["_id" => new MongoDB\BSON\ObjectId($_POST['comment_id'])]);
    $rows = checkForQueryError($manager,"usersDB.comments", $query);

    foreach ($rows as $row) {
        $post_id = $row->post_id;
    }

    $newValue = intval($_POST['old_comment_count']) - 1;
    $newValue = strval($newValue);

    $bulkUpdateComments = new MongoDB\Driver\BulkWrite();

    $bulkUpdateComments->update(["_id" => new MongoDB\BSON\ObjectId($post_id)], ['$set' => ['post_comment_count' => $newValue]]);

    $namespace = returnNamespace(); // find by comment by id and then find post that relates to it in order to update comment count

    try {
        $resultUpdateComments = $manager->executeBulkWrite($namespace, $bulkUpdateComments);
        $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($_POST['comment_id'])]);
        $result = $manager->executeBulkWrite("usersDB.comments", $bulk);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error: " . $e->getMessage());
    }

    $_SERVER['QUERY_STRING'] = str_replace("post_comment_count={$_POST['old_comment_count']}", "post_comment_count={$newValue}", $_SERVER['QUERY_STRING']);

    header("location: ../single-blog.php?" . $_SERVER['QUERY_STRING']);
} else {
    header("location:" . $_SERVER["HTTP_REFERER"]);
}