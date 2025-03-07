<?php
include "databank.php";

// Controleer of de 'id' parameter is ingesteld
if (!isset($_GET['id'])) {
    die("Error: Recipe ID not provided.");
}

$recipe_id = $_GET['id'];

// Fetch the specific recipe from the database
$stmt = $Mysql->prepare("SELECT title, description, image_path, instructions, user_id FROM recepten WHERE recipe_id = ?");
if ($stmt === false) {
    die("Error: Failed to prepare the SQL statement.");
}

$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Recipe not found.");
}

$recipe = $result->fetch_assoc();
$author_id = $recipe['user_id'];
$stmt->close(); // Close the statement after fetching the recipe

// Fetch the author's username from the database
$stmt = $Mysql->prepare("SELECT username FROM gebruikers WHERE user_id = ?");
if ($stmt === false) {
    die("Error: Failed to prepare the SQL statement.");
}

$stmt->bind_param("i", $author_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Author not found.");
}

$author = $result->fetch_assoc();
$author_username = $author['username'];
$stmt->close(); // Close the statement after fetching the author

// Controleer of de gebruiker is ingelogd
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Verwerk de volgactie als het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($current_user_id && $author_id != $current_user_id) {
        if ($action == 'follow') {
            // Voeg de volgactie toe aan de database
            $stmt = $Mysql->prepare("INSERT INTO volgen (follower_id, followed_id, created_at) VALUES (?, ?, NOW())");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("ii", $current_user_id, $author_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action == 'unfollow') {
            // Verwijder de volgactie uit de database
            $stmt = $Mysql->prepare("DELETE FROM volgen WHERE follower_id = ? AND followed_id = ?");
            if ($stmt === false) {
                die("Error: Failed to prepare the SQL statement.");
            }
            $stmt->bind_param("ii", $current_user_id, $author_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    // Redirect to avoid form resubmission
    header("Location: single_recipe.php?id=$recipe_id");
    exit;
}

// Controleer of de huidige gebruiker de auteur volgt
$is_following = false;
if ($current_user_id) {
    $stmt = $Mysql->prepare("SELECT * FROM volgen WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $current_user_id, $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_following = $result->num_rows > 0;
    $stmt->close();
}

// Verwerk het reviewformulier als het is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['comment'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Voeg de review toe aan de database
    $stmt = $Mysql->prepare("INSERT INTO feedback (user_id, recipe_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt === false) {
        die("Error: Failed to prepare the SQL statement.");
    }
    $stmt->bind_param("iiis", $current_user_id, $recipe_id, $rating, $comment);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: single_recipe.php?id=$recipe_id");
    exit;
}

// Verwerk het verwijderformulier als het is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = $_POST['delete_comment_id'];

    // Verwijder de review uit de database
    $stmt = $Mysql->prepare("DELETE FROM feedback WHERE feedback_id = ? AND recipe_id = ?");
    if ($stmt === false) {
        die("Error: Failed to prepare the SQL statement.");
    }
    $stmt->bind_param("ii", $delete_comment_id, $recipe_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: single_recipe.php?id=$recipe_id");
    exit;
}

// Haal de reviews voor het recept op
$stmt = $Mysql->prepare("SELECT f.feedback_id, f.rating, f.comment, f.created_at, g.username FROM feedback f JOIN gebruikers g ON f.user_id = g.user_id WHERE f.recipe_id = ? ORDER BY f.created_at DESC");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($recipe['title']); ?></title>
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
</head>
<body>
    <?php include "header.php"; ?>
    <!-- end header -->

    <!-- breadcrumb-section -->
    <div class="breadcrumb-section breadcrumb-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="breadcrumb-text">
                        <p>Discover our delicious recipes</p>
                        <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end breadcrumb section -->

    <!-- recipe details section -->
    <div class="recipe-details-section mt-150 mb-150">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="single-recipe-details">
                        <div class="recipe-text-box">
                            <p>Posted by: <?php echo htmlspecialchars($author_username); ?></p>
                            <?php if ($current_user_id && $author_id != $current_user_id): ?>
                                <form action="single_recipe.php?id=<?php echo $recipe_id; ?>" method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="<?php echo $is_following ? 'unfollow' : 'follow'; ?>">
                                    <button type="submit" class="btn <?php echo $is_following ? 'btn-danger' : 'btn-primary'; ?>">
                                        <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="recipe-image">
                            <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="img-fluid">
                        </div>
                        <div class="recipe-text-box">
                            <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                            <h3>Instructions</h3>
                            <p><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></p>
                            <button onclick="copyURL()" class="btn btn-primary mt-3">Copy URL</button>
                        </div>

                        <!-- Review Form -->
                        <?php if ($current_user_id): ?>
                            <div class="review-form mt-5">
                                <h3>Leave a Review</h3>
                                <form action="single_recipe.php?id=<?php echo $recipe_id; ?>" method="post">
                                    <div class="form-group">
                                        <label for="rating">Rating:</label>
                                        <select name="rating" id="rating" class="form-control" required>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Comment:</label>
                                        <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Display Reviews -->
                        <div class="reviews mt-5">
                            <h3>Reviews</h3>
                            <?php if ($reviews_result->num_rows > 0): ?>
                                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                    <div class="review">
                                        <p><strong><?php echo htmlspecialchars($review['username']); ?></strong> (<?php echo htmlspecialchars($review['rating']); ?>/5)</p>
                                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        <p><small><?php echo htmlspecialchars($review['created_at']); ?></small></p>
                                        <?php if ($current_user_id == $author_id): ?>
                                            <form action="single_recipe.php?id=<?php echo $recipe_id; ?>" method="post" style="display:inline;">
                                                <input type="hidden" name="delete_comment_id" value="<?php echo $review['feedback_id']; ?>">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No reviews yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end recipe details section -->

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
                <div class="col-lg-3 col-md=6">
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
    <script>
        function copyURL() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copied to clipboard');
            }).catch(err => {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script>
</body>
</html>
<?php
$Mysql->close();
?>