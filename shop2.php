<?php

include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Controleer of de gebruiker is ingelogd en of de rol 'boer' of 'winkelier' is
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['boer', 'winkelier'])) {
    header("Location: login.php");
    exit;
}

// Variabelen voor berichten
$message = "";
$message_class = "";

// Controleer of het formulier is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controleer of de benodigde velden bestaan in de POST-array
    if (isset($_POST['product_name'], $_POST['product_description'], $_POST['product_price'])) {
        // Verkrijg de waarden uit het formulier
        $product_name = $_POST['product_name'];
        $product_description = $_POST['product_description'];
        $product_price = $_POST['product_price'];

        // Voeg het product toe aan de database
        $stmt = $Mysql->prepare("INSERT INTO producten (name, description, price, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $product_name, $product_description, $product_price, $_SESSION['user_id']); // Bind de parameters

        // Voer de query uit en controleer of de toevoeging succesvol was
        if ($stmt->execute()) {
            $message = "Product succesvol toegevoegd!";
            $message_class = "success";
        } else {
            $message = "Er is een fout opgetreden tijdens het toevoegen van het product. Probeer het opnieuw.";
            $message_class = "error";
        }

        // Sluit de statement
        $stmt->close();
    } else {
        $message = "Vul alstublieft alle velden in!";
        $message_class = "error";
    }
}

// Sluit de databaseverbinding
$Mysql->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop</title>
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
            position: fixed;
            top: 0;
            margin-left: 60%;
            background-color: #333;
            z-index: 1000;
            display: flex; /* Center the navbar horizontally */
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
        .shop {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 80px; /* Adjusted to account for the fixed navbar */
        }
        .message {
            width: 100%;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #28a745;
            color: white;
        }
        .error {
            background-color: #dc3545;
            color: white;
        }
        input, textarea {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
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
<br>
<br><br><br><br>
    <!-- Toon het bericht bovenaan de pagina -->
    <?php if ($message): ?>
        <div class="message <?php echo $message_class; ?>" id="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="shop">
        <h2>Product Toevoegen</h2>
        <form action="shop2.php" method="POST">
            <input type="text" name="product_name" placeholder="Product Naam" required>
            <textarea name="product_description" placeholder="Product Beschrijving" required></textarea>
            <input type="number" step="0.01" name="product_price" placeholder="Product Prijs" required>
            <input type="submit" value="Product Toevoegen">
        </form>
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
    </script>

</body>
</html>
