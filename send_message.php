<?php
include "databank.php";
session_start();

header('Content-Type: application/json');

// Zet foutmeldingen aan voor debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ["success" => false];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["chat_message"], $_POST["seller_id"], $_POST["consumer_id"])) {
    $consumer_id = intval($_POST["consumer_id"]);
    $seller_id = intval($_POST["seller_id"]);
    $message = trim($_POST["chat_message"]);
    $sent_by = in_array($_SESSION['role'], ['consument', 'thuischef']) ? 'consumer' : 'seller';

    if ($consumer_id > 0 && $seller_id > 0 && !empty($message)) {
        $stmt = $Mysql->prepare("INSERT INTO messages (consumer_id, seller_id, message, sent_by) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(["success" => false, "error" => $Mysql->error]);
            exit;
        }
        $stmt->bind_param("iiss", $consumer_id, $seller_id, $message, $sent_by);
        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            $response["error"] = $stmt->error;
        }
        $stmt->close();
    } else {
        $response["error"] = "Ongeldige invoer.";
    }
} else {
    $response["error"] = "Ongeldige aanvraag.";
}

echo json_encode($response);
?>
