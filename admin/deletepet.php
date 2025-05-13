<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['pet_id'])) {
    $pet_id = intval($_GET['pet_id']);
    $stmt = $conn->prepare("DELETE FROM Pet WHERE pet_id = ?");
    $stmt->bind_param("i", $pet_id);

    if ($stmt->execute()) {
        header("Location: manage_pets.php?msg=deleted");
    } else {
        echo "Error deleting pet.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>