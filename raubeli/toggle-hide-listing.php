<?php
include("raubeli.php");
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['listing_id'])) {
    header("Location: seller-profile.php");
    exit;
}

$listing_id = (int)$_GET['listing_id'];
$seller_id = $_SESSION['seller_id'];

// Get current hidden status
$stmt = $conn->prepare("SELECT hidden FROM listing WHERE listing_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $listing_id, $seller_id);
$stmt->execute();
$stmt->bind_result($current_hidden);

if ($stmt->fetch()) {
    $stmt->close();

    $new_hidden = $current_hidden ? 0 : 1;
    $update = $conn->prepare("UPDATE listing SET hidden = ? WHERE listing_id = ? AND seller_id = ?");
    $update->bind_param("iii", $new_hidden, $listing_id, $seller_id);
    $update->execute();
    $update->close();
}

$conn->close();
header("Location: add.php");
exit;
?>
