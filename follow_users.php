<?php
include "databank.php";


// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to follow users.");
}

$current_user_id = $_SESSION['user_id'];

// Verwerk de volgactie als het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['followed_id'])) {
    $followed_id = $_POST['followed_id'];
    $action = $_POST['action'];

    if ($followed_id == $current_user_id) {
        $message = "Error: You cannot follow/unfollow yourself.";
    } else {
        if ($action == 'follow') {
            // Voeg de volgactie toe aan de database
            $stmt = $Mysql->prepare("INSERT INTO volgen (follower_id, followed_id, created_at) VALUES (?, ?, NOW())");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("ii", $current_user_id, $followed_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "You are now following this user.";
            } else {
                $message = "Error: Could not follow the user.";
            }
            $stmt->close();
        } elseif ($action == 'unfollow') {
            // Verwijder de volgactie uit de database
            $stmt = $Mysql->prepare("DELETE FROM volgen WHERE follower_id = ? AND followed_id = ?");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("ii", $current_user_id, $followed_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "You have unfollowed this user.";
            } else {
                $message = "Error: Could not unfollow the user.";
            }
            $stmt->close();
        }
    }
}

// Haal alle gebruikers op behalve de huidige gebruiker
$stmt = $Mysql->prepare("SELECT user_id, username, email FROM gebruikers WHERE user_id != ?");
if ($stmt === false) {
    die("Error: Failed to prepare the SQL statement.");
}

$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();

// Haal de lijst van gebruikers die de huidige gebruiker volgt
$stmt = $Mysql->prepare("SELECT followed_id FROM volgen WHERE follower_id = ?");
if ($stmt === false) {
    die("Error: Failed to prepare the SQL statement.");
}

$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$following = [];
while ($row = $result->fetch_assoc()) {
    $following[] = $row['followed_id'];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Follow Users</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
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
                                <li class="current-list-item"><a href="#">Home</a></li>
                                <li><a href="about.html">About</a></li>
                                <li><a href="contact.html">Contact</a></li>
                                <li><a href="shop.html">Shop</a></li>
                            </ul>
                        </nav>
                        <div class="mobile-menu"></div>
                        <!-- menu end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end header -->

    <!-- follow users section -->
     <br>
     <br>
     <br>
    <div class="follow-users-section mt-150 mb-150">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h2>Follow Users</h2>
                    <?php if (isset($message)): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <ul class="list-group">
                        <?php foreach ($users as $user): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                <form action="follow_users.php" method="post" style="display:inline;">
                                    <input type="hidden" name="followed_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo in_array($user['user_id'], $following) ? 'unfollow' : 'follow'; ?>">
                                    <button type="submit" class="btn <?php echo in_array($user['user_id'], $following) ? 'btn-danger' : 'btn-primary'; ?>">
                                        <?php echo in_array($user['user_id'], $following) ? 'Unfollow' : 'Follow'; ?>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
                        </div>
    </div>
    <!-- end follow users section -->

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
    <!-- main js -->
    <script src="assets/js/main.js"></script>
</body>
</html>