<?php

session_start();
require_once("../functions.php");

if (isset($_POST['message'], $_POST['subject'], $_POST['send']) && !empty($_POST['message']) && !empty($_POST['subject'])) {
    sendEmail("", $_POST['subject'], $_POST['message']);
}


if (isset($_COOKIE['_id'], $_COOKIE['name'], $_COOKIE['email'], $_COOKIE['photo'])) {
    $_SESSION['_id'] = $_COOKIE['_id'];
    $_SESSION['name'] = $_COOKIE['name'];
    $_SESSION['email'] = $_COOKIE['email'];
    $_SESSION['photo'] = $_COOKIE['photo'];
}

if (isset($_SESSION['_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['photo'])) {

    require_once("../partials/header.php");
    ?>

    <section class="contact-section padding_top">
        <div class="container">
            <div class="d-none d-sm-block mb-5 pb-4">


                <div class="row">
                    <div class="col-12">
                        <h2 class="contact-title">Get in Touch</h2>
                    </div>
                    <div class="col-lg-8">
                        <form class="form-contact contact_form m-1" action="contact.php" method="post"
                              novalidate="novalidate">
                            <div class="row">
                                <div class="col-12 my-3">
                                    <div class="form-group">

                  <textarea style="border: 1px solid #000!important;" class="form-control w-100" name="message"
                            id="message" cols="30" rows="9"
                            onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter Message'"
                            placeholder='Enter Message'></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <input style="border: 1px solid #000!important;" class="form-control"
                                               name="subject" id="subject" type="text" onfocus="this.placeholder = ''"
                                               onblur="this.placeholder = 'Enter Subject'" placeholder='Enter Subject'>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button name="send" type="submit" class="btn_3 button-contactForm">Send Message</button>
                                <!--              <a href="#" class="btn_3 button-contactForm"></a>-->
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4">
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-home"></i></span>
                            <div class="media-body">
                                <h3>Buttonwood, California.</h3>
                                <p>Rosemead, CA 91770</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-tablet"></i></span>
                            <div class="media-body">
                                <h3>00 (440) 9865 562</h3>
                                <p>Mon to Fri 9am to 6pm</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-email"></i></span>
                            <div class="media-body">
                                <h3>support@colorlib.com</h3>
                                <p>Send us your query anytime!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>


    <?php
    require_once("../partials/footer.php");

} else {
    header("location: ../registration_login/registration.php");
}
?>