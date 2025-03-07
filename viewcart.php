<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Controleer of de gebruiker is ingelogd en of de rol 'consument' of 'thuischef' is
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['consument', 'thuischef'])) {
    header("Location: login.php");
    exit;
}

// Verkrijg de shop_user_id van de geselecteerde shop
$shop_user_id = isset($_GET['shop_user_id']) ? intval($_GET['shop_user_id']) : 0;
$consumer_id = $_SESSION['user_id']; // De ID van de ingelogde consument

// Update de hoeveelheid of verwijder een item uit de winkelwagen
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_quantity'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $quantity = intval($_POST['quantity']);

        // Controleer de beschikbare voorraad
        $stmt = $Mysql->prepare("SELECT p.stock FROM cart_items ci JOIN producten p ON ci.product_id = p.product_id WHERE ci.id = ?");
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();
        $stmt->bind_result($stock);
        $stmt->fetch();
        $stmt->close();

        if ($quantity > $stock) {
            $message = "Niet genoeg voorraad beschikbaar!";
            $message_class = "error";
        } else {
            $stmt = $Mysql->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $cart_item_id);
            $stmt->execute();
            $stmt->close();
            $message = "Hoeveelheid bijgewerkt!";
            $message_class = "success";
        }
    } elseif (isset($_POST['delete_item'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $stmt = $Mysql->prepare("DELETE FROM cart_items WHERE id = ?");
        $stmt->bind_param("i", $cart_item_id);
        $stmt->execute();
        $stmt->close();
        $message = "Item verwijderd!";
        $message_class = "success";
    }
}

// Verkrijg de producten in het winkelwagentje voor de specifieke shop
$stmt = $Mysql->prepare("SELECT ci.id AS cart_item_id, p.name, ci.quantity, ci.price FROM cart_items ci JOIN producten p ON ci.product_id = p.product_id WHERE ci.cart_id = (SELECT id FROM cart WHERE user_id = ? AND shop_id = ?)");
$stmt->bind_param("ii", $consumer_id, $shop_user_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Winkelwagen</title>
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
        .cart {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px; /* Voeg wat ruimte toe boven het formulier voor de boodschap */
        }
        .cart-item {
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: left;
        }
        .cart-item h3 {
            margin: 0;
            color: #333;
        }
        .cart-item p {
            margin: 5px 0;
            color: #666;
        }
        .cart-item .price {
            color: #007BFF;
            font-weight: bold;
        }
        .cart-item .quantity {
            color: #28a745;
            font-weight: bold;
        }
        h2 {
            color: #fff;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            width: 80%;
            max-width: 800px;
        }
        .message.success {
            background-color: #28a745;
            color: white;
        }
        .message.error {
            background-color: #dc3545;
            color: white;
        }
        .back-button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="navbar-container">
    <?php include "header.php"; ?>
</div>

<div class="cart">
    <h2>Winkelwagen</h2>
    <?php if (isset($message)): ?>
        <div class="message <?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="cart-item">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p class="price">Prijs: €<?php echo htmlspecialchars($row['price']); ?></p>
                <p class="quantity">Aantal: <?php echo htmlspecialchars($row['quantity']); ?></p>
                <form action="viewcart.php?shop_user_id=<?php echo htmlspecialchars($shop_user_id); ?>" method="POST">
                    <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($row['cart_item_id']); ?>">
                    <input type="number" name="quantity" min="1" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>
                    <input type="submit" name="update_quantity" value="Bijwerken">
                    <input type="submit" name="delete_item" value="Verwijderen" class="delete-button">
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Je winkelwagen is leeg.</p>
    <?php endif; ?>
    <form action="shops.php" method="GET">
        <input type="submit" class="back-button" value="Terug naar Shops">
    </form>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>