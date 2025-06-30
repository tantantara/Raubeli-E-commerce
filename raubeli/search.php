<?php
include("raubeli.php");
include("header.php");

$allowed = ['admin', 'seller', 'user'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed)) {
    header("Location: login.php");
    exit;
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
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
            margin-bottom: 15px;
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
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

        <?php if ($query === ''): ?>
            <p>Please enter a search term.</p>
        <?php else: ?>
            <?php
            $stmt = $conn->prepare("SELECT listing_id, title, price, image FROM listing WHERE title LIKE ?");
            $searchTerm = "%$query%";
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <?php if ($result->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                            $image_file = !empty($row['image']) ? 'uploads/' . htmlspecialchars($row['image']) : 'uploads/no-image.png';
                            $title = htmlspecialchars($row['title']);
                            $price = number_format($row['price'], 2);
                            $id = $row['listing_id'];
                        ?>
                        <a href="product.php?id=<?php echo $id; ?>" class="product-card">
                            <img src="<?php echo $image_file; ?>" alt="<?php echo $title; ?>">
                            <div class="product-name"><?php echo $title; ?></div>
                            <div class="product-price">RM <?php echo $price; ?></div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No products found matching your search.</p>
            <?php endif; ?>

            <?php
            $stmt->close();
            $conn->close();
            ?>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
