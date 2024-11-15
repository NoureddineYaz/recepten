<?php
include 'databank.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

// Variabelen voor berichten
$message = "";
$message_class = "";

// Verwerken van het "wachtwoord vergeten" formulier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Zoek de gebruiker op basis van het e-mailadres
    $stmt = $Mysql->prepare("SELECT * FROM gebruikers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Gebruiker gevonden, stuur resetlink per e-mail
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50));  // Genereer een willekeurige token
        $expires = time() + 1800;  // Token verloopt na 30 minuten

        // Update de gebruiker met de token en vervaldatum
        $update_stmt = $Mysql->prepare("UPDATE gebruikers SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update_stmt->bind_param("sis", $token, $expires, $email);
        $update_stmt->execute();

        // Stel de resetlink samen
        $reset_link = "http://localhost/noureddine/Groenten%20en%20recepten/reset_password.php?token=" . $token;

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
            $mail->setFrom('jouw-email@gmail.com', 'Jouw Naam');  // Je Gmail-adres en naam
            $mail->addAddress($email);  // Het e-mailadres van de ontvanger
            $mail->Subject = 'Wachtwoord Reset Verzoek';
            $mail->Body    = 'Hallo, klik op de volgende link om je wachtwoord te resetten: ' . $reset_link;

            // Verstuur de e-mail
            $mail->send();
            $message = 'Er is een resetlink naar je e-mail gestuurd. Controleer je inbox!';
            $message_class = 'success';
        } catch (Exception $e) {
            $message = "Er is iets misgegaan bij het verzenden van de e-mail. Mailer Error: {$mail->ErrorInfo}";
            $message_class = 'error';
        }
    } else {
        $message = "E-mailadres bestaat niet in ons systeem.";
        $message_class = 'error';
    }

    // Sluit de statement
    $stmt->close();
}

// Verwerken van het login formulier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Zoek de gebruiker in de database
    $stmt = $Mysql->prepare("SELECT * FROM gebruikers WHERE username = ?");
    $stmt->bind_param("s", $username); // Bind de parameter (gebruikersnaam)
    $stmt->execute();
    $result = $stmt->get_result();

    // Controleer of de gebruiker bestaat
    if ($result->num_rows > 0) {
        // Verkrijg de gebruikersgegevens
        $user = $result->fetch_assoc();
        // Controleer of het ingevoerde wachtwoord overeenkomt met het gehashte wachtwoord in de database
        if (password_verify($password, $user['hashed_password'])) {
            // Als het wachtwoord correct is, start een sessie en log de gebruiker in
            session_start();
            $_SESSION['user_id'] = $user['id']; // Sla de gebruiker-ID op in de sessie
            $_SESSION['username'] = $user['username']; // Sla de gebruikersnaam op in de sessie
            $_SESSION['role'] = $user['role']; // Sla de rol van de gebruiker op in de sessie
            header("Location: index1.php"); // Redirect naar de dashboardpagina
            exit;
        } else {
            $message = "Ongeldig wachtwoord!";
            $message_class = "error";
        }
    } else {
        $message = "Gebruiker niet gevonden!";
        $message_class = "error";
    }

    // Sluit de statement
    $stmt->close();
}

// Sluit de databaseverbinding
$Mysql->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
        .form-container {
            position: relative;
        }
        .form-container form {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
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
    <?php if ($message): ?>
        <div class="message <?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="breadcrumb-section1 breadcrumb-bg1">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="breadcrumb-text">
                        <div class="form-container">
                            <div class="contact-form">
                            <!-- Login Form -->
                            <form class="login-form" id="login-form" action="login.php" method="POST">
                                <style>
                                    h2 {
                                        color: aliceblue;
                                    }
                                </style>
                                <h2>Login</h2>
                                <p>
                                <input type="text" name="username" placeholder="Gebruikersnaam" required>
                                </p>
                                <p>
                                <input type="password" name="password" placeholder="Wachtwoord" required>
                                </p>
                                <br>
                                <input type="submit" value="Inloggen">
                                <br><br>
                                <p>Don't have an account? <a href="register.php">Sign up</a></p>
                                <p><a href="#" onclick="showForgotPasswordForm()">Wachtwoord vergeten?</a></p>
                            </form>

                            <!-- Wachtwoord vergeten formulier -->
                            <form class="forgot-password-form" id="forgot-password-form" action="login.php" method="POST" style="display: none;">
                                <h3>Wachtwoord vergeten</h3>
                                <input type="email" name="email" placeholder="Voer je e-mailadres in" required>
                                <input type="submit" value="Verstuur resetlink">
                                <br>
                                <p><a href="#" onclick="hideForgotPasswordForm()">Terug naar inloggen</a></p>
                            </form>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForgotPasswordForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('forgot-password-form').style.display = 'block';
        }

        function hideForgotPasswordForm() {
            document.getElementById('forgot-password-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
        }
    </script>

    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.meanmenu.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>