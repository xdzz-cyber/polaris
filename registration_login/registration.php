<?php

session_start();

require_once("../functions.php");

if (!isset($_POST['name'], $_POST['password'], $_POST['email'], $_POST['send']) && empty($_POST['name']) && empty($_POST['email']) && empty($_POST['password'])) {
    $msg = $_GET['msg'] ?? "Get started with your free account";
    require_once("../partials/header.php");?>


    <div class="container">

        <div class="card bg-light">
            <article class="card-body mx-auto" style="max-width: 400px;">
                <h4 class="card-title mt-3 text-center">Create Account</h4>
                <p class="text-center"><?=$msg?></p>
                <form action="registration.php" method="post" enctype="multipart/form-data">
                    <div class="form-group input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> <i class="fa fa-user"></i> </span>
                        </div>
                        <input name="name" id="name" class="form-control" placeholder="Username" type="text">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> <i class="fa fa-key"></i> </span>
                        </div>
                        <input name="password" id="password" class="form-control" placeholder="Password"
                               type="password">
                    </div>

                    <div class="form-group input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> <i class="fa fa-envelope"></i> </span>
                        </div>
                        <input name="email" id="email" class="form-control" placeholder="Email" type="email">
                    </div>
                    <div class="form-group input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"> <i class="fa fa-image"></i> </span>
                        </div>
                        <input name="photo" id="photo" class="form-control" placeholder="photo" type="file">
                    </div>


                    <div class="form-group">
                        <input name="send" type="submit" class="btn btn-primary btn-block" value="Create Account"/>
                    </div>
                    <p class="text-center">Have an account? <a href="login.php">Log In</a></p>
                </form>
            </article>
        </div> <!-- card.// -->

    </div>

    <?php
    require_once("../partials/footer.php");
} else if (isset($_POST['name'], $_POST['password'], $_POST['email'], $_POST['send']) && !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {


    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    $query = new MongoDB\Driver\Query(['name' => strval($_POST['name']), 'password' => hash("sha256", $_POST['password'])]);

    $rows = checkForQueryError($manager, "usersDB.users", $query);


    $rows = $rows->toArray();


    if (!$rows) {

        if ($_FILES['photo']['error'] == 0) {

            $filenameTMP = $_FILES['photo']['tmp_name'];
            $filename = time() . $_FILES['photo']['name'];

            move_uploaded_file($filenameTMP, "../usersPhoto/{$filename}");
        } else {
            $filename = "noPhoto.png";
        }

        $bulk = new MongoDB\Driver\BulkWrite;

        $name = strval($_POST['name']);
        $hashedPassword = hash("sha256", $_POST['password']);
        $email = strval($_POST['email']);
        $photoName = strval($filename);

        $newUser = [
            "_id" => new MongoDB\BSON\ObjectId,
            "name" => $name,
            "password" => $hashedPassword,
            "email" => $email,
            "photo" => $photoName
        ];

        try {
            $bulk->insert($newUser);
            $result = $manager->executeBulkWrite("usersDB.users", $bulk);
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            die("Error: " . $e->getMessage());
        }

        setcookie("_id", $newUser['_id'], time() + 60 + 60 * 24 * 30, "/");
        setcookie("name", $newUser['name'], time() + 60 + 60 * 24 * 30, "/");
        setcookie("email", $newUser['email'], time() + 60 + 60 * 24 * 30,"/");
        setcookie("photo", $newUser['photo'], time() + 60 + 60 * 24 * 30, "/");

        header("location: ../index.php");

    } else {
        header("location: registration.php?msg=userAlreadyExists");
    }
} else{
    header("location: registration.php");
}
?>