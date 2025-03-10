<?php
include "databank.php"; // Zorg ervoor dat je databaseverbinding hier correct is ingesteld

// Variabelen voor berichten
$message = "";
$message_class = ""; // Dit bepaalt de CSS-klasse voor het bericht

// Controleer of het formulier is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controleer of de benodigde velden bestaan in de POST-array
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
        // Verkrijg de waarden uit het formulier
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password']; // Bevestigingswachtwoord
        $role = $_POST['role']; // Verkrijg de rol uit het formulier

        // Controleer of de rol een geldige waarde heeft
        $allowed_roles = ['thuischef', 'boer', 'winkelier', 'consument'];
        if (!in_array($role, $allowed_roles)) {
            $message = "Ongeldige rol geselecteerd!";
            $message_class = "error";
        } else {
            // Controleer of de wachtwoorden gelijk zijn
            if ($password !== $confirm_password) {
                $message = "Wachtwoorden komen niet overeen!";
                $message_class = "error";
            } else {
                // Controleer of de gebruiker al bestaat in de database
                $stmt = $Mysql->prepare("SELECT * FROM gebruikers WHERE email = ? OR username = ?");
                $stmt->bind_param("ss", $email, $username); // Bind de parameters (strings)
                $stmt->execute();
                $stmt->store_result(); // Sla de resultaten op

                // Als de gebruiker al bestaat, toon een foutmelding
                if ($stmt->num_rows > 0) {
                    $message = "De gebruikersnaam of e-mail bestaat al!";
                    $message_class = "error";
                } else {
                    // Hash het wachtwoord
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Voeg de nieuwe gebruiker toe aan de database
                    $stmt = $Mysql->prepare("INSERT INTO gebruikers (username, email, hashed_password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role); // Bind de parameters (strings)

                    // Voer de query uit en controleer of de registratie succesvol was
                    if ($stmt->execute()) {
                        $message = "Registratie geslaagd! Je kunt nu inloggen.";
                        $message_class = "success";
                        header("Location: login.php");
                        exit;
                    } else {
                        $message = "Er is een fout opgetreden tijdens de registratie. Probeer het opnieuw.";
                        $message_class = "error";
                    }
                }

                // Sluit de statement
                $stmt->close();
            }
        }
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
    <title>Register</title>
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
    <style>
        body {
            height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column; /* Zorg ervoor dat de pagina van boven naar beneden wordt opgebouwd */
            justify-content: center;
            align-items: center;
            background-color: #051922;
            margin: 0;
        }
        .top-header-area {
            background: #07212e;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .login {
            background: #07212e;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 400px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px; /* Voeg wat ruimte toe boven het formulier voor de boodschap */
        }
        .message {
            width: 100%;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #28a745; /* Groen */
            color: white;
        }
        .error {
            background-color: #dc3545; /* Rood */
            color: white;
        }
        input, select {
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
        p {
            color: #ccc;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
<?php include "header.php"; ?>

    <!-- Toon het bericht bovenaan de pagina -->
    <?php if ($message): ?>
        <div class="message <?php echo $message_class; ?>" id="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="login">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <select name="role" required>
                <option value="consument">Consument</option>
                <option value="thuischef">Thuischef</option>
                <option value="boer">Boer</option>
                <option value="winkelier">Winkelier</option>
            </select>
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- JavaScript om het bericht na 3 seconden automatisch te verbergen -->
    <script>
        // Controleer of het bericht bestaat
        if (document.getElementById('message')) {
            // Verberg het bericht na 3 seconden
            setTimeout(function() {
                document.getElementById('message').style.display = 'none';
            }, 3000); // 3000 milliseconden = 3 seconden
        }
    </script>

</body>
</html>