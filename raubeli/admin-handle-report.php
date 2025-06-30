<?php
session_start();
include("icon.php");
include("raubeli.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Dismiss Report
if (isset($_POST['dismiss'], $_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);

    $stmt = $conn->prepare("DELETE FROM report WHERE report_id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();
}

// Suspend Seller
if (isset($_POST['suspend'], $_POST['seller_id'])) {
    $seller_id = intval($_POST['seller_id']);
    $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : null;

    // 1. Suspend the seller
    $stmt = $conn->prepare("UPDATE seller SET status = 'suspended' WHERE seller_id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $stmt->close();

    // 2. Insert report if not already exists
    if ($listing_id) {
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM report WHERE listing_id = ?");
        $check_stmt->bind_param("i", $listing_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count == 0) {
            $reason = "Suspended by admin";
            $stmt = $conn->prepare("INSERT INTO report (order_id, listing_id, report_reason, report_date, admin_id) VALUES (NULL, ?, ?, NOW(), NULL)");
            $stmt->bind_param("is", $listing_id, $reason);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Unsuspend Seller
if (isset($_POST['unsuspend'], $_POST['seller_id'])) {
    $seller_id = intval($_POST['seller_id']);

    $stmt = $conn->prepare("UPDATE seller SET status = 'active' WHERE seller_id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: admin-reports.php");
exit;
