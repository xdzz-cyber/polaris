<?php
session_start();

require_once ("../functions.php");

if (!isset($_POST['send']) || $_FILES['video']['error'] != 0) {

    require_once("../partials/header.php");
    ?>
    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center d-flex justify-content-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3>Please, choose the file to upload</h3>
                            <form class="row contact_form" action="addVideo.php" method="post"
                                  enctype="multipart/form-data">

                                <div class="col-md-12 form-group p_star">
                                    <input type="file" class="form-control newPost" id="video" name="video"
                                           placeholder="video" accept="video/mp4,video/x-m4v,video/*">
                                </div>

                                <div class="col-md-12 form-group">
                                    <input type="hidden" name="post_id" value="<?= $_GET['post_id'] ?>">
                                    <input type="hidden" name="currentPage" value="<?= $_GET['currentPage'] ?>">
                                    <input type="submit" value="Upload" name="send" class="btn_3"/>
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
} else if (isset($_POST['send']) && $_FILES['video']['error'] == 0) {

    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    $bulk = new \MongoDB\Driver\BulkWrite();
    $newVideo = [
        "_id" => new MongoDB\BSON\ObjectId(),
        "post_id" => $_POST['post_id'],
        "file_name" => time() . basename($_FILES['video']['name'])
    ];

    try{
        $bulk->insert($newVideo);
        $result = $manager->executeBulkWrite("usersDB.user_videos", $bulk);
    } catch (\MongoDB\Driver\Exception\Exception $e){
        die("Error:" . $e->getMessage());
    }

    $filenameTMP = $_FILES['video']['tmp_name'];
    $target_path = "../videos/userVideos/"; //PUT IN THE PATH WHERE YOU WANT THE UPLOADED FILE TO GO.
    $target_path = $target_path . $newVideo['file_name'];
    move_uploaded_file($filenameTMP, $target_path);

    header("location: ../single-blog.php?unsetNews=true&currentPage={$_POST['currentPage']}");

}
?>


