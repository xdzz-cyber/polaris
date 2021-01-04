<?php
session_start();

require_once("../functions.php");

if (!isset($_POST['title'], $_POST['content'], $_POST['send']) && isset($_GET['post_id'], $_GET['currentPage']) && !empty($_GET['post_id'])) {

    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
    $query = new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectId($_GET['post_id'])]);

    $rows = checkForQueryError($manager, "usersDB.user_posts", $query);

    foreach ($rows as $row) {
        $postTitle = $row->postTitle;
        $postBody = $row->postBody;
        $postTags = $row->post_tags;
    }

    require_once("../partials/header.php");
    ?>
    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center d-flex justify-content-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3>Please, fill the empty blanks below</h3>
                            <form class="row contact_form" action="edit.php" method="post" novalidate="novalidate">

                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control newPost" id="title" name="title"
                                           value="<?= $postTitle ?>"
                                           placeholder="title">
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <textarea class="form-control newPost" name="content" id="content" cols="30"
                                              rows="10" placeholder="content"><?= $postBody ?></textarea>
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control newPost" id="post_tags" name="post_tags"
                                           value="<?= $postTags ?>"
                                           placeholder="post_tags">
                                </div>

                                <div class="col-md-12 form-group">
                                    <input type="hidden" name="post_id" value="<?= $_GET['post_id'] ?>">
                                    <input type="hidden" name="currentPage" value="<?= $_GET['currentPage'] ?>">
                                    <input type="submit" value="Update" name="send" class="btn_3"/>
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
} else if (isset($_POST['title'], $_POST['content'], $_POST['post_tags'], $_POST['post_id'], $_POST['currentPage'], $_POST['send']) && !empty($_POST['title']) && !empty($_POST['content']) && !empty($_POST['post_tags']) && !empty($_POST['post_id'])) {


    $title = $_POST['title'];
    $content = $_POST['content'];
    $post_tags = $_POST['post_tags'];

    try {
        $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(["_id" => new MongoDB\BSON\ObjectId($_POST['post_id'])], ['$set' => ['postTitle' => $title, 'postBody' => $content, 'post_tags' => $post_tags]]);
        $result = $manager->executeBulkWrite("usersDB.user_posts", $bulk);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Error:" . $e);
    }
    if (!empty($_POST['currentPage']) && $_POST['currentPage'] > 1) {
        header("location: ../single-blog.php?currentPage={$_POST['currentPage']}");
    } else {
        header("location: ../single-blog.php");
    }

}
?>

