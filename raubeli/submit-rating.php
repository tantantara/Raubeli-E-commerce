<?php
include("raubeli.php");
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $listing_id = $_POST['listing_id'] ?? null;
    $rating = $_POST['rating'] ?? null;

    if (!$order_id || !$listing_id || !$rating || $rating < 1 || $rating > 5) {
        $_SESSION['msg'] = "Invalid submission. Please try again.";
        header("Location: orders.php");
        exit;
    }

    // Check if rating already exists
    $stmt = $conn->prepare("SELECT * FROM report WHERE order_id = ? AND listing_id = ? AND rating IS NOT NULL");
    $stmt->bind_param("ii", $order_id, $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['msg'] = "You have already rated this product.";
    } else {
        // Insert new rating into report table
        $insert = $conn->prepare("INSERT INTO report (order_id, listing_id, rating) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $order_id, $listing_id, $rating);
        if ($insert->execute()) {
            $_SESSION['msg'] = "Thank you for your rating!";
        } else {
            $_SESSION['msg'] = "Failed to submit rating.";
        }
        $insert->close();
    }

    $stmt->close();
    $conn->close();
    header("Location: orders.php");
    exit;
} else {
    header("Location: orders.php");
    exit;
}
