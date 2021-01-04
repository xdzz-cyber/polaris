<?php
session_start();

require_once ("../functions.php");

if (!isset($_POST['title'], $_POST['content'], $_POST['post_tags'], $_POST['send'])) {
    require_once("../partials/header.php");
    ?>
    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center d-flex justify-content-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3>Please, fill the empty blanks below</h3>
                            <form class="row contact_form" action="createPost.php" method="post"
                                  novalidate="novalidate">

                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control newPost" id="title" name="title" value=""
                                           placeholder="title">
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <textarea class="form-control newPost" name="content" id="content" cols="30"
                                              rows="10" placeholder="content"></textarea>
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control newPost" id="post_tags" name="post_tags"
                                           value=""
                                           placeholder="post_tags">
                                </div>

                                <div class="col-md-12 form-group">
                                    <input type="submit" value="Create" name="send" class="btn_3"/>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once("../partials/footer.php");
} else if (isset($_POST['title'], $_POST['content'], $_POST['post_tags'], $_POST['send']) && !empty($_POST['title']) && !empty($_POST['content']) && !empty($_POST['post_tags'])) {
    $bulk = new MongoDB\Driver\BulkWrite;

    $title = $_POST['title'];
    $content = $_POST['content'];
    $post_tags = $_POST['post_tags'];

    $newPost = [
        '_id' => new MongoDB\BSON\ObjectId,
        'postTitle' => $title,
        'postBody' => $content,
        "postDate" => date("d-m-Y h:m:s"),
        'user_photo' => $_SESSION['photo'],
        'user_id' => $_SESSION['_id'],
        'post_tags' => $post_tags,
        "post_comment_count" => "0",
        "likes_count" => 0
    ];

    try {
        $bulk->insert($newPost);
        $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
        $result = $manager->executeBulkWrite("usersDB.user_posts", $bulk);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Error:" . $e);
    }
    header("location: ../index.php");
} else {
    header("location: ../createPost.php");
}
?>

