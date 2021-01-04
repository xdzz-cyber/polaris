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
                            <form class="row contact_form" action="deleteVideo.php" method="post">

                                <div class="col-md-12 form-group p_star">
                                    <!--                                    <label for="">Confrim delete</label>-->
                                    Yes <input type="radio" class="form-control newPost" id="confirm" name="confirm"
                                               value="1">
                                    <!--                                    <label for="">Cancel delete</label>-->
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
} else if (isset($_POST['confirm'], $_POST['post_id'], $_POST['currentPage'], $_POST['send']) && !empty($_POST['post_id']) && !empty($_POST['currentPage'])) {
    if (intval($_POST['confirm']) < 1) {
        header("location: ../single-blog.php?unsetNews=true&currentPage={$_POST['currentPage']}"); //post_id={$_POST['post_id']}
    } else {
        $bulk = new MongoDB\Driver\BulkWrite;
        try {
            $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

            $queryToUnlink = new MongoDB\Driver\Query(["post_id" => $_POST['post_id']]);
            $resultUnlink = checkForQueryError($manager, "usersDB.user_videos", $queryToUnlink);

            foreach ($resultUnlink as $row) {
                $filename = $row->file_name;
                unlink("../videos/userVideos/{$filename}");
            }

            $bulk->delete(["post_id" => $_POST['post_id']]);

            $result = $manager->executeBulkWrite("usersDB.user_videos", $bulk);

            header("location: ../single-blog.php?unsetNews=true&currentPage={$_POST['currentPage']}");
        } catch (MongoDB\Driver\Exception\Exception $e) {
            die("Error:" . $e);
        }
    }

} else {
    header("location: " . $_SERVER['HTTP_REFERER']);
}

?>

