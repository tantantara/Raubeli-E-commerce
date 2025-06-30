<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle remove item
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

// Handle update quantity
if (isset($_POST['update_qty'])) {
    $listing_id = $_POST['listing_id'];
    $new_qty = max(1, intval($_POST['quantity']));
    if (isset($_SESSION['cart'][$listing_id])) {
        $_SESSION['cart'][$listing_id]['quantity'] = $new_qty;
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Cart - Raubeli</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; margin:0; padding:0; }
        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 12px #ccc;
        }
        h2 {
            color: #ff2256;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #ff2256;
            color: white;
        }
        .qty-input {
            width: 50px;
            text-align: center;
        }
        .remove-btn {
            background: #ff2256;
            color: white;
            border: none;
            padding: 6px 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        .remove-btn:hover {
            background-color: #e01e4f;
        }
        .update-btn {
            background: #ff2256;
            color: white;
            border: none;
            padding: 6px 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .update-btn:hover {
            background-color: #e01e4f;
        }
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        .empty-cart {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin: 50px 0;
        }
    </style>
</head>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="back.png" alt="Back" style="width: 40px; height: 40px;">
</a>

<body>
<div class="cart-container">
    <h2>My Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">Your cart is empty.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>

            <?php
            $grand_total = 0;
            foreach ($_SESSION['cart'] as $listing_id => $item):
                $item_total = $item['price'] * $item['quantity'];
                $grand_total += $item_total;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td>RM <?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                            <input type="number" name="quantity" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1">
                            <button type="submit" name="update_qty" class="update-btn">Update</button>
                        </form>
                    </td>
                    <td>RM <?php echo number_format($item_total, 2); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $listing_id; ?>" class="remove-btn" onclick="return confirm('Remove this item?');">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="total">Total: RM <?php echo number_format($grand_total, 2); ?></div>

        <form method="POST" action="checkout.php" style="text-align: right; margin-top: 20px;">
        <button type="submit" name="proceed_checkout" class="update-btn" style="font-size: 18px; padding: 10px 20px;">
            Proceed to Checkout
        </button>
    </form>

    <?php endif; ?>

</div>

<?php include("footer.php"); ?>

</body>
</html>
