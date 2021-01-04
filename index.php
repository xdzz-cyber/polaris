<?php

session_start();

require_once "functions.php";

unset($_SESSION['isNews']);

if (isset($_SESSION['search_input'])) {
    $_SESSION['search_input'] = "";
}

if (isset($_COOKIE['_id'], $_COOKIE['name'], $_COOKIE['email'], $_COOKIE['photo'])) {
    $_SESSION['_id'] = $_COOKIE['_id'];
    $_SESSION['name'] = $_COOKIE['name'];
    $_SESSION['email'] = $_COOKIE['email'];
    $_SESSION['photo'] = $_COOKIE['photo'];
}

if (isset($_POST['form_email'], $_POST['send']) && !empty($_POST['form_email'])) {
    sendEmail($_POST['form_email']);
}

if (isset($_SESSION['_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['photo'])) {

    require_once("partials/header.php");
    ?>

    <section class="banner_part">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="banner_slider owl-carousel">

                        <div class="single_banner_slider">
                            <div class="row">
                                <div class="col-lg-5 col-md-8">
                                    <div class="banner_text">
                                        <div class="banner_text_iner">
                                            <h1>All users posts</h1>
                                            <p>In this section you can find answer to any of your question and even ask
                                                your own</p>
                                            <a href="blog.php" class="btn_2">Go to posts</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner_img d-none d-lg-block">
                                    <img src="img/bannerImages/posts.jpg" alt="">
                                </div>
                            </div>
                        </div>

                        <div class="single_banner_slider">
                            <div class="row">
                                <div class="col-lg-5 col-md-8">
                                    <div class="banner_text">
                                        <div class="banner_text_iner">
                                            <h1>Check out latest news</h1>
                                            <p>Go read latest news from all around the world</p>
                                            <a href="blog.php?news=true" class="btn_2">Go to news</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="banner_img d-none d-lg-block">
                                    <img src="img/bannerImages/theNews.webp" alt="">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="subscribe_area section_padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="subscribe_area_text text-center">
                        <h5>Join Our Newsletter</h5>
                        <h2>Subscribe to get Updated
                            with new offers</h2>

                        <form action="index.php" method="post">
                            <div class="input-group">
                                <input type="email" name="form_email" class="form-control"
                                       placeholder="enter email address"
                                       aria-label="Recipient's username" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button name="send" type="submit" id="basic-addon2" class="input-group-text btn_2">
                                        subscribe now
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    require_once("partials/footer.php");
} else {
    header("location: registration_login/registration.php");
}
?>