<?php
include "databank.php";
session_start();
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $user_id = $_SESSION['user_id'];

    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["recipe_image"]["name"], PATHINFO_EXTENSION));
    $unique_name = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_name;
    $uploadOk = 1;

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["recipe_image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
            $stmt = $Mysql->prepare("INSERT INTO recepten (user_id, title, description, ingredients, instructions, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("isssss", $user_id, $title, $description, $ingredients, $instructions, $target_file);
            $stmt->execute();
            $stmt->close();

            // Haal de ID van het nieuw toegevoegde recept op
            $stmt = $Mysql->prepare("SELECT recipe_id FROM recepten WHERE image_path = ?");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("s", $target_file);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $recipe_id = $row['recipe_id'];
            $stmt->close();

            // Haal de lijst van volgers op
            $stmt = $Mysql->prepare("SELECT g.email FROM volgen v JOIN gebruikers g ON v.follower_id = g.user_id WHERE v.followed_id = ?");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $naam="Select username from gebruikers where user_id=$user_id";
            $naamresult = $Mysql->query($naam);
            $naamrow = $naamresult->fetch_assoc();
            $naam = $naamrow['username'];
            // Stuur een e-mail naar elke volger
            while ($row = $result->fetch_assoc()) {
                $to = $row['email'];
                $subject = "New Recipe Uploaded";
                $message = "A new recipe titled '$title' has been uploaded by '$naam'. Check it out! <a href='http://localhost/noureddine/YAZ%20younes/recepten/single_recipe.php?id=$recipe_id'>Click here</a>";

                // Maak de PHPMailer instantie aan
                $mail = new PHPMailer(true);
                try {
                    // Stel de SMTP instellingen in
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'fruit.share.recipes@gmail.com';  // Vul je Gmail e-mailadres in
                    $mail->Password = 'sghzndgbvhnhldfp';  // Gebruik je app-wachtwoord hier
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;  // Poort voor Gmail SMTP

                    // Ontvanger en onderwerp van de e-mail
                    $mail->setFrom('fruit.share.recipes@gmail.com', 'Fruit Share Recipes');
                    $mail->addAddress($to);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    $mail->send();
                    echo "Email sent to: $to<br>";
                } catch (Exception $e) {
                    echo "Message could not be sent to $to. Mailer Error: {$mail->ErrorInfo}<br>";
                }
            }

            $stmt->close();
            $Mysql->close();

            echo "The file " . htmlspecialchars(basename($_FILES["recipe_image"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Recipe</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/favicon.png">
    <!-- google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <!-- fontawesome -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <!-- owl carousel -->
    <link rel="stylesheet" href="assets/css/owl.carousel.css">
    <!-- magnific popup -->
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <!-- animate css -->
    <link rel="stylesheet" href="assets/css/animate.css">
    <!-- mean menu css -->
    <link rel="stylesheet" href="assets/css/meanmenu.min.css">
    <!-- main style -->
    <link rel="stylesheet" href="assets/css/main.css">
    <!-- responsive -->
    <link rel="stylesheet" href="assets/css/responsive.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            overflow: hidden;
        }
        .form-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container form {
            position: absolute;
            width: 100%;
            max-width: 600px;
            top: 0;
            left: 0;
            margin: auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            color: #333;
        }
    </style>
</head>
<body>

    <!-- header -->
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
                                        <li><a href="index.html">Static Home</a></li>
                                        <li><a href="index_2.html">Slider Home</a></li>
                                    </ul>
                                </li>
                                <li><a href="about.html">About</a></li>
                                <li><a href="#">Pages</a>
                                    <ul class="sub-menu">
                                        <li><a href="404.html">404 page</a></li>
                                        <li><a href="about.html">About</a></li>
                                        <li><a href="cart.html">Cart</a></li>
                                        <li><a href="checkout.html">Check Out</a></li>
                                        <li><a href="contact.html">Contact</a></li>
                                        <li><a href="news.html">News</a></li>
                                        <li><a href="shop.html">Shop</a></li>
                                    </ul>
                                </li>
                                <li><a href="news.html">News</a>
                                    <ul class="sub-menu">
                                        <li><a href="news.html">News</a></li>
                                        <li><a href="single-news.html">Single News</a></li>
                                    </ul>
                                </li>
                                <li><a href="contact.html">Contact</a></li>
                                <li><a href="shop.html">Shop</a>
                                    <ul class="sub-menu">
                                        <li><a href="shop.html">Shop</a></li>
                                        <li><a href="checkout.html">Check Out</a></li>
                                        <li><a href="single-product.html">Single Product</a></li>
                                        <li><a href="cart.html">Cart</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="header-icons">
                                        <a class="shopping-cart" href="cart.html"><i class="fas fa-shopping-cart"></i></a>
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
    <!-- end header -->
     <style>
        
.form-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0.9;
}

.form-container form {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin: auto;
    padding: 30px;
    background:#07212e;
    opacity: 0.9;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.form-container h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    text-align: center;
}

.form-container p {
    margin-bottom: 15px;
}

.form-container input[type="text"],
.form-container textarea,
.form-container input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

.form-container input[type="submit"] {
    width: 100%;
    padding: 10px;
    background: #007BFF;
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.form-container input[type="submit"]:hover {
    background: #0056b3;
}

.header-icons a {
    color: #333;
    margin: 0 10px;
    font-size: 18px;
    transition: color 0.3s ease;
}
h2 {
    background-color:F28123 ;
}

.header-icons a:hover {
    color: #007BFF;
}</style>
    <div class="breadcrumb-section1 breadcrumb-bg1">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="breadcrumb-text">
                        <div class="form-container">
                            <form action="upload_recipe.php" method="post" enctype="multipart/form-data">
                                <h2>Upload Recipe</h2>
                                <p>
                                    <input class="" type="text" name="title" placeholder="Title" required>
                                </p>
                                <p>
                                    <textarea name="description" placeholder="Description" required></textarea>
                                </p>
                                <p>
                                    <textarea name="ingredients" placeholder="Ingredients" required></textarea>
                                </p>
                                <p>
                                    <textarea name="instructions" placeholder="Instructions" required></textarea>
                                </p>
                                <p>
                                    Select image to upload: <input type="file" name="recipe_image" id="recipe_image" required>
                                </p>
                                <p>
                                    <input type="submit" value="Upload Recipe" name="submit">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.meanmenu.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>