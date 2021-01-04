<?php

session_start();

require_once("../functions.php");

if (isset($_POST['comment'], $_POST['user_id'], $_POST['post_id'], $_POST['send']) && !empty($_POST['comment']) && !empty($_POST['post_id']) && !empty($_POST['user_id'])) {


    $managerForComments = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    // Making query to update post_comment_count and then adding comment to commentsTable

    $queryForComments = new MongoDB\Driver\Query(["_id" => new MongoDB\BSON\ObjectId($_POST['post_id'])]);

    $namespace = returnNamespace();

    $rowsForComments = checkForQueryError($managerForComments, $namespace, $queryForComments);


    foreach ($rowsForComments as $value) {
        $commentCount = $value->post_comment_count;
    }


    $commentCount = intval($commentCount) + 1; // cast to int because function returns string and thus we cant do increment on string type correctly
    $commentCount = strval($commentCount); // and after that cast back because we insert data type that it was initially which is String type


    $bulkForComments = new MongoDB\Driver\BulkWrite();
    $comment = [
        "comment_content" => $_POST['comment'],
        "user_id" => $_POST['user_id'],
        "post_id" => $_POST['post_id'],
        "comment_date" => date("F j, Y, g:i a")
    ];

    try {
        $bulkForComments->insert($comment);
        $result = $managerForComments->executeBulkWrite("usersDB.comments", $bulkForComments);


        $key_words = explode("&", $_SERVER['QUERY_STRING']);
        $key_words[7] = strval("post_comment_count=" . $commentCount);
        $newQueryString = implode("&", $key_words);

        $bulkForUpdateComments = new MongoDB\Driver\BulkWrite();
        $bulkForUpdateComments->update(['_id' => new MongoDB\BSON\ObjectId($_POST['post_id'])], ['$set' => ['post_comment_count' => $commentCount]]);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error: " . $e->getMessage());
    }

    $resultForUpdateComments = $managerForComments->executeBulkWrite($namespace, $bulkForUpdateComments);

    header("location: ../single-blog.php?" . $newQueryString);

}