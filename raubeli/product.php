<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'seller', 'user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];

$listing_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0);
if ($listing_id === 0) {
    echo "No product specified.";
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT listing_id, title, price, stock, description, image, seller_id, hidden FROM listing WHERE listing_id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Product not found.";
    exit();
}
$product = $result->fetch_assoc();
$stmt->close();

// If product is hidden, block access (unless admin or owner seller)
$is_owner = ($role === 'seller' && isset($_SESSION['seller_id']) && $_SESSION['seller_id'] == $product['seller_id']);
if ($product['hidden'] && !($role === 'admin' || $is_owner)) {
    echo "This product is no longer available.";
    exit();
}

// Fetch seller info for user
$seller = null;
if ($role === 'user') {
    $seller_stmt = $conn->prepare("SELECT store_name, name, email, seller_id FROM seller WHERE seller_id = ?");
    $seller_stmt->bind_param("i", $product['seller_id']);
    $seller_stmt->execute();
    $seller_result = $seller_stmt->get_result();
    $seller = $seller_result->fetch_assoc();
    $seller_stmt->close();
}

// Admin: check if seller suspended
$is_suspended = false;
if ($role === 'admin') {
    $status_stmt = $conn->prepare("SELECT status FROM seller WHERE seller_id = ?");
    $status_stmt->bind_param("i", $product['seller_id']);
    $status_stmt->execute();
    $status_stmt->bind_result($seller_status);
    $status_stmt->fetch();
    $is_suspended = ($seller_status === 'suspended');
    $status_stmt->close();
}

// Admin: suspend or unsuspend
if ($role === 'admin' && isset($_POST['suspend_seller'])) {
    $new_status = $is_suspended ? 'active' : 'suspended';
    $update_stmt = $conn->prepare("UPDATE seller SET status = ? WHERE seller_id = ?");
    $update_stmt->bind_param("si", $new_status, $product['seller_id']);
    $update_stmt->execute();
    $update_stmt->close();
    $_SESSION['msg'] = "Seller has been " . ($new_status === 'suspended' ? 'suspended' : 'unsuspended') . ".";
    header("Location: admin-reports.php");
    exit();
}

// Seller: hide product
if ($role === 'seller' && $is_owner && isset($_POST['hide_product'])) {
    $hide_stmt = $conn->prepare("UPDATE listing SET hidden = 1 WHERE listing_id = ?");
    $hide_stmt->bind_param("i", $listing_id);
    $hide_stmt->execute();
    $hide_stmt->close();
    header("Location: add.php?message=ProductHidden");
    exit();
}

// User: add to cart
if ($role === 'user' && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['hide_product'])) {
    $quantity = max(1, intval($_POST['quantity']));
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$listing_id] = [
        'title' => $product['title'],
        'description' => $product['description'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $quantity
    ];
    header("Location: cart.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .product-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fafafa;
            display: flex;
            gap: 20px;
        }
        .product-image img {
            width: 350px;
            height: 350px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-details {
            flex-grow: 1;
        }
        .product-details h1 {
            margin-top: 0;
            color: #ff2256;
        }
        .product-details h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .product-details .price {
            font-size: 20px;
            color: #222;
            font-weight: bold;
        }
        .product-details p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }
        .product-details form {
            margin-top: 20px;
        }
        .product-details input[type="number"] {
            width: 60px;
            padding: 5px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .product-details button {
            padding: 10px 20px;
            background-color: #ff2256;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .product-details button:hover {
            background-color: #e01e4f;
        }
        .delete-btn {
            background-color: #cc0000;
            margin-top: 15px;
        }
        .delete-btn:hover {
            background-color: #990000;
        }
        .suspend-btn {
            background-color: #ff9900;
            margin-top: 15px;
        }
        .suspend-btn:hover {
            background-color: #cc7a00;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="back.png" alt="Back" style="width: 40px; height: 40px;">
</a>

<div class="product-container">
    <div class="product-image">
        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
    </div>

    <div class="product-details">
        <h1><?php echo htmlspecialchars($product['title']); ?></h1>
        <h2><?php echo htmlspecialchars($product['description']); ?></h2>
        <p class="price">RM <?php echo number_format($product['price'], 2); ?></p>
        <p>Stock: <?php echo (int)$product['stock']; ?></p>

        <?php if ($role === 'user'): ?>
            <!-- Add to Cart -->
            <form method="POST">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" required>
                <button type="submit">Add to Cart</button>
            </form>

            <!-- Info Icon -->
            <div style="margin-top: 15px;">
                <img src="info.png" alt="Seller Info" title="Click to view seller details"
                     style="width: 28px; height: 28px; cursor: pointer;"
                     onclick="document.getElementById('seller-info').classList.toggle('hidden');">
            </div>

            <!-- Seller Info -->
            <div id="seller-info" class="hidden" style="margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px;">
                <h3 style="color: #ff2256;">Seller Information</h3>
                <p><strong>Store:</strong> <?php echo htmlspecialchars($seller['store_name']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($seller['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($seller['email']); ?></a></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($seller['seller_id']); ?></a></p>
            </div>

        <?php elseif ($role === 'seller' && $is_owner): ?>
            <!-- Seller Owner: Hide Option -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to hide this product?');">
                <button type="submit" name="hide_product" class="delete-btn">Hide Product</button>
            </form>

        <?php elseif ($role === 'admin'): ?>
            <!-- Admin: Suspend/Unsuspend -->
            <form method="POST" onsubmit="return confirm('Suspend this seller?');">
                <input type="hidden" name="listing_id" value="<?php echo $product['listing_id']; ?>">
                <button type="submit" name="suspend_seller" class="suspend-btn">
                    <?php echo $is_suspended ? 'Unsuspend Seller' : 'Suspend Seller'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
