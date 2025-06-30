<?php
session_start();
include('raubeli.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $seller_id = $_SESSION['seller_id'];
    $order_id = (int)$_POST['update'];
    $status_id = (int)$_POST['status_id'][$order_id];

    // Verify seller owns this order by checking linked listings
    $check_sql = "
    SELECT COUNT(*) as cnt
    FROM orderitem oi
    JOIN listing l ON oi.listing_id = l.listing_id
    WHERE oi.order_id = ? AND l.seller_id = ?
    ";

    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ii", $order_id, $seller_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    if ($row_check['cnt'] > 0) {
        // Seller owns this order, update status
        $update_sql = "UPDATE orders SET status_id = ? WHERE order_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("ii", $status_id, $order_id);
        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Order #$order_id status updated successfully.";
        } else {
            $_SESSION['message'] = "Failed to update order status.";
        }
        $stmt_update->close();
    } else {
        $_SESSION['message'] = "You do not have permission to update this order.";
    }

    $stmt_check->close();
    header("Location: seller-orders.php");
    exit();
} else {
    header("Location: seller-orders.php");
    exit();
}
