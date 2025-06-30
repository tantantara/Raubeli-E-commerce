<?php
include("raubeli.php");
include("header.php");
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'seller', 'user'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];

// Fetch latest 12 listings
$listings = [];
$result = $conn->query("SELECT listing_id, title, price, image FROM listing WHERE hidden = 0 ORDER BY listing_id DESC LIMIT 12");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
} else {
    echo "Error fetching listings: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .container {
            display: flex;
        }
        .sidebar {
            width: 200px;
            padding: 20px;
            background-color: #f9f9f9;
            border-right: 1px solid #ccc;
        }
        .sidebar h4 {
            margin-bottom: 10px;
        }
        .sidebar input[type="text"] {
            width: 100%;
            padding: 6px 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 10px;
        }
        .sidebar a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
        }
        .sidebar a:hover {
            color: #ff2256;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .product-card {
            padding: 10px;
            background-color: #fafafa;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-align: center;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .product-card:hover {
            background-color: #ff2256;
            color: white;
            transform: scale(1.03);
        }
        .product-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .product-price {
            font-size: 14px;
            color: #333;
        }
        .product-card:hover .product-price {
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="sidebar">
        <h4>Categories</h4>
        <ul>
            <li><a href="category.php?name=Electronics">Electronics</a></li>
            <li><a href="category.php?name=Books">Books</a></li>
            <li><a href="category.php?name=Foods%20%26%20Drinks">Foods & Drinks</a></li>
            <li><a href="category.php?name=Services">Services</a></li>
            <li><a href="category.php?name=Others">Others</a></li>
        </ul>
    </div>

    <div class="content">

        <div class="products-grid">
            <?php if (empty($listings)): ?>
                <p style="text-align:center; color:#999;">No products available.</p>
            <?php else: ?>
                <?php foreach ($listings as $listing): ?>
                    <a href="product.php?id=<?php echo $listing['listing_id']; ?>" class="product-card">
                        <img src="uploads/<?php echo htmlspecialchars($listing['image']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                        <div class="product-name"><?php echo htmlspecialchars($listing['title']); ?></div>
                        <div class="product-price">RM <?php echo number_format($listing['price'], 2); ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
