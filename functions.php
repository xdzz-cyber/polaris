<?php

require_once realpath(__DIR__ . "/vendor/autoload.php");

use Dotenv\Dotenv;


function checkForQueryError($executeVar, $namespace, $query)
{
    try {
        return $executeVar->executeQuery($namespace, $query);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

function getEnvVars($mongodb_uri = false, $email_username = false, $email_pass = false, $email_name = false){

    $constNames = ["MANAGER_URI", "EMAIL", "PASSWORD", "NAME"];

    $dotenv = Dotenv::createUnsafeImmutable(__DIR__);
    $dotenv->load();

    $args = func_get_args();
    $answer = [];

    $i = 0;
    foreach ($args as $arg){
        if ($arg !== false){
            $answer[] = getenv($constNames[$i]);
        }
        $i++;
    }
    return $answer;
}

function receiveNews($currentPage = 0, $recentPosts = false)
{
    $url = "http://newsapi.org/v2/everything?domains=wsj.com&apiKey=87b0b9f3d982441c996d6a47bd2daf19";
    $response = file_get_contents($url);
    $newsData = json_decode($response, true);

    switch ($recentPosts) {
        case true:
            $countAllNews = $_SESSION['countForAll'];
            $skip = $countAllNews - $_SESSION['limitForAll'];
            $limit = $_SESSION['limitForAll'];
            break;
        case false:
            $skip = 0;
            $limit = $_SESSION['countForAll'];
            $countAllNews = intval($newsData['totalResults']);
            break;
        default:
            $limit = 3;
            $skip = $limit * (intval($currentPage) - 1);
            $countAllNews = intval($newsData['totalResults']);
            break;
    }

    $countNews = 0;

    $resultArr = [];

    $totalResults = [];

    for ($i = $skip; $i < $countAllNews; $i++) {

        if ($newsData['articles'][$i]['source']['id'] && $countNews < $limit) {
            $resultArr[] = $newsData['articles'][$i];
            $totalResults[] = $newsData['totalResults'][$i];
            $countNews++;
        }
    }
    return array("result" => $resultArr, "totalResults" => $totalResults);
}


function sendEmail($to = "", $subject = "", $message = "")
{
    require_once("phpmailer/PHPMailerAutoload.php");

    $mail = new PHPMailer();
    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";

    $mail->Username = getEnvVars(false,true)[0];
    $mail->Password = getEnvVars(false,false,true)[0];

    $mail->setFrom(getEnvVars(false,true)[0], getEnvVars(false,false, false, true)[0]);

    if (!empty($to)) {
        $mail->addAddress($to);
    } else {
        $to = getEnvVars(false,true)[0];
        $mail->addAddress($to);
    }
    $mail->addReplyTo(getEnvVars(false,true)[0]);

    $mail->isHTML(true);
    if (!empty($message) && !empty($subject)) {
        $mail->Subject = $subject;
        $mail->Body = $message;
    } else {
        $mail->Subject = "Test subject from php script";
        $mail->Body = "<h1>We're glad that you chose us</h1> <p>Can't wait to send you our latest news</p>";
    }

    try {
        $mail->send();
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}


function findComments($isComment, $user_id, $post_id)
{

    $managerForComments = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    $queryForComments = new MongoDB\Driver\Query(["post_id" => strval($post_id)]);

    try {
        $rowsForComments = $managerForComments->executeQuery("usersDB.comments", $queryForComments);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error:" . $e->getMessage());
    }

    $comments = [];


    $users = [];


    foreach ($rowsForComments as $comment) {
        if ($isComment) {
            if ($comment->user_id == $user_id) {
                $comments[] = array("comment_id" => strval($comment->_id), "post_id" => $comment->post_id, "comment_content" => $comment->comment_content, "comment_date" => $comment->comment_date, "user_id" => $comment->user_id, "current_user_comment" => true);
            } else {
                $comments[] = array("comment_id" => strval($comment->_id), "post_id" => $comment->post_id, "comment_content" => $comment->comment_content, "comment_date" => $comment->comment_date, "user_id" => $comment->user_id, "current_user_comment" => false);
            }

        } else {

            $queryForUsers = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId($comment->user_id)]);

            $rowsForUsers = checkForQueryError($managerForComments, "usersDB.users", $queryForUsers);

            $rowsForUsers2 = iterator_to_array($rowsForUsers);


            foreach ($rowsForUsers2 as $user) {  // only ONE USER so time complexity is ok

                $match = false;

                if (!empty($users)) {
                    foreach ($users as $user2) {
                        if ($user2['username'] == $user->name) {
                            $match = true;
                        }
                    }
                }

                if ($match) {
                    continue;
                } else {
                    $users[] = array("user_id" => $user->_id, "username" => $user->name, "user_photo" => $user->photo);
                }

            }

        }

    }

    if ($isComment) {
        return $comments;
    } else {
        return $users;
    }

}

function testTags($tags)
{
    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
    $query = new MongoDB\Driver\Query(["post_tags" => $tags]);

    if (isset($_SESSION['isNews'])) {
        $namespace = "usersDB.news_posts";
    } else {
        $namespace = "usersDB.user_posts";
    }

    try {
        $rows = $manager->executeQuery($namespace, $query);
    } catch (\MongoDB\Driver\Exception\Exception $e) {
        die("Error: " . $e->getMessage());
    }

    $count = 0;

    foreach ($rows as $row) {
        $count++;
    }

    return $count;
}


function categoryNotEmpty($cat_name, $manager, $queryParam)
{
    $toUserTable = isset($_GET['news']) ? "usersDB.news_posts" : "usersDB.user_posts";
    $queryForCategories = new MongoDB\Driver\Query(['category' => $cat_name]);

    $resultForCategories = checkForQueryError($manager,$toUserTable, $queryForCategories);

    $countForCategories = 0;

    foreach ($resultForCategories as $row) {
        if ($countForCategories > 0) break;
        $countForCategories++;
    }

    if ($countForCategories > 0) {
        return "blog.php?" . $queryParam . "&category={$cat_name}";
    }
    return "";
}

function returnNamespace()
{
    if (isset($_SESSION['isNews'])) {
        return "usersDB.news_posts";
    } else {
        return "usersDB.user_posts";
    }
}
