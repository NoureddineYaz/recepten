<?php
include "databank.php";
session_start();

// Fetch all recipes from the database
$stmt = $Mysql->prepare("SELECT recipe_id, title, description, image_path FROM recepten");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Recipes</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/meanmenu.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        .single-latest-news {
            background: #fff;
            border: 1px solid #e1e1e1;
            margin-bottom: 30px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .single-latest-news:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .latest-news-bg {
            background-size: cover;
            background-position: center;
            height: 200px;
            margin-bottom: 20px;
        }

        .news-text-box h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .news-text-box .excerpt {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
        }

        .read-more-btn {
            font-size: 14px;
            color: #333;
            text-transform: uppercase;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .read-more-btn:hover {
            color: #ff6347;
        }
    </style>
</head>
<body>
<?php include "header.php"; ?>
    <!-- breadcrumb-section -->
    <div class="breadcrumb-section breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="breadcrumb-text">
                        <p>Discover our delicious recipes</p>
                        <h1>All Recipes</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end breadcrumb section -->

    <!-- recipes section -->
    <div class="recipes-section mt-150 mb-150">
        <div class="container">
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="single-latest-news">
                            <a href="single_recipe.php?id=<?php echo $row['recipe_id']; ?>">
                                <div class="latest-news-bg" style="background-image: url('<?php echo $row['image_path']; ?>');"></div>
                            </a>
                            <div class="news-text-box">
                                <h3><a href="single_recipe.php?id=<?php echo $row['recipe_id']; ?>"><?php echo $row['title']; ?></a></h3>
                                <p class="excerpt"><?php echo $row['description']; ?></p>
                                <a href="single_recipe.php?id=<?php echo $row['recipe_id']; ?>" class="read-more-btn">read more <i class="fas fa-angle-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <!-- end recipes section -->

    <!-- footer -->
    <div class="footer-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-box about-widget">
                        <h2 class="widget-title">About us</h2>
                        <p>Ut enim ad minim veniam perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-box get-in-touch">
                        <h2 class="widget-title">Get in Touch</h2>
                        <ul>
                            <li>34/8, East Hukupara, Gifirtok, Sadan.</li>
                            <li>support@fruitkha.com</li>
                            <li>+00 111 222 3333</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-box pages">
                        <h2 class="widget-title">Pages</h2>
                        <ul>
                            <li><a href="index.html">Home</a></li>
                            <li><a href="about.html">About</a></li>
                            <li><a href="services.html">Shop</a></li>
                            <li><a href="news.html">News</a></li>
                            <li><a href="contact.html">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-box subscribe">
                        <h2 class="widget-title">Subscribe</h2>
                        <p>Subscribe to our mailing list to get the latest updates.</p>
                        <form action="index.html">
                            <input type="email" placeholder="Email">
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end footer -->

    <!-- jquery -->
    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <!-- bootstrap -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!-- count down -->
    <script src="assets/js/jquery.countdown.js"></script>
    <!-- isotope -->
    <script src="assets/js/jquery.isotope-3.0.6.min.js"></script>
    <!-- waypoints -->
    <script src="assets/js/waypoints.js"></script>
    <!-- owl carousel -->
    <script src="assets/js/owl.carousel.min.js"></script>
    <!-- magnific popup -->
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <!-- mean menu -->
    <script src="assets/js/jquery.meanmenu.min.js"></script>
    <!-- sticker js -->
    <script src="assets/js/sticker.js"></script>
    <!-- main js -->
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
$stmt->close();
$Mysql->close();
?>