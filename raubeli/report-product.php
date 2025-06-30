<?php
session_start();
include("raubeli.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['customer_id'];
    $listing_id = intval($_POST['listing_id']);
    $order_id = intval($_POST['order_id']);
    $report_reason = trim($_POST['report_reason']);

    if (!empty($report_reason)) {
        // Check if already reported by this customer for same order + listing
        $stmt = $conn->prepare("SELECT report_id FROM report WHERE customer_id = ? AND order_id = ? AND listing_id = ?");
        $stmt->bind_param("iii", $customer_id, $order_id, $listing_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            date_default_timezone_set("Asia/Kuala_Lumpur");
            $report_date = date("Y-m-d H:i:s");

            $insert = $conn->prepare("INSERT INTO report (customer_id, order_id, listing_id, report_reason, report_date) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("iiiss", $customer_id, $order_id, $listing_id, $report_reason, $report_date);
            $insert->execute();
            $insert->close();
        }

        $stmt->close();
    }

    $conn->close();
    header("Location: orders.php");
    exit();
}
?>
