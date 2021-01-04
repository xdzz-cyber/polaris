<?php
session_start();

require_once ("../functions.php");

if (!isset($_POST['confirm'], $_POST['send']) && isset($_GET['post_id'], $_GET['currentPage']) && !empty($_GET['post_id']) && !empty($_GET['currentPage'])) {
    require_once("../partials/header.php");
    ?>
    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center d-flex justify-content-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3>Please, fill the empty blanks below</h3>
                            <form class="row contact_form" action="delete.php" method="post" novalidate="novalidate">

                                <div class="col-md-12 form-group p_star">
                                    Yes <input type="radio" class="form-control newPost" id="confirm" name="confirm"
                                               value="1">
                                    No <input type="radio" class="form-control newPost" id="confirm" name="confirm"
                                              value="0">
                                </div>

                                <div class="col-md-12 form-group">
                                    <input type="hidden" name="post_id" value="<?= $_GET['post_id'] ?>">
                                    <input type="hidden" name="currentPage" value="<?= $_GET['currentPage'] ?>">
                                    <input type="submit" value="Confirm" name="send" class="btn_3"/>
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
} else if (isset($_POST['confirm'], $_POST['post_id'], $_POST['send']) && !empty($_POST['post_id'])) {
    if (intval($_POST['confirm']) < 1) {
        header("location: ../single-blog.php?unsetNews=true&currentPage={$_POST['currentPage']}");
    } else {
        $bulk = new MongoDB\Driver\BulkWrite;
        try {
            $bulk->delete(["_id" => new MongoDB\BSON\ObjectId($_POST['post_id'])]);
            $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
            $result = $manager->executeBulkWrite("usersDB.user_posts", $bulk);
            header("location: ../blog.php");
        } catch (MongoDB\Driver\Exception\Exception $e) {
            die("Error:" . $e);
        }
    }

} else {
    header("location: " . $_SERVER['HTTP_REFERER']);
}

?>

