<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Start de sessie
session_start();

// Controleer of de gebruiker is ingelogd en of de rol 'boer' of 'winkelier' is
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['boer', 'winkelier'])) {
    header("Location: login.php");
    exit;
}

// Variabelen voor berichten
$message = "";
$message_class = "";

// Controleer of het formulier is verzonden om een product bij te werken
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'], $_POST['product_price'], $_POST['product_stock'])) {
    $product_id = $_POST['product_id'];
    $product_price = $_POST['product_price'];
    $product_stock = $_POST['product_stock'];

    // Werk het product bij in de database
    $stmt = $Mysql->prepare("UPDATE producten SET price = ?, stock = ? WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("diii", $product_price, $product_stock, $product_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $message = "Product succesvol bijgewerkt!";
        $message_class = "success";
    } else {
        $message = "Er is een fout opgetreden tijdens het bijwerken van het product. Probeer het opnieuw.";
        $message_class = "error";
    }

    // Sluit de statement
    $stmt->close();
}

// Verkrijg de producten van de ingelogde gebruiker
$stmt = $Mysql->prepare("SELECT product_id, name, description, price, stock FROM producten WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Verkrijg de klanten die berichten hebben gestuurd
$customers_stmt = $Mysql->prepare("SELECT DISTINCT m.consumer_id, g.username AS consumer_name 
                                   FROM messages m 
                                   JOIN gebruikers g ON m.consumer_id = g.user_id 
                                   WHERE m.seller_id = ?");
$customers_stmt->bind_param("i", $_SESSION['user_id']);
$customers_stmt->execute();
$customers_result = $customers_stmt->get_result();

// Sluit de databaseverbinding
$Mysql->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mijn Shop</title>
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
        .header-container {
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            margin-left: 60%;
        }
        .shop {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px;
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
        .customer-list {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .customer-list ul {
            list-style: none;
            padding: 0;
        }
        .customer-list li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }
        .customer-list li:hover {
            background: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="header-container">
    <?php include "header.php"; ?>
</div>

    <!-- Toon het bericht bovenaan de pagina -->
    <?php if ($message): ?>
        <div class="message <?php echo $message_class; ?>" id="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="shop">
        <h2>Mijn Shop</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <form action="myshop.php" method="POST">
                        <p class="price">Prijs: €<input type="number" step="0.01" name="product_price" value="<?php echo htmlspecialchars($row['price']); ?>" required></p>
                        <p class="stock">Voorraad: <input type="number" name="product_stock" value="<?php echo htmlspecialchars($row['stock']); ?>" required></p>
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                        <input type="submit" value="Bijwerken">
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Je hebt nog geen producten toegevoegd.</p>
        <?php endif; ?>
    </div>

    <div class="customer-list">
        <h3>Klanten</h3>
        <ul>
            <?php while ($customer_row = $customers_result->fetch_assoc()): ?>
                <li onclick="loadMessages(<?php echo $customer_row['consumer_id']; ?>)">
                    <?php echo htmlspecialchars($customer_row['consumer_name']); ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="chatbox">
        <h3>Berichten</h3>
        <div class="chat-messages" id="chat-messages">
            <!-- Berichten worden hier geladen -->
        </div>

        <div id="chat-form-container">
            <form id="chat-form">
                <input type="hidden" name="seller_id" value="<?php echo $_SESSION['user_id']; ?>">
                <input type="hidden" name="consumer_id" id="consumer_id">
                <textarea name="chat_message" id="chat_message" placeholder="Typ een bericht..." required></textarea>
                <button type="submit">Verstuur</button>
            </form>
        </div>
    </div>

    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- JavaScript om het bericht na 3 seconden automatisch te verbergen -->
    <script>
        if (document.getElementById('message')) {
            setTimeout(function() {
                document.getElementById('message').style.display = 'none';
            }, 3000);
        }

        document.getElementById("chat-form").addEventListener("submit", function(e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("sent_by", "seller");
            
            fetch("send_message.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("chat_message").value = "";
                    loadMessages(document.getElementById("consumer_id").value);
                } else {
                    console.error('Fout bij versturen:', data.error);
                }
            })
            .catch(error => console.error('Fout:', error));
        });

        function loadMessages(consumerId) {
            document.getElementById("consumer_id").value = consumerId;
            fetch("get_messages.php?seller_id=<?php echo $_SESSION['user_id']; ?>&consumer_id=" + consumerId)
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
                        div.classList.add("chat-message", msg.sent_by === 'seller' ? "chat-sent" : "chat-received");
                        div.innerHTML = `<p>${msg.message}</p><span>${msg.time}</span>`;
                        chatMessages.appendChild(div);
                    });

                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => console.error('Fout:', error));
        }

        // Laad berichten elke 3 seconden
        setInterval(function() {
            loadMessages(document.getElementById("consumer_id").value);
        }, 3000);
    </script>

</body>
</html>
