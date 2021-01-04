<?php
session_start();

require_once("functions.php");

if (!isset($_SESSION['allData']) || !empty($_SESSION['allData'])) {
    $_SESSION['oldAllData'] = $_SESSION['allData'];
    $_SESSION['allData'] = [];
}

if (!isset($_SESSION['dataNews']) || !empty($_SESSION['dataNews'])) {
    $_SESSION['dataNews'] = [];
}

if (!isset($_SESSION['dataUsers']) || !empty($_SESSION['dataUsers'])) {
    $_SESSION['dataUsers'] = [];
}


if (isset($_GET['unsetNews'])) {
    unset($_SESSION['isNews']);
}

if (isset($_SESSION['search_input'])) {
    unset($_SESSION['search_input']);
}

if (!isset($_GET['currentPage']) && !isset($_GET['search_input']) && !isset($_SESSION['search_input'])) {
    $_SESSION['search_input'] = "";
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

    if (isset($_GET['currentPage']) && !empty($_GET['currentPage'])) {
        $currentPage = $_GET['currentPage'];
    } else {
        $currentPage = 1;
    }


    $perPage = 3;
    $skip = ($currentPage - 1) * $perPage;
    $prev = $currentPage - 1;
    $next = $currentPage + 1;

    $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

    // check if search value is set

    if (isset($_GET['search_input']) && !empty($_GET['search_input'])) {
        $_SESSION['search_input'] = $_GET['search_input'];
        $countForTags = testTags($_SESSION['search_input']);
        if ($countForTags < 1) {
            $_SESSION['search_input'] = "";
        }
    }


    if (isset($_GET['category'])) {
        $category = $_GET['category'];
        $query = new MongoDB\Driver\Query(["post_tags" => new MongoDB\BSON\Regex("{$_SESSION['search_input']}"), "category" => $category], ["limit" => $perPage, "skip" => $skip]);

    } else {
        $query = new MongoDB\Driver\Query(["post_tags" => new MongoDB\BSON\Regex("{$_SESSION['search_input']}")], ["limit" => $perPage, "skip" => $skip]);
    }


    unset($_SESSION['data']);
    $_SESSION['data'] = [];

    $postsDay = [];

    $count = 0;

    if (isset($_GET['news']) && !empty($_GET['news']) || isset($_SESSION['isNews'])) {

        $_SESSION['isNews'] = true;

        $rows = checkForQueryError($manager, "usersDB.news_posts", $query);

        foreach ($rows as $value) {
            $count++;
            $_SESSION['data'][] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => substr($value->postBody, 0, 250), "post_photo" => $value->post_photo, "postDate" => $value->postDate, "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count); //"user_id"=>$value->user_id,  "username"=>"example",
            $postsDay[] = substr($value->postDate, 8, 2);
        }


    } else {

        unset($_SESSION['isNews']);

        $rows = checkForQueryError($manager, "usersDB.user_posts", $query);

        foreach ($rows as $value) {
            $count++;

            $_SESSION['data'][] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "user_photo" => $value->user_photo, "postDate" => $value->postDate, "user_id" => $value->user_id, "username" => "example", "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count);
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


    $monthdays = [];

    if ($_SESSION['isNews']) {
        $start = 5;
    } else {
        $start = 3;
    }

    foreach ($_SESSION['data'] as $v) {
        $monthdays[] = substr($v['postDate'], $start, 2);
    }

    for ($i = 0; $i < count($monthdays); $i++) {
        $oldValue = $monthdays[$i];
        $monthdays[$i] = date("F", mktime(0, 0, 0, $oldValue, 10));
    }

    if (isset($_SESSION['search_input']) && !empty($_SESSION['search_input'])) {
        $numPages = ceil($countForSearch / $perPage);
    } else {
        $numPages = ceil($countForAll / $perPage);
    }


    if (empty($_SESSION['allData'])) {

        $queryAll = new MongoDB\Driver\Query([]);

        $rowsAll = checkForQueryError($manager, "usersDB.user_posts", $queryAll);

        foreach ($rowsAll as $value) {
            $_SESSION['dataUsers'][] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => $value->postBody, "user_photo" => $value->user_photo, "postDate" => $value->postDate, "user_id" => $value->user_id, "username" => "example", "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count);
        }

        $queryForUsernames = new MongoDB\Driver\Query([]);

        $rowsForUsernames = checkForQueryError($manager, "usersDB.users", $queryForUsernames);

        $usernamesUsers = [];

        foreach ($rowsForUsernames as $username) {
            $usernamesUsers[] = array("id" => $username->_id, "username" => $username->name);
        }

        for ($i = 0; $i < count($_SESSION['dataUsers']); $i++) {
            for ($j = 0; $j < count($usernamesUsers); $j++) {
                if ($_SESSION['dataUsers'][$i]['user_id'] == $usernamesUsers[$j]['id']) {
                    $_SESSION['dataUsers'][$i]['username'] = $usernamesUsers[$j]['username'];
                }
            }
        }

        $_SESSION['allData'][] = $_SESSION['dataUsers'];

        $rows = checkForQueryError($manager, "usersDB.news_posts", new MongoDB\Driver\Query([]));

        foreach ($rows as $value) {
            $count++;
            $_SESSION['dataNews'][] = array("post_id" => $value->_id, "postTitle" => $value->postTitle, "postBody" => substr($value->postBody, 0, 250), "post_photo" => $value->post_photo, "postDate" => $value->postDate, "post_tags" => $value->post_tags, "post_comment_count" => $value->post_comment_count, "likes_count" => $value->likes_count); //"user_id"=>$value->user_id,  "username"=>"example",
        }

        $_SESSION['allData'][] = $_SESSION['dataNews'];
    }

    ?>

    <section class="blog_area padding_top">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mb-5 mb-lg-0">
                    <div class="blog_left_sidebar">
                        <?php
                        $i = 0;
                        $j = 0;
                        foreach ($_SESSION["data"] as $v) {

                            $v['post_id'] = strval($v['post_id']);


                            if (isset($_SESSION['isNews']) && !empty($_SESSION['isNews'])) {
                                $src = $v['post_photo'];
                                $videoFragment = "<img style='max-width: 100%;height: 35vh;image-rendering: optimizeQuality;' class='card-img rounded-0' src='{$src}' alt='each post image'>";
                            } else {
                                if (substr($v['user_photo'], 0, 5) == "https") {
                                    $src = $v['user_photo'];
                                } else {
                                    $src = "usersPhoto/{$v['user_photo']}";
                                }
                                $queryForVideo = new MongoDB\Driver\Query(["post_id" => $v['post_id']]);

                                $rowsForVideos = checkForQueryError($manager, "usersDB.user_videos", $queryForVideo);

                                $videoCount = 0;

                                foreach ($rowsForVideos as $video) {
                                    $videoname = $video->file_name;
                                    $videoCount++;
                                }

                                if ($videoCount == 0 || empty($videoname)) {
                                    $videoFragment = "<img style='max-width: 100%;height: 35vh;image-rendering: optimizeQuality;' class='card-img rounded-0' src='{$src}' alt='each post image'>";
                                } else {
                                    $videoFragment = "<video poster='{$src}' style='max-width: 100%;height: 45vh;' controls>
                                    <source src='videos/userVideos/{$videoname}' type='video/mp4'>
                                    <source src='videos/userVideos/{$videoname}' type='video/webm'>
                                    <source src='videos/userVideos/{$videoname}' type='video/ogg'>
                                </video>";
                                }

                                if ($videoname) {
                                    $style = 'padding-bottom:10vh';
                                } else {
                                    $style = '';
                                }
                                $videoname = "";
                                $videoCount = 0;
                            }


                            echo "<div class='blog_item_img' style={$style}>
                                {$videoFragment}
                                <a href=\"single-blog.php?post_id={$v['post_id']}&singleBlog=true\" class='blog_item_date'>
                                    <h3>{$postsDay[$i++]}</h3>
                                    <p>{$monthdays[$j++]}</p>
                                </a>
                            </div>
                            
                            <div class='blog_details'>
                                <a class='d-inline-block' href=\"single-blog.php?post_id={$v['post_id']}&singleBlog=true\">
                                    <h2>{$v['postTitle']}</h2>
                                </a>
                                <p>{$v['postBody']}</p> 
                            </div>
                        </article>";

                        }

                        ?>

                        <nav class="blog-pagination justify-content-center d-flex">
                            <ul class="pagination">
                                <?php

                                if (!empty($_GET['category'])) {
                                    $searchCategoryParam = "&category={$_GET['category']}";
                                }

                                if (!empty($_SESSION['search_input'])) {
                                    $searchCategoryParam .= "&search_input={$_SESSION['search_input']}";
                                }

                                if (isset($_GET['news'])) {
                                    $queryParam = "news=true";
                                } else {
                                    $queryParam = "unsetNews=true";
                                }

                                if (!($currentPage == 1) && !empty($currentPage)) {
                                    echo "<li class='page-item'><a href='blog.php?{$queryParam}&currentPage={$prev}{$searchCategoryParam}' class='page-link' aria-label='Previous'><i class='ti-angle-left'></i></a></li>";
                                }
                                ?>
                                <?php

                                $from = 1;


                                if (empty($_GET['category']) && ((empty($_GET['search_input']) && $numPages > 10) || (!empty($_GET['search_input']) && $numPages > 10))) {
                                    if ($currentPage > 10) {
                                        $from = $currentPage - 9;
                                        $to = $currentPage;
                                    } else {
                                        $to = 10;
                                    }

                                } else {
                                    $to = $numPages;
                                }

                                for ($i = $from; $i <= $to; $i++) {
                                    if ($currentPage == $i) {
                                        echo "<li class='page-item'><a href='blog.php?{$queryParam}&currentPage={$i}{$searchCategoryParam}' style='color: hotpink !important;' class='page-link'>{$i}</a></li>";
                                    } else {
                                        echo "<li class='page-item'><a href='blog.php?{$queryParam}&currentPage={$i}{$searchCategoryParam}' class='page-link'>{$i}</a></li>";
                                    }
                                }
                                ?>
                                <?php
                                if (!($currentPage == $numPages) && $numPages != 0) {
                                    echo "<li class='page-item'><a href='blog.php?{$queryParam}&currentPage={$next}{$searchCategoryParam}' class='page-link' aria-label='Next'><i class='ti-angle-right'></i></a></li>";
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="blog_right_sidebar">


                        <aside class="single_sidebar_widget popular_post_widget">
                            <h3 class="widget_title">Recent Posts</h3>
                            <?php
                            foreach ($_SESSION['recentPosts'] as $v) {

                                $v['post_id'] = strval($v['post_id']);

                                if (isset($_SESSION['isNews']) && !empty($_SESSION['isNews'])) {
                                    $src = $v['post_photo'];
                                    $photoAlt = "post photo";
                                } else {
                                    $photoAlt = "user photo";
                                    if (substr($v['user_photo'], 0, 5) == "https") {
                                        $src = $v['user_photo'];
                                    } else {
                                        $src = "usersPhoto/{$v['user_photo']}";
                                    }
                                }

                                echo "<div class='media post_item'>
                        <img width='150px' height='120px' src='{$src}' alt='{$photoAlt}'>
                        <div class='media-body'>
                        <a href='single-blog.php?post_id={$v['post_id']}&singleBlog=true'>
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

                                if (isset($_GET['news'])) {
                                    $queryParam = "news=true";
                                } else {
                                    $queryParam = "unsetNews=true";
                                }
                                ?>
                            </ul>
                        </aside>

                        <aside class="single_sidebar_widget instagram_feeds">
                            <h4>Posts categories</h4>
                            <ul class="list-group categoryList">
                                <?php
                                $categoryArr = ["business", "techcrunch", "wsj", "apple", "bitcoin"];

                                ?>
                                <li class="list-group-item fa fa-business-time"
                                "> <a href="<?= categoryNotEmpty('business', $manager, $queryParam) ?>"
                                      class="category_name">Business
                                    <a class="fas fa-minus-square float-right catDel category_delete"
                                       href=""></a></a></li>
                                <li class="list-group-item fas fa-microchip"><a class="category_name"
                                                                                href="<?= categoryNotEmpty('techcrunch', $manager, $queryParam) ?>">Techcrunch
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                                </a></li>
                                <li class="list-group-item fas fa-journal-whills"><a class="category_name"
                                                                                     href="<?= categoryNotEmpty('wsj', $manager, $queryParam) ?>">WSJ
                                        <a class="fas fa-minus-square float-right catDel category_delete" href=""></a>
                                    </a></li>
                                <li class="list-group-item fab fa-apple"><a class="category_name"
                                                                            href="<?= categoryNotEmpty('apple', $manager, $queryParam) ?>">Apple
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                                <li class="list-group-item fab fa-bitcoin"><a class="category_name"
                                                                              href="<?= categoryNotEmpty('bitcoin', $manager, $queryParam) ?>">Bitcoin
                                        <a class="fas fa-minus-square float-right catDel category_delete"
                                           href=""></a></a></li>
                            </ul>
                            <form action="addCategory.php" method="post" class="addCategoryForm">
                                <br><input class='btn btn-success sendCategory' type='submit' name='sendCategory'
                                           value='Add new category'/>
                            </form>
                        </aside>

                        <?php

                        if (isset($_GET['news'])) {
                            $emailPath = "blog.php?news=true";
                        } else if (isset($_GET['unsetNews'])) {
                            $emailPath = "blog.php?unsetNews=true";
                        }

                        ?>

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