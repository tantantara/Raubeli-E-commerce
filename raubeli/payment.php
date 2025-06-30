<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$order_id = $_GET['order_id'] ?? 0;

// Fetch order total
$stmt = $conn->prepare("SELECT SUM(quantity * price) AS total FROM orderitem WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->bind_result($total_amount);
$stmt->fetch();
$stmt->close();

if (isset($_POST['pay_now'])) {
    $payment_date = date('Y-m-d H:i:s');
    $payment_method = $_POST['payment_method'];

    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO payment (order_id, payment_date, amount, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $order_id, $payment_date, $total_amount, $payment_method);
    $stmt->execute();
    $stmt->close();

    // Update order status to 'Processing' (status_id = 2)
    $stmt = $conn->prepare("UPDATE orders SET status_id = 2 WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    // Now update stock quantities for each ordered item
    $stmt = $conn->prepare("SELECT listing_id, quantity FROM orderitem WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $listing_id = $row['listing_id'];
        $quantity = $row['quantity'];

        $updateStockStmt = $conn->prepare("UPDATE listing SET stock = stock - ? WHERE listing_id = ?");
        $updateStockStmt->bind_param("ii", $quantity, $listing_id);
        $updateStockStmt->execute();
        $updateStockStmt->close();
    }
    $stmt->close();

    header("Location: receipt.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment - Raubeli</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }

        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        }

        .payment-container h2 {
            color: #ff2256;
            text-align: center;
            margin-bottom: 20px;
        }

        .payment-container p {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .payment-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #444;
        }

        .payment-container select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }

        .payment-container button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background-color: #ff2256;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .payment-container button:hover {
            background-color: #e01e4f;
        }
    </style>
</head>

<body>

<div class="payment-container">
    <h2>Payment for Order #<?php echo $order_id; ?></h2>
    <p>Total Amount: RM <?php echo number_format($total_amount, 2); ?></p>

    <form method="POST">
        <label>Payment Method:</label>
        <select name="payment_method" required>
            <option value="Online Banking">Online Banking</option>
            <option value="Cash On Delivery">Cash On Delivery</option>
        </select>

        <button type="submit" name="pay_now">Pay Now</button>
    </form>
</div>

<?php include("footer.php"); ?>

</body>
</html>
