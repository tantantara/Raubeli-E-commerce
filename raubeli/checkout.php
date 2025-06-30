<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if (isset($_POST['confirm_order'])) {
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $order_date = date('Y-m-d H:i:s');
    $status_id = 1; // Pending status

    // Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, status_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Order insert failed: " . $conn->error);
    }
    $stmt->bind_param("isi", $customer_id, $order_date, $status_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO orderitem (order_id, listing_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Order item insert failed: " . $conn->error);
    }

    foreach ($_SESSION['cart'] as $listing_id => $item) {
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $stmt->bind_param("iiid", $order_id, $listing_id, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();

    unset($_SESSION['cart']); // Clear cart
    header("Location: payment.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Confirm Order - Raubeli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }

        .checkout-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        .checkout-container h2 {
            color: #ff2256;
            text-align: center;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .cart-item-details {
            flex-grow: 1;
        }

        .cart-item-details p {
            margin: 4px 0;
            color: #333;
        }

        .confirm-button {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            background-color: #ff2256;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .confirm-button:hover {
            background-color: #e01e4f;
        }

        .back-link {
            display:inline-block; 
            margin:20px 0 0 20px;
        }

        .back-link img {
            width: 40px; 
            height: 40px;
        }
    </style>
</head>
<body>

<a href="cart.php" class="back-link">
    <img src="back.png" alt="Back">
</a>

<div class="checkout-container">
    <h2>Confirm Your Order</h2>

    <?php foreach ($_SESSION['cart'] as $listing_id => $item): ?>
        <?php
        $imagePath = !empty($item['image']) ? 'uploads/' . htmlspecialchars($item['image']) : 'uploads/default-product.jpg';
        ?>
        <div class="cart-item">
            <img src="<?php echo $imagePath; ?>" alt="Product Image">
            <div class="cart-item-details">
                <p><strong><?php echo htmlspecialchars($item['title']); ?></strong></p>
                <p>Quantity: <?php echo intval($item['quantity']); ?></p>
                <p>Price: RM <?php echo number_format($item['price'], 2); ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <form method="POST">
        <button type="submit" name="confirm_order" class="confirm-button">Confirm & Proceed to Payment</button>
    </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>
