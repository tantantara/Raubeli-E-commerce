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

// Hide the listing only if it belongs to the current seller
$stmt = $conn->prepare("UPDATE listing SET hidden = 1 WHERE listing_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $listing_id, $seller_id);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: add.php");
exit;
?>
