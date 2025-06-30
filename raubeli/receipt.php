<?php
// Set timezone to Malaysia
ini_set('date.timezone', 'Asia/Kuala_Lumpur');
date_default_timezone_set('Asia/Kuala_Lumpur');

include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, os.status_name, p.amount, p.payment_method
    FROM orders o
    LEFT JOIN orderstatus os ON o.status_id = os.status_id
    LEFT JOIN payment p ON o.order_id = p.order_id
    WHERE o.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($oid, $order_date, $status_name, $amount, $payment_method);
$stmt->fetch();
$stmt->close();

if (!$oid) {
    echo "Order not found.";
    exit();
}

$display_order_date = date("d/m/Y, g:i A", strtotime($order_date));

// Fetch order items
$stmt = $conn->prepare("SELECT listing_id, quantity, price FROM orderitem WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$order_items = [];
while ($row = $result->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Receipt for Order #<?php echo $order_id; ?></title>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="home.png" alt="Home" style="width: 40px; height: 40px;">
</a>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
    }

    .receipt-container {
        max-width: 800px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
    }

    .receipt-container h2 {
        color: #ff2256;
        text-align: center;
        margin-bottom: 20px;
    }

    .receipt-container p {
        font-size: 16px;
        color: #444;
        margin: 8px 0;
    }

    .receipt-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .receipt-container table th, .receipt-container table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .receipt-container table th {
        background-color: #ff2256;
        color: #fff;
    }

    .receipt-container table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .receipt-container table tr:hover {
        background-color: #f9e5ea;
    }

    .receipt-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #777;
    }
</style>
</head>
<body>

<div class="receipt-container">
    <h2>Receipt for Order #<?php echo $order_id; ?></h2>
    <p><strong>Order Date:</strong> <?php echo $display_order_date; ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($status_name); ?></p>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
    <p><strong>Total Paid:</strong> RM <?php echo number_format($amount, 2); ?></p>

    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th>Listing ID</th>
                <th>Quantity</th>
                <th>Price (RM)</th>
                <th>Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['listing_id']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="receipt-footer">
        Thank you for your purchase at Raubeli!
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
