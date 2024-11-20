<?php
include "databank.php"; // Zorg ervoor dat je verbinding hebt met je database

// Fout- en succesberichten variabelen
$message = "";
$message_class = "";

// Controleer of de reset-link geldig is
$form_visible = true;  // We tonen het formulier standaard
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Zoek de gebruiker op basis van de token
    $stmt = $Mysql->prepare("SELECT * FROM gebruikers WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // De token is geldig, controleer of deze nog niet is verlopen
        $user = $result->fetch_assoc();
        if (time() > $user['reset_expires']) {
            $form_visible = false; // Verberg het formulier bij een verlopen token
            $message = "De resetlink is verlopen.";
            $message_class = "error";
        } else {
            // Laat de gebruiker het wachtwoord resetten
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (isset($_POST['password'], $_POST['confirm_password'])) {
                    $password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];

                    // Controleer of de wachtwoorden overeenkomen
                    if ($password !== $confirm_password) {
                        $message = "Wachtwoorden komen niet overeen!";
                        $message_class = "error";
                    } else {
                        // Hash het nieuwe wachtwoord
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Update het wachtwoord in de database en verwijder de reset-token
                        $stmt = $Mysql->prepare("UPDATE gebruikers SET hashed_password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
                        $stmt->bind_param("ss", $hashed_password, $token);

                        if ($stmt->execute()) {
                            // Zet de form op 'niet zichtbaar' omdat het wachtwoord succesvol is gereset
                            $form_visible = false; 
                            $message = "Wachtwoord succesvol gereset! Je kunt nu inloggen.";
                            $message_class = "success";
                            sleep(1);
                            header("Location: login.php");
                        } else {
                            $message = "Er is een fout opgetreden tijdens het resetten van je wachtwoord.";
                            $message_class = "error";
                        }
                    }
                } else {
                    $message = "Vul alstublieft beide velden in.";
                    $message_class = "error";
                }
            }
        }
    } else {
        // De token is niet geldig
        $form_visible = false; // Verberg het formulier bij een ongeldige token
        $message = "Ongeldige resetlink.";
        $message_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Voeg hier je bestaande CSS en iconen toe -->
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

    <!-- Je custom CSS voor de layout -->
    <style>
        body {
            background-color: #051922;
            display: flex;
            justify-content: center;
            align-items: center;
            color: aliceblue;
        }
        h2 {
            color: white;
        }
        .reset-form {
            text-align: center;
            background-color:#051922;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            margin-top: 20px;
        }
        .reset-form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .message.success {
            color: #4CAF50;
            font-size: 18px;
            margin: 20px 0;
        }
        .message.error {
            color: #F44336;
            font-size: 18px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <!-- Hier wordt het fout- of succesbericht weergegeven -->
    <?php if ($message): ?>
        <div class="message <?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Formulier voor wachtwoord reset, alleen zichtbaar als het formulier nog niet succesvol verzonden is of als de token geldig is -->
    <?php if ($form_visible): ?>
        <div class="reset-form">
            
            <h2>Reset je wachtwoord</h2>
            <style>
                form{
                    background-color: #051922;
                }
            </style>
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Nieuw wachtwoord" required>
                <input type="password" name="confirm_password" placeholder="Bevestig wachtwoord" required>
                <input type="submit" value="Wachtwoord resetten">
            </form>
        </div>
    <?php endif; ?>

    <!-- Scripts die je pagina gebruikt -->
    <script src="assets/js/jquery-1.11.3.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.meanmenu.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>
</html>