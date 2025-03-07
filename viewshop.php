<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Controleer of de gebruiker is ingelogd en of de rol 'consument' of 'thuischef' is
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['consument', 'thuischef'])) {
    header("Location: login.php");
    exit;
}

// Verkrijg de user_id van de geselecteerde shop
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$consumer_id = $_SESSION['user_id']; // De ID van de ingelogde consument

// Controleer of de shop_id bestaat in de gebruikers tabel
$stmt = $Mysql->prepare("SELECT user_id FROM gebruikers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Error: Shop ID does not exist.");
}
$stmt->close();

// Controleer of er een winkelwagentje bestaat voor deze consument en shop
$stmt = $Mysql->prepare("SELECT id FROM cart WHERE user_id = ? AND shop_id = ?");
$stmt->bind_param("ii", $consumer_id, $user_id);
$stmt->execute();
$stmt->bind_result($cart_id);
$stmt->fetch();
$stmt->close();

// Als er nog geen winkelwagentje is, maak er een aan
if (!$cart_id) {
    $stmt = $Mysql->prepare("INSERT INTO cart (user_id, shop_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $consumer_id, $user_id);
    $stmt->execute();
    $cart_id = $stmt->insert_id;
    $stmt->close();
}

// Product toevoegen aan het winkelwagentje
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Haal de productprijs op
    $stmt = $Mysql->prepare("SELECT price, stock FROM producten WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($price, $stock);
    $stmt->fetch();
    $stmt->close();

    if ($quantity > $stock) {
        echo "<p style='color: red;'>Niet genoeg voorraad beschikbaar!</p>";
    } else {
        // Controleer of het product al in de winkelwagen zit
        $stmt = $Mysql->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $cart_id, $product_id);
        $stmt->execute();
        $stmt->bind_result($cart_item_id, $existing_quantity);
        $stmt->fetch();
        $stmt->close();

        if ($cart_item_id) {
            // Update de hoeveelheid in het winkelwagentje
            $new_quantity = $existing_quantity + $quantity;
            $stmt = $Mysql->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_quantity, $cart_item_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Voeg het product toe aan het winkelwagentje
            $stmt = $Mysql->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $cart_id, $product_id, $quantity, $price);
            $stmt->execute();
            $stmt->close();
        }
        echo "<p style='color: green;'>Product toegevoegd aan winkelwagen!</p>";
    }
}

// Verkrijg de producten van de geselecteerde shop
$stmt = $Mysql->prepare("SELECT product_id, name, description, price, stock FROM producten WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Producten</title>
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
        .shop {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px; /* Voeg wat ruimte toe boven het formulier voor de boodschap */
        }
        .product {
            background: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: left;
        }
        .product h3 {
            margin: 0;
            color: #333;
        }
        .product p {
            margin: 5px 0;
            color: #666;
        }
        .product .price {
            color: #007BFF;
            font-weight: bold;
        }
        .product .stock {
            color: #28a745;
            font-weight: bold;
        }
        h2 {
            color: #fff;
            margin-bottom: 20px;
        }
        input[type="number"] {
            width: 100px;
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
        .view-cart {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .view-cart:hover {
            background-color: #218838;
        }
        .chatbox {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .chat-messages {
            height: 250px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .chat-message {
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 5px;
            max-width: 70%;
        }
        .chat-sent {
            background: #28a745;
            color: white;
            text-align: right;
            margin-left: auto;
        }
        .chat-received {
            background: #f1f1f1;
            color: #333;
            text-align: left;
            margin-right: auto;
        }
        .chat-message span {
            display: block;
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        #chat-form textarea {
            width: calc(100% - 50px);
            height: 40px;
            border-radius: 5px;
            padding: 5px;
            resize: none;
            border: 1px solid #ddd;
        }
        #chat-form button {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            border: none;
            background: #007BFF;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="navbar-container">
    <?php include "header.php"; ?>
</div>

<div class="shop">
    <h2>Shop Producten</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p class="price">Prijs: €<?php echo htmlspecialchars($row['price']); ?></p>
                <p class="stock">Voorraad: <?php echo htmlspecialchars($row['stock']); ?></p>
                <form action="viewshop.php?user_id=<?php echo htmlspecialchars($user_id); ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                    <input type="hidden" name="shop_user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <input type="number" name="quantity" min="1" max="<?php echo htmlspecialchars($row['stock']); ?>" value="1" required>
                    <input type="submit" value="Toevoegen aan winkelwagen">
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Er zijn geen producten beschikbaar in deze shop.</p>
    <?php endif; ?>
    <form action="viewcart.php" method="GET">
        <input type="hidden" name="shop_user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <input type="submit" class="view-cart" value="Bekijk Winkelwagen">
    </form>
</div>

<div class="chatbox">
    <h3>Chat met de verkoper</h3>
    <div class="chat-messages" id="chat-messages">
        <!-- Berichten worden hier geladen -->
    </div>

    <form id="chat-form">
        <input type="hidden" name="seller_id" value="<?php echo $user_id; ?>">
        <input type="hidden" name="consumer_id" value="<?php echo $consumer_id; ?>">
        <textarea name="chat_message" id="chat_message" placeholder="Typ een bericht..." required></textarea>
        <button type="submit">Verstuur</button>
    </form>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
document.getElementById("chat-form").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    
    fetch("send_message.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("chat_message").value = "";
            loadMessages();
        } else {
            console.error('Fout bij versturen:', data.error);
        }
    })
    .catch(error => console.error('Fout:', error));
});

function loadMessages() {
    let sellerId = <?php echo htmlspecialchars($user_id); ?>;
    let consumerId = <?php echo htmlspecialchars($consumer_id); ?>;
    
    fetch("get_messages.php?seller_id=" + sellerId + "&consumer_id=" + consumerId)
        .then(response => response.json())
        .then(data => {
            let chatMessages = document.getElementById("chat-messages");
            chatMessages.innerHTML = "";

            if (data.error) {
                chatMessages.innerHTML = `<p style="color: red;">${data.error}</p>`;
                return;
            }

            data.forEach(msg => {
                let div = document.createElement("div");
                
                // Bepaal de CSS-klasse op basis van de rol (sent_by)
                if (msg.sent_by === 'consumer') {
                    div.classList.add("chat-message", "chat-sent");
                } else {
                    div.classList.add("chat-message", "chat-received");
                }

                // Voeg de inhoud van het bericht toe
                div.innerHTML = `<p>${msg.message}</p><span>${msg.time}</span>`;
                chatMessages.appendChild(div);
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(error => console.error('Fout:', error));
}

// Laad berichten elke 3 seconden
setInterval(loadMessages, 3000);
loadMessages();
</script>
</body>
</html>
