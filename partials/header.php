<?php

session_start();

//require_once ("../functions.php");


if (isset($_POST['search_input'])) {

    if (isset($_SESSION['isNews'])) {
        $url = "../blog.php?news=true&search_input={$_POST['search_input']}";
    } else {
        $url = "../blog.php?search_input={$_POST['search_input']}";
    }

    header("location: {$url}");
}

?>


<!doctype html>
<html lang="zxx">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Polaris</title>
    <link rel="icon" href="../img/favicon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- animate CSS -->
    <link rel="stylesheet" href="../css/animate.css">
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="../css/owl.carousel.min.css">
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="../css/all.css">
    <!-- flaticon CSS -->
    <link rel="stylesheet" href="../css/flaticon.css">
    <link rel="stylesheet" href="../css/themify-icons.css">
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="../css/magnific-popup.css">
    <!-- swiper CSS -->
    <link rel="stylesheet" href="../css/slick.css">
    <!-- style CSS -->
    <link rel="stylesheet" href="../css/style.css">

    <script
            src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"></script>
    <script>

        let categories = {
            "fa-business-time": "business",
            "fa-microchip": "techcrunch",
            "fa-journal-whills": "wsj",
            "fa-apple": "apple",
            "fa-bitcoin": "bitcoin"
        }
        let domElems = {}


        function removeDOMel() {

            for (const [key, value] of Object.entries(categories)) {
                if (localStorage.getItem(key)) {
                    $(`.${key}`).remove()
                }
            }
        }


        async function asyncResult() {
            await removeDOMel()
        }

        function asyncAdd() {


            for (const [key, value] of Object.entries(categories)) {
                domElems[key] = $(`.${key}`).prop("outerHTML")
            }
            if (localStorage.length > 0) {
                for (const [key, value] of Object.entries(categories)) {
                    if (localStorage.getItem(key)) {
                        let nameValue = ` name = ${key} value=${value}`
                        let divClass = "div-" + key
                        $(`<div class=${divClass}><br> <label class=\`label-${key}\`>${value} <input type='checkbox'` + nameValue + "/> " + "</label><br><br></div>").insertBefore(".sendCategory")
                    }
                }
            }

        }

        async function asyncAddResult() {
            await asyncAdd()
        }

        function getBookmarks(del, post_id) {
            let sessionData = <?php echo json_encode($_SESSION['oldAllData']); ?>;
            let postsInfo = {}
            for (const [key, value] of Object.entries(sessionData[0])) {
                for (let i = parseInt(localStorage.getItem("bookMarkIndex")); i >= 0; i--) {
                    if (localStorage.getItem(`bookmark-${i}`) === value['post_id']['$oid']) {
                        if (value['post_photo']) {
                            postsInfo[value['post_id']['$oid']] = {
                                "postTitle": value['postTitle'].substr(0, 25) + "...",
                                "post_photo": value['post_photo'],
                                "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&news=true",
                                "post_id": value['post_id']['$oid']
                            }

                        } else {
                            if (value['user_photo'].substr(0, 5) === "https") {
                                postsInfo[value['post_id']['$oid']] = {
                                    "postTitle": value['postTitle'].substr(0, 25) + "...",
                                    "user_photo": value['user_photo'],
                                    "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&unsetNews=true",
                                    "post_id": value['post_id']['$oid']
                                }

                            } else {
                                postsInfo[value['post_id']['$oid']] = {
                                    "postTitle": value['postTitle'].substr(0, 25) + "...",
                                    "user_photo": "usersPhoto/" + value['user_photo'],
                                    "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&unsetNews=true",
                                    "post_id": value['post_id']['$oid']
                                }

                            }

                        }
                    }

                }

            }


            for (const [key, value] of Object.entries(sessionData[1])) {

                for (let i = parseInt(localStorage.getItem("bookMarkIndex")); i >= 0; i--) {
                    if (localStorage.getItem(`bookmark-${i}`) === value['post_id']['$oid']) {
                        if (value['post_photo']) {
                            postsInfo[value['post_id']['$oid']] = {
                                "postTitle": value['postTitle'].substr(0, 25) + "...",
                                "post_photo": value['post_photo'],
                                "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&news=true",
                                "post_id": value['post_id']['$oid']
                            }

                        } else {
                            if (value['user_photo'].substr(0, 5) === "https") {
                                postsInfo[value['post_id']['$oid']] = {
                                    "postTitle": value['postTitle'].substr(0, 25) + "...",
                                    "user_photo": value['user_photo'],
                                    "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&unsetNews=true",
                                    "post_id": value['post_id']['$oid']
                                }

                            } else {
                                postsInfo[value['post_id']['$oid']] = {
                                    "postTitle": value['postTitle'].substr(0, 25) + "...",
                                    "user_photo": "usersPhoto/" + value['user_photo'],
                                    "url_link": localStorage.getItem(`bookMarkLink-${i}`) + "&unsetNews=true",
                                    "post_id": value['post_id']['$oid']
                                }

                            }

                        }

                    }

                }

            }

            console.log(postsInfo)


            for (const [key, value] of Object.entries(postsInfo)) {
                if (value['user_photo']) {
                    if (document.querySelector(`.p-1.post-id-${value['post_id']}`)) {
                        if (del && value['post_id'] === post_id) {
                            $(`.p-1.post-id-${value['post_id']}`).remove()
                        }
                    } else {
                        document.querySelector(".bookmarkDiv").insertAdjacentHTML("afterbegin", `<div class="p-1 post-id-${value['post_id']}"><a href="${value['url_link']}"><img width='40px' height='25px' src='${value['user_photo']}' alt=user_photo></a> <a class="text-white" href="${value['url_link']}">${value['postTitle']}</a></div>`)
                    }
                } else {
                    if (document.querySelector(`.p-1.post-id-${value['post_id']}`)) {
                        if (del && value['post_id'] === post_id) {
                            $(`.p-1.post-id-${value['post_id']}`).remove()
                        }
                    } else {
                        document.querySelector(".bookmarkDiv").insertAdjacentHTML("afterbegin", `<div class="p-1 post-id-${value['post_id']}"><a href="${value['url_link']}"><img width='40px' height='25px' src='${value['post_photo']}' alt=post_photo></a> <a class="text-white" href="${value['url_link']}">${value['postTitle']}</a></div>`)
                    }
                }
            }
        }

        async function showBookmarks(del = false, post_id = 0) {

            await getBookmarks(del, post_id)

        }

        function addCatStyles() {
            $(".category_name").mouseover(function () {
                $(this).css("font-size", "20px")

                $(this).mouseout(function () {
                    $(this).css("font-size", "15px")
                })
            })

            $(".category_delete").mouseover(function () {
                $(this).css("background-color", "red")

                $(this).mouseout(function () {
                    $(this).css("background-color", "white")
                })
            })
        }

        function delCategory() {
            for (const [key, value] of Object.entries(categories)) {
                if (!(localStorage.getItem(key)) && $(event.target.parentElement).hasClass(key)) {
                    let nameValue = ` name = ${key} value=${value}`
                    let divClass = "div-" + key
                    $(`<div class=${divClass}><br> <label class=\`label-${key}\`>${value} <input type='checkbox'` + nameValue + "/> " + "</label><br><br></div>").insertBefore(".sendCategory")
                }
            }
            for (const [key, value] of Object.entries(categories)) {
                if ($(event.target.parentElement).hasClass(key)) {
                    localStorage.setItem(key, value)
                }
            }
            return false;
        }

        $(document).ready(function () {

            showBookmarks();

            addCatStyles();


            $(".addBookmark").click(function (event) {
                event.preventDefault()

                let searchParams = new URLSearchParams(window.location.search)
                let post_id = searchParams.get("post_id")

                if (!localStorage.getItem("bookMarkIndex")) {
                    localStorage.setItem("bookMarkIndex", "0")
                }

                if (!localStorage.getItem("maxBookmarks")) {
                    localStorage.setItem("maxBookmarks", "3")
                }

                let i = parseInt(localStorage.getItem("bookMarkIndex"));
                let foundCount = 0
                let notYet = false
                let deleteIndex = 0


                if (i > 0) {
                    for (i; i >= 0; i--) {

                        if (localStorage.getItem(`bookmark-${i}`)) foundCount++;

                        if (localStorage.getItem(`bookmark-${i}`) && localStorage.getItem(`bookmark-${i}`) === post_id) {
                            deleteIndex = i
                        }

                    }

                } else {
                    notYet = true
                }

                if (deleteIndex === 0) notYet = true;

                if (notYet) {
                    if (foundCount < parseInt(localStorage.getItem("maxBookmarks"))) {
                        let newIndex = parseInt(localStorage.getItem("bookMarkIndex")) + 1
                        localStorage.setItem(`bookmark-${newIndex}`, post_id)
                        localStorage.setItem("bookMarkIndex", newIndex.toString())
                        localStorage.setItem(`bookMarkLink-${newIndex}`, window.location.href)
                    }
                    showBookmarks()
                } else {
                    showBookmarks(true, post_id);
                    localStorage.removeItem(`bookmark-${deleteIndex}`)
                    localStorage.removeItem(`bookMarkLink-${deleteIndex}`)
                }

                showBookmarks();
            })
            asyncAddResult()

            asyncResult()

            $(".catDel").click(async function (event) {
                event.preventDefault()
                $(event.target.parentElement).remove()
                await delCategory()
            })

            $(".sendCategory").click(function (event) {
                event.preventDefault()
                // 1 - if in localStorage then add HTML and remove from localStorage
                for (const [key, value] of Object.entries(categories)) {
                    if (localStorage.getItem(key) && document.querySelector(`input[name=${key}]`).checked) {
                        document.querySelector(`.div-${key}`).remove()
                        document.querySelector(".categoryList").insertAdjacentHTML("afterbegin", domElems[key])
                        localStorage.removeItem(key)
                    }
                }
                addCatStyles()
            })
        })

    </script>

</head>

<body>
<!--::header part start::-->
<header class="main_menu home_menu">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <a class="navbar-brand" href="../index.php"> <img class="brand_logo" src="../img/newLogo.png"
                                                                      alt="logo" style="height: 100px!important;"> </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="menu_icon"><i class="fas fa-bars"></i></span>
                    </button>

                    <div class="collapse navbar-collapse main-menu-item" id="navbarSupportedContent">
                        <ul class="navbar-nav">

                            <?php
                            if (isset($_SESSION['_id'], $_SESSION['name']) && !empty($_SESSION['_id']) && !empty($_SESSION['name'])) {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../index.php">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../blog.php?news=true">News</a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="../blog.php" id="navbarDropdown_2"
                                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    blog
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown_2">
                                    <?php
                                        $manager = new MongoDB\Driver\Manager(getEnvVars(true)[0]);

                                        try{
                                            $rows = $manager->executeQuery("usersDB.user_posts", new MongoDB\Driver\Query(["user_id"=>$_SESSION['_id']]));
                                        } catch (\MongoDB\Driver\Exception\Exception $e){
                                            die("Error: " . $e->getMessage());
                                        }

                                        $rowCounter = 0;
                                        foreach ($rows as $row)$rowCounter++;

                                        if ($rowCounter >  0) echo "<a class=\"dropdown-item\" href=\"../single-blog.php?unsetNews=true\">Your blog</a>";
                                    ?>

                                    <a class="dropdown-item" href="../blog.php?unsetNews=true">All users blog</a>
                                    <a class="dropdown-item" href="../add/createPost.php">Create new post</a>
                                </div>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="../comments_contact/contact.php">Contact</a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="../blog.php" id="navbarDropdown_3"
                                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    bookmarks
                                </a>

                                <div class="dropdown-menu bookmarkDiv" aria-labelledby="navbarDropdown_3">

                                </div>
                            </li>


                            <li class="nav-item">
                                <a class="nav-link" href="../registration_login/exit.php">Exit</a>
                            </li>

                        </ul>
                    </div>

                    <div class="hearer_icon d-flex">
                        <a id="search_1"
                           onclick="submitForm === true ? document.querySelector('#search_form').submit() : submitForm = true"><i
                                    class="ti-search"></i></a>
                    </div>
                </nav>
                <?php } ?>


            </div>
        </div>
    </div>


    <div class="search_input" id="search_input_box">
        <div class="container ">
            <form action="" method="post" id="search_form"
                  class="d-flex justify-content-between search-inner">
                <input type="text" class="form-control" id="search_input" placeholder="Search Here" name="search_input">
                <span onclick="submitForm = false;" class="ti-close" id="close_search" title="Close Search"></span>
            </form>
        </div>
    </div>
</header>