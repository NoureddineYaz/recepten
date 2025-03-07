<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld




// Controleer of de gebruiker is ingelogd en of de rol 'consument' is
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'consument' && $_SESSION['role'] !== 'thuischef')) {
    header("Location: login.php");
    exit;
}

// Verkrijg alle shops (gebruikers met de rol 'boer' of 'winkelier')
$stmt = $Mysql->prepare("SELECT user_id, username, role FROM gebruikers WHERE role IN ('boer', 'winkelier')");
$stmt->execute();
$result = $stmt->get_result();

// Sluit de statement
$stmt->close();

// Sluit de databaseverbinding
$Mysql->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shops</title>
    <link rel="stylesheet" href="assets/css/main.css">
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
        }
        .top-header-area {
            background: #07212e;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .main-menu-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .site-logo {
            margin-left: 0; /* Verplaats de logo naar links */
        }
        .shops {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px; /* Voeg wat ruimte toe boven het formulier voor de boodschap */
        }
        .shop {
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: left;
        }
        .shop h3 {
            margin: 0;
            color: #333;
        }
        .shop p {
            margin: 5px 0;
            color: #666;
        }
        .shop a {
            color: #007BFF;
            text-decoration: none;
        }
        .shop a:hover {
            text-decoration: underline;
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

<div class="shops">
    <h2>Alle Shops</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="shop">
                <h3><?php echo htmlspecialchars($row['username']); ?> (<?php echo htmlspecialchars($row['role']); ?>)</h3>
                <p><a href="viewshop.php?user_id=<?php echo htmlspecialchars($row['user_id']); ?>">Bekijk Shop</a></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Er zijn geen shops beschikbaar.</p>
    <?php endif; ?>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
