<footer class="footer_part">
    <div class="container">
        <div class="row justify-content-around">
            <div class="col-sm-6 col-lg-2">
                <div class="single_footer_part">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <?php
                        $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

                        try{
                            $rows = $manager->executeQuery("usersDB.user_posts", new MongoDB\Driver\Query(["user_id"=>$_SESSION['_id']]));
                        } catch (\MongoDB\Driver\Exception\Exception $e){
                            die("Error: " . $e->getMessage());
                        }

                        $rowCounter = 0;
                        foreach ($rows as $row)$rowCounter++;

                        if ($rowCounter >  0) echo "<li><a href=\"../single-blog.php\">Watch your posts</a></li>";
                        ?>

                        <li><a href="../blog.php">Go see other users interesting posts</a></li>
                        <li><a href="../blog.php?news=true">News</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="single_footer_part">
                    <h4>Newsletter</h4>
                    <p>Subscribe to get latest news
                    </p>
                </div>
            </div>
        </div>

    </div>
    <div class="copyright_part">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="copyright_text">
                        <p>
                            Copyright &copy;<script>document.write(new Date().getFullYear());</script>
                            All rights reserved | This website is made with <i class="ti-heart" aria-hidden="true"></i>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer_icon social_icon">
                        <ul class="list-unstyled">
                            <li><a href="https://www.facebook.com/profile.php?id=100017666172871"
                                   class="single_social_icon"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="https://twitter.com/Nadhobbog" class="single_social_icon"><i
                                            class="fab fa-twitter"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!--::footer_part end::-->

<!-- jquery plugins here-->
<!--<script src="../js/jquery-1.12.1.min.js"></script>-->
<!-- popper js -->
<script src="../js/popper.min.js"></script>
<!-- bootstrap js -->
<script src="../js/bootstrap.min.js"></script>
<!-- easing js -->
<script src="../js/jquery.magnific-popup.js"></script>
<!-- swiper js -->
<script src="../js/swiper.min.js"></script>
<!-- swiper js -->
<script src="../js/masonry.pkgd.js"></script>
<!-- particles js -->
<script src="../js/owl.carousel.min.js"></script>
<script src="../js/jquery.nice-select.min.js"></script>
<!-- slick js -->
<script src="../js/slick.min.js"></script>
<script src="../js/jquery.counterup.min.js"></script>
<script src="../js/waypoints.min.js"></script>
<script src="../js/contact.js"></script>
<script src="../js/jquery.ajaxchimp.min.js"></script>
<script src="../js/jquery.form.js"></script>
<script src="../js/jquery.validate.min.js"></script>
<script src="../js/mail-script.js"></script>
<!-- custom js -->
<script src="../js/custom.js"></script>

<script>
    let submitForm = false;

    // Update and Delete comments stuff
    let elementsArrayLength = Array.from(document.querySelectorAll(".modifyComment")).length;

    let commentDisabledObject = {};

    for (let i = 0; i < elementsArrayLength; i++) {

        let currentChangeComment = document.querySelector("#changeComment" + i);
        let currentDeleteComment = document.querySelector("#deleteComment" + i);

        let currentUserComment = document.querySelector(".current_user_comment" + i);
        let currentModifyCommentMode = document.querySelector(".modify_comment_mode" + i);

        let currentModifyComment = document.querySelector(".modifyComment" + i);

        commentDisabledObject[i] = false;

        currentChangeComment.addEventListener("click", () => {

            if (commentDisabledObject[i] === false) {
                currentChangeComment.textContent = "Update";
                currentUserComment.disabled = false;
                commentDisabledObject[i] = true;
            } else {
                currentModifyCommentMode.value = "update";
                currentModifyComment.submit();
            }
        })

        currentDeleteComment.addEventListener("click", () => {

            if (commentDisabledObject[i] === false) {
                currentDeleteComment.textContent = "Confirm delete";
                commentDisabledObject[i] = true;
            } else {
                currentModifyCommentMode.value = "delete";
                currentModifyComment.submit();
            }
        })
    }

</script>
</body>

</html>