<!-- header -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ...existing code... -->
</head>
<body>
    <header>
        <div class="top-header-area" id="sticker">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-sm-12 text-center">
                        <div class="main-menu-wrap">
                            <!-- logo -->
                            <div class="site-logo">
                                <a href="index.html">
                                    <img src="assets/img/logo.png" alt="">
                                </a>
                            </div>
                            <!-- logo -->

                            <!-- menu start -->
                            <nav class="main-menu">
                                <ul>
                                    <li class="current-list-item"><a href="#">Home</a>
                                        <ul class="sub-menu">
                                            <li><a href="all_recipes.php">Recipes</a></li>
                                            <li><a href="upload_recipe.php">Upload Recipe</a></li>
                                            <?php 
                                    if (isset($_SESSION['user_id'])) {
                                        if ($_SESSION['role'] === 'boer' || $_SESSION['role'] === 'winkelier') {
                                            echo '<li><a href="myshop.php">My Shop</a></li>';
                                            echo '<li><a href="shop2.php">Voeg product toe</a></li>';
                                        }
                                        if ($_SESSION['role'] === 'consument' || $_SESSION['role'] === 'thuischef') {
                                            echo '<li><a href="shops.php">Shops</a></li>';
                                        }}
                                        ?>
                                        </ul>
                                    </li>
                                    <li><a href="about.php">About</a></li>
                                    <li><a href="#">Pages</a>
                                        <ul class="sub-menu">
                                            <li><a href="404.php">404 page</a></li>
                                            <li><a href="about.php">About</a></li>
                                            <li><a href="checkout.php">Check Out</a></li>
                                            <li><a href="contact.php">Contact</a></li>
                                            <li><a href="news.php">News</a></li>
                                            <li><a href="shop.php">Shop</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="news.php">News</a>
                                        <ul class="sub-menu">
                                            <li><a href="news.php">News</a></li>
                                            <li><a href="single-news.php">Single News</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="contact.php">Contact</a></li>
                                    <li><a href="shop.php">Shop</a>
                                        <ul class="sub-menu">
                                            <li><a href="shop.php">Shop</a></li>
                                            <li><a href="checkout.php">Check Out</a></li>
                                            <li><a href="single-product.php">Single Product</a></li>
                                        </ul>
                                    </li>
                                    <?php 
                                    if (isset($_SESSION['user_id'])) {
                                      echo '<li><a href="logout.php">Logout</a></li>';
                                    } else {
                                        echo '<li><a href="login.php">Login</a></li>';
                                    }
                                    ?>
                                    <li>
                                        <div class="header-icons">
                                            <a class="shopping-cart" href="#"><i class="fas fa-shopping-cart"></i></a>
                                            <a class="mobile-hide search-bar-icon" href="#"><i class="fas fa-search"></i></a>
                                        </div>
                                    </li>
                                </ul>
                            </nav>
                            <a class="mobile-show search-bar-icon" href="#"><i class="fas fa-search"></i></a>
                            <div class="mobile-menu"></div>
                            <!-- menu end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</body>
</html>