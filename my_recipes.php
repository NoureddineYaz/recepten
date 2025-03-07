<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Start de sessie
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Haal de recepten van de ingelogde gebruiker op
$stmt = $Mysql->prepare("SELECT recipe_id, title, description, image_path FROM recepten WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Sluit de databaseverbinding
$Mysql->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mijn Recepten</title>
    <link rel="stylesheet" href="assets/css/main.css">
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
    <style>
        body {
            height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            background-color: #051922;
            margin: 0;
        }
        .navbar-container {
            width: 100%;
            position: fixed;
            top: 0;
            background-color: #333;
            z-index: 1000;
        }
        .navbar {
            display: flex;
            justify-content: center; /* Center the links within the navbar */
            width: 100%; /* Make the navbar span the entire width */
        }
        .navbar a {
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .recipes {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 80px; /* Adjusted to account for the fixed navbar */
        }
        .recipe {
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: left;
        }
        .recipe h3 {
            margin: 0;
            color: #333;
        }
        .recipe p {
            margin: 5px 0;
            color: #666;
        }
        .recipe img {
            max-width: 100%;
            border-radius: 5px;
        }
        h2 {
            color: #fff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="navbar-container">
    <?php include "header.php"; ?>
</div>

<div class="recipes">
    <h2>Mijn Recepten</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="recipe">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <?php if ($row['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php endif; ?>
                <p><a href="single_recipe.php?id=<?php echo $row['recipe_id']; ?>" class="btn btn-primary">Bekijk Recept</a></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Je hebt nog geen recepten toegevoegd.</p>
    <?php endif; ?>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
