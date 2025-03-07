<?php
include "databank.php";

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$consumer_id = isset($_GET["consumer_id"]) ? intval($_GET["consumer_id"]) : 0;
$seller_id = isset($_GET["seller_id"]) ? intval($_GET["seller_id"]) : 0;

if ($consumer_id > 0 && $seller_id > 0) {
    $stmt = $Mysql->prepare("SELECT message, sent_at, sent_by FROM messages 
                            WHERE (consumer_id = ? AND seller_id = ?) OR (consumer_id = ? AND seller_id = ?) 
                            ORDER BY sent_at ASC");
    if (!$stmt) {
        echo json_encode(["error" => $Mysql->error]);
        exit;
    }

    $stmt->bind_param("iiii", $consumer_id, $seller_id, $seller_id, $consumer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            "message" => htmlspecialchars($row["message"]),
            "time" => $row["sent_at"],
            "sent_by" => $row["sent_by"]
        ];
    }

    echo json_encode($messages);
    $stmt->close();
} else {
    echo json_encode(["error" => "Ongeldige gebruikers-ID's."]);
}
?>
