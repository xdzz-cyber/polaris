<?php
require_once("functions.php");

session_start();

if (!isset($_SESSION['allData']) || !empty($_SESSION['allData'])) {
    $_SESSION['oldAllData'] = $_SESSION['allData'];
}

if (!isset($_SESSION['redditTitle'])) {
    $_SESSION['redditTitle'] = "";
}

if (isset($_GET['addVideo'])) {
    header("location: add/addVideo.php?post_id={$_GET['post_id']}&addVideo&unsetNews=true&currentPage={$_GET['currentPage']}");
}

if (isset($_GET['news'])) {
    $_SESSION['isNews'] = true;
}

if (isset($_GET['unsetNews'])) {
    unset($_SESSION['isNews']);
}

if (isset($_POST['form_email'], $_POST['send']) && !empty($_POST['form_email'])) {
    sendEmail($_POST['form_email']);
}


if (isset($_COOKIE['_id'], $_COOKIE['name'], $_COOKIE['email'], $_COOKIE['photo'])) {
    $_SESSION['_id'] = $_COOKIE['_id'];
    $_SESSION['name'] = $_COOKIE['name'];
    $_SESSION['email'] = $_COOKIE['email'];
    $_SESSION['photo'] = $_COOKIE['photo'];
}

if (isset($_SESSION['_id'], $_SESSION['name'], $_SESSION['email'], $_SESSION['photo'])) {


    require_once("partials/header.php");
    require_once("recent_posts.php");
    require_once("count_num_pages.php");

    if (isset($_GET['currentPage']) && !empty($_GET['currentPage'])) {
        $currentPage = $_GET['currentPage'];
    } else {
        $currentPage = 1;
    }

    $perPage = 1;
    $skip = ($currentPage - 1) * $perPage;
    $prev = $currentPage - 1;
    $next = $currentPage + 1;

    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    unset($_SESSION['data']);
    $_SESSION['data'] = [];

    $postsDay = [];

    if (isset($_SESSION['isNews'])) {
        $query = new MongoDB\Driver\Query([]);

        $rows = checkForQueryError($manager, "usersDB.news_posts", $query);

        foreach ($rows as $value) {
            $_SESSION['data'][] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "post_photo" => $value->post_photo, "postDate" => $value->postDate, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count, "post_tags" => $value->post_tags);
            $postsDay[] = substr($value->postDate, 0, 2);
        }

    } else {

        if (!isset($_GET['singleBlog'])) {
            $query = new MongoDB\Driver\Query(["user_id" => $_SESSION['_id']], ["limit" => $perPage, "skip" => $skip]);
        } else {
            $query = new MongoDB\Driver\Query([]);
        }

        $rows = checkForQueryError($manager, "usersDB.user_posts", $query);

        foreach ($rows as $value) {
            $_SESSION['data'][] = array("user_id" => $value->user_id, "username" => "example", "post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "user_photo" => $value->user_photo, "postDate" => $value->postDate, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count, "post_tags" => $value->post_tags);

            $postsDay[] = substr($value->postDate, 0, 2);
        }

        $queryForUsernames = new MongoDB\Driver\Query([]);

        $rowsForUsernames = checkForQueryError($manager, "usersDB.users", $queryForUsernames);

        $usernames = [];

        foreach ($rowsForUsernames as $username) {
            $usernames[] = array("id" => $username->_id, "username" => $username->name);
        }

        for ($i = 0; $i < count($_SESSION['data']); $i++) {
            for ($j = 0; $j < count($usernames); $j++) {
                if ($_SESSION['data'][$i]['user_id'] == $usernames[$j]['id']) {
                    $_SESSION['data'][$i]['username'] = $usernames[$j]['username'];
                }
            }
        }
    }


    $numPages = ceil($countAllPages / $perPage);


    ?>

    <section class="blog_area single-post-area padding_top">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 posts-list">

                    <?php
                    $likesFormPath = "like.php?" . $_SERVER['QUERY_STRING'];

                    $foundCounter = 0;

                    foreach ($_SESSION['data'] as $data) {

                        if (isset($_GET['post_id']) && $_SESSION['data'][0]['user_photo']) {
                            $userPost = true;
                        } else {
                            $userPost = false;
                        }
                        if (isset($_GET['post_id']) && $_SESSION['data'][0]['post_photo']) {
                            $newsPost = true;
                        } else {
                            $newsPost = false;
                        }

                        if ($data['user_id'] == $_GET['post_id'] && $data['user_id'] == $_SESSION['_id']) $userPosts = true;


                        if ($userPost || $newsPost) $singleRecentPost = true;


                        if ($data['post_id'] == $_GET['post_id']) {
                            $foundCounter++;

                            if (!$newsPost) {
                                $user_id = $data['user_id'];
                                $photo = $data['user_photo'];
                            } else {
                                $photo = $data['post_photo'];
                            }
                            $post_id = $data['post_id'];
                            $str_post_id = strval($data['post_id']);

                            $title = $data['postTitle'];
                            $_SESSION['redditTitle'] = $title;

                            $content = substr($data['postBody'], 0, 250);

                            $post_tags = $data['post_tags'];

                            $post_comment_count = $data['post_comment_count'];
                            $likes_count = $data['likes_count'];

                            if ($userPost || $userPosts) {
                                $username = $userPost ? $data['username'] : $_SESSION['name'];
                                if (substr($photo, 0, 5) == "https") {
                                    $src = $photo;
                                } else {
                                    $src = "usersPhoto/{$photo}";
                                }
                            } else if ($newsPost) {
                                $src = $photo;
                            }
                        }
                    }

                    if ($foundCounter == 0) {
                        $userPosts = true;
                        foreach ($_SESSION['data'] as $v) {
                            $user_id = $_SESSION['_id'];
                            $username = $_SESSION['name'];
                            $post_id = $v['post_id'];
                            $str_post_id = strval($v['post_id']);

                            $post_comment_count = $v['post_comment_count'];

                            $likes_count = $v['likes_count'];

                            $title = $v['postTitle'];
                            $post_tags = $v['post_tags'];

                            $_SESSION['redditTitle'] = $title;

                            $content = $v['postBody'];


                            if (substr($v['user_photo'], 0, 5) == "https") {
                                $src = $v['user_photo'];
                            } else {
                                $src = "usersPhoto/{$v['user_photo']}";
                            }
                        }
                    }


                    echo "<div class='single-post'>
                            <div class='feature-img'>
                               <img style='max-width: 100%;height: 35vh;image-rendering: optimizeQuality;' class='img-fluid' src='{$src}' alt='post photo for single post'>
                            </div>
                            <div class='blog_details'>
                                <h2>{$title}</h2>
                                <ul class='blog-info-link mt-3 mb-4'>";
                    if (!empty($username)) {
                        echo "<li><a href='#'><i class='far fa-user'></i> {$username}</a></li>";
                    }

                    echo "<li><a href='#'><i class='far fa-comments'></i> {$post_comment_count} Comments</a></li>
                          <li><a href='#'><i class='far fa-tags'></i>{$post_tags}</a></li>";


                    if (!$userPost && !$newsPost) {
                        echo "<li><a href='edit/edit.php?post_id={$post_id}&currentPage={$currentPage}'><i class='far fa-edit'></i> Edit</a></li>
                                <li><a href='delete/delete.php?post_id={$post_id}&currentPage={$currentPage}'><i class='far fa-trash-alt'></i> Delete</a></li>
                                <li><a href='single-blog.php?post_id={$str_post_id}&addVideo&unsetNews=true&currentPage={$currentPage}'><i class='fas fa-video'></i> Upload video</a></li>
                                <li><a href='delete/deleteVideo.php?post_id={$post_id}&currentPage={$currentPage}'><i class='fas fa-video-slash'></i> Delete video</a></li>";
                    }

                    echo "</ul><p>{$content}</p>
                            </div>
                    </div>";


                    $comment_user_id = $_SESSION['_id'];

                    ?>

                    <form id="likeForm" action="<?= $likesFormPath ?>" method="post">
                        <div class="navigation-top">
                            <div class="d-sm-flex justify-content-between text-center">
                                <p class="like-info"><span class="align-middle p-1"><a
                                                onclick="document.querySelector('#likeForm').submit();"><i
                                                    class="far fa-heart"></i></a> <?= $likes_count ?>
                                    people like this</span>
                                    <span class="align-middle">
                                        <?php
                                        if (!$userPosts) echo '<a class="addBookmark"  href=""><i class="fas fa-bookmark"></i></a>';
                                        ?>
                                    </span>
                                </p>
                                <div class="col-sm-4 text-center my-2 my-sm-0">

                                </div>
                                <ul class="social-icons">
                                    <?php
                                    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                                    ?>
                                    <li>
                                        <a href="http://reddit.com/submit?title=<?= $_SESSION['redditTitle'] ?>&text=<?= $actual_link ?>"
                                           target="_blank"><i class="fab fa-reddit"></i></a></li>
                                    <li>
                                        <a href="http://twitter.com/share?url=<?= $actual_link ?>&text=<?= $_SESSION['redditTitle'] ?>&hashtags=<?= $post_tags ?>"
                                           target="_blank"><i class="fab fa-twitter"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <input type="hidden" name="like_post_id" value="<?= $post_id ?>">
                    </form>


                    <?php if (!$singleRecentPost) { ?>
                        <nav class="blog-pagination justify-content-center d-flex">
                            <ul class="pagination">
                                <?php
                                if (!($currentPage == 1) && $currentPage) { //&post_id={$post_id}
                                    echo "<li class='page-item'><a href='single-blog.php?currentPage={$prev}&unsetNews=true' class='page-link' aria-label='Previous'><i class='ti-angle-left'></i></a></li>";
                                }
                                ?>
                                <?php

                                for ($i = 1; $i <= $numPages; $i++) {
                                    if ($currentPage == $i) {
                                        echo "<li class='page-item'><a href='single-blog.php?currentPage={$i}&unsetNews=true' style='color: hotpink !important;' class='page-link'>{$i}</a></li>";
                                    } else {
                                        echo "<li class='page-item'><a href='single-blog.php?currentPage={$i}&unsetNews=true' class='page-link'>{$i}</a></li>";
                                    }
                                }
                                ?>
                                <?php
                                if (!($currentPage == $numPages) && $currentPage && $numPages) {
                                    echo "<li class='page-item'><a href='single-blog.php?currentPage={$next}&unsetNews=true' class='page-link' aria-label='Next'><i class='ti-angle-right'></i></a></li>";
                                }
                                ?>
                            </ul>
                        </nav>
                    <?php } ?>


                    <div class="comments-area">
                        <h4><?= $post_comment_count ?> Comments</h4>
                        <?php

                        $comment_path = "comments_contact/comments.php?" . $_SERVER['QUERY_STRING']; // to fetch and then set all the get params to be able to back to the same page(WE HAVE TO HAVE DIFFERENT GET PARAMS TO BE ABLE TO IDENTIFY POSTS)

                        if (!$singleRecentPost) {
                            if (isset($user_id) && !empty($user_id)) {
                                $user_id = strval($user_id);
                            } else {
                                $user_id = strval($comment_user_id);
                            }
                            $post_id = strval($post_id);
                            $comment_user_id = strval($comment_user_id);

                        }

                        $comments = findComments(true, $comment_user_id, $post_id);
                        $users = findComments(false, $comment_user_id, $post_id);

                        $old_comment_count = count($comments);
                        $i = 0;

                        if (!empty($comments)) {
                            foreach ($users as $user) {
                                foreach ($comments as $comment) {
                                    if ($comment['user_id'] == $user['user_id'] && $comment['post_id'] == $post_id) {
                                        if (substr($user['user_photo'], 0, 5) == "https") {
                                            $src = $user['user_photo'];
                                        } else {
                                            $src = "usersPhoto/{$user['user_photo']}";
                                        }
                                        echo "
                   <div class=\"comment-list\">
                     <div class=\"single-comment justify-content-between d-flex\">
                        <div class=\"user justify-content-between d-flex\">
                           <div class=\"thumb\">
                              <img src=\"{$src}\" alt=\"user's photo\">
                           </div>
                           <div class=\"desc\">";
                                        if ($comment['current_user_comment']) {
                                            $modifyCommentPath = "edit/modifyComment.php?" . $_SERVER['QUERY_STRING'];
                                            echo " <form class='modifyComment modifyComment{$i}' action='{$modifyCommentPath}' method='post'>
                                                       <input name='newContent' class=\"comment current_user_comment{$i}\" disabled='true'  value='{$comment['comment_content']}'/>";
                                        } else {
                                            echo "<p class='comment'>{$comment['comment_content']}</p>";
                                        }
                                        echo "
                                 
                              <div class=\"d-flex justify-content-between\">
                                 <div class=\"d-flex align-items-center\">
                                    <h5>
                                       <a href=\"#\">{$user['username']}</a>
                                    </h5>
                                    <p class=\"date\">{$comment['comment_date']}</p>
                                 </div> ";
                                        if ($comment['current_user_comment']) {

                                            echo "
                                                <div class=\"reply-btn\">
                                    <button type='button' id='changeComment{$i}' class=\"btn btn-reply text-uppercase d-inline-block\">click to change</button>
                                    <button type='button' id='deleteComment{$i}' class=\"btn btn-reply text-uppercase d-inline-block\">delete</button>
                                    <input type='hidden' name='comment_id' value='{$comment['comment_id']}'>
                                    <input type='hidden' name='old_comment_count' value='{$old_comment_count}'>
                                    <input type='hidden' name='currentPage' value='{$currentPage}'>
                                    <input type='hidden' name='mode' class='modify_comment_mode{$i}'>
                                 </div>
                                            </form> ";
                                            $i++;
                                        }
                                        echo "
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                        ";
                                    }
                                }
                            }

                        }


                        ?>


                    </div>

                    <div class="comment-form">
                        <h4>Leave a Reply</h4>
                        <form action="<?= $comment_path ?>" method="post" class="form-contact comment_form"
                              id="commentForm">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                              <textarea style="border: 1px solid #000 !important;" class="form-control w-100"
                                        name="comment" id="comment" cols="30" rows="9"
                                        placeholder="Write Comment"></textarea>
                                        <input type="hidden" name="user_id" value="<?= $comment_user_id ?>">
                                        <input type="hidden" name="post_id" value="<?= $post_id ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button name="send" type="submit" class="btn_3 button-contactForm">Send Message</button>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="col-lg-4">
                    <div class="blog_right_sidebar">

                        <aside class="single_sidebar_widget popular_post_widget">
                            <h3 class="widget_title">Recent Posts</h3>
                            <?php

                            foreach ($_SESSION['recentPosts'] as $v) {
                                if (isset($_SESSION['isNews'])) {
                                    $photoAlt = "post photo";
                                    $src = $v['post_photo'];
                                } else {
                                    $photoAlt = "user photo";
                                    if (substr($v['user_photo'], 0, 5) == "https") {
                                        $src = $v['user_photo'];
                                    } else {
                                        $src = "usersPhoto/{$v['user_photo']}";
                                    }
                                }
                                $v['post_id'] = strval($v['post_id']);
                                $addToLink = isset($_SESSION['isNews']) ? '' : '&singleBlog=true';

                                echo "<div class='media post_item'>
                        <img width='150px' height='120px' src='{$src}' alt='{$photoAlt}'>
                        <div class='media-body'>
                        <a href='single-blog.php?post_id={$v['post_id']}{$addToLink}'>
                            <h3>{$v['postTitle']}</h3>
                        </a>
                        <p>{$v['postDate']}</p>
                        </div>
                        </div>";
                            }
                            ?>

                        </aside>


                        <aside class="single_sidebar_widget instagram_feeds">
                            <h4 class="widget_title">Instagram Feeds</h4>
                            <ul class="instagram_row flex-wrap">
                                <?php
                                for ($i = 0; $i < 6; $i++) {
                                    echo "<li><img class='img-fluid' src='https://picsum.photos/200?random={$i}' alt='instagram feed photo'></li>";
                                }

                                if (isset($_SESSION['isNews'])) {
                                    $queryParam = "news=true";
                                } else {
                                    $queryParam = "unsetNews=true";
                                }

                                ?>
                            </ul>
                        </aside>

                        <?php

                        if (isset($_GET['post_id'], $_GET['post_comment_count'])) {
                            $emailPath = "single-blog.php?post_id={$_GET['post_id']}&post_comment_count={$_GET['post_comment_count']}";
                        } else if (isset($_GET['unsetNews'])) {
                            $emailPath = "single-blog.php?unsetNews=true";
                        }
                        ?>

                        <aside class="single_sidebar_widget instagram_feeds">
                            <h4>Posts categories</h4>
                            <ul class="list-group categoryList">
                                <li class="list-group-item fa fa-business-time"><a class="category_name"
                                                                                   href="blog.php?<?= $queryParam ?>&category=business">Business
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           class="fas fa-minus-square float-right catDel" href=""></a></a></li>
                                <li class="list-group-item fas fa-microchip"><a class="category_name"
                                                                                href="blog.php?<?= $queryParam ?>&category=techcrunch">Tech
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                                </a></li>
                                <li class="list-group-item fas fa-journal-whills"><a class="category_name"
                                                                                     href="blog.php?<?= $queryParam ?>&category=wsj">WSJ
                                        <a class="fas fa-minus-square float-right catDel category_delete" href=""></a>
                                    </a></li>
                                <li class="list-group-item fab fa-apple"><a class="category_name"
                                                                            href="blog.php?<?= $queryParam ?>&category=apple">Apple
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                                <li class="list-group-item fab fa-bitcoin"><a class="category_name"
                                                                              href="blog.php?<?= $queryParam ?>&category=bitcoin">Bitcoin
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                            </ul>
                            <form action="addCategory.php" method="post" class="addCategoryForm">
                                <br><input class='btn btn-success sendCategory' type='submit' name='sendCategory'
                                           value='Add new category'/>
                            </form>
                        </aside>

                        <aside class="single_sidebar_widget newsletter_widget">
                            <h4 class="widget_title">Newsletter</h4>
                            <form action="<?= $emailPath ?>" method="post">
                                <div class="form-group">
                                    <input name="form_email" type="email" class="form-control"
                                           onfocus="this.placeholder = ''"
                                           onblur="this.placeholder = 'Enter email'" placeholder='Enter email' required>
                                </div>
                                <button name="send" class="button rounded-0 primary-bg text-white w-100 btn_1"
                                        type="submit">Subscribe
                                </button>
                            </form>
                        </aside>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    require_once("partials/footer.php");

} else {
    header("location:registration_login.php");
}
?>