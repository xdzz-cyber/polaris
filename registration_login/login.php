<?php

require_once("oauthLogin.php");
require_once("../functions.php");

$login_url = "";

if (isset($_GET['code'])) {


    $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $google_client->setAccessToken($token['access_token']);

        $_SESSION['access_token'] = $token['access_token'];

        $google_service = new Google_Service_Oauth2($google_client);

        $data = $google_service->userinfo->get();
        $_SESSION['data'] = $data;

        if (!empty($data['given_name'])) {
            $_SESSION['name'] = $data['given_name'];
        }

        if (!empty($data['email'])) {
            $_SESSION['email'] = $data['email'];
        }  /// need _id and photo

        if (!empty($data['picture'])) {
            $_SESSION['photo'] = $data['picture'];
        }

        $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
        $query = new MongoDB\Driver\Query(['name' => $_SESSION['name'], 'email' => $_SESSION['email'], 'photo' => $_SESSION['photo']]);

        try {
            $rows = $manager->executeQuery("usersDB.users", $query);

            $countToFindUSer = 0;

            foreach ($rows as $row) {
                $user_id = strval($row->_id);
                $countToFindUSer++;
            }

            if ($countToFindUSer > 0) {
                $_SESSION['_id'] = $user_id;
            } else {
                $_SESSION['_id'] = new MongoDB\BSON\ObjectId();
            }
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            die("Error: " . $e->getMessage());
        }

    }

}

if (!isset($_SESSION['access_token'])) {
    $login_url = $google_client->createAuthUrl();
}


if (isset($_SESSION['_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['photo'])) {
    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);
    $queryToFind = new MongoDB\Driver\Query(['name' => $_SESSION['name'], 'email' => $_SESSION['email']]);
    $rowsToFind = checkForQueryError($manager,"usersDB.users" ,$queryToFind);
    $recordsToFInd = iterator_to_array($rowsToFind);

    if (!$recordsToFInd || count($recordsToFInd) < 0) {
        $bulk = new MongoDB\Driver\BulkWrite;

        $newUser = [
            "_id" => $_SESSION['_id'],
            "name" => $_SESSION['name'],
            "password" => "user's google password",
            "email" => $_SESSION['email'],
            "photo" => $_SESSION['photo']
        ];

        try {
            $bulk->insert($newUser);
            $result = $manager->executeBulkWrite("usersDB.users", $bulk);
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            die("Error: " . $e->getMessage());
        }

    }
    header("location: ../index.php");
}

if (!isset($_POST['name'], $_POST['password']) || empty($_POST['name']) || empty($_POST['password'])) {
    $msg = $_GET['msg'] ?? "Please Sign in now";

    require_once("../partials/header.php");
    ?>

    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_text text-center">
                        <div class="login_part_text_iner">
                            <h2>New on Polaris?</h2>
                            <p>There are advances being made in science and technology
                                everyday, and a good example of this is the</p>
                            <a href="registration.php" class="btn_3">Create an Account</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3><?= $msg ?></h3>
                            <form class="row contact_form" action="login.php" method="post">

                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control" id="name" name="name" value=""
                                           placeholder="Username">
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <input type="password" class="form-control" id="password" name="password" value=""
                                           placeholder="Password">
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <a href="<?= $login_url ?>"><img
                                                src="../googleImages/btn_google_signin_light_normal_web.png"
                                                alt="google sign in button"></a>
                                </div>

                                <div class="col-md-12 form-group">
                                    <input type="submit" class="btn_3" name="send" value="Login">
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

} else if (isset($_POST['name'], $_POST['password']) && !empty($_POST['name']) && !empty($_POST['password'])) {

    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    $query = new MongoDB\Driver\Query(['name' => strval($_POST['name']), 'password' => hash("sha256", $_POST['password'])]);

    $rows = checkForQueryError($manager, "usersDB.users", $query);

    $records = iterator_to_array($rows);


    if ($records) {

        $_id = "";
        $name = "";
        $email = "";
        $photo = "";

        foreach ($records as $record) {
            $_id = $record->_id;
            $name = strval($record->name);
            $email = strval($record->email);
            $photo = strval($record->photo);
        }

        setcookie("_id", $_id, time() + 60 + 60 * 24 * 30, "/");
        setcookie("name", $name, time() + 60 + 60 * 24 * 30, "/");
        setcookie("email", $email, time() + 60 + 60 * 24 * 30, "/");
        setcookie("photo", $photo, time() + 60 + 60 * 24 * 30, "/");

        header("location: ../index.php");
    } else {
        header("location: login.php?msg=wrongData");
    }

}
?>