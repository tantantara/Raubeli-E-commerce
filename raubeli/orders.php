<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$orders = [];

// Get existing ratings (based on order + listing)
$ratings = [];
$rating_result = $conn->query("SELECT order_id, listing_id, rating FROM report WHERE rating IS NOT NULL");
while ($row = $rating_result->fetch_assoc()) {
    $key = $row['order_id'] . '-' . $row['listing_id'];
    $ratings[$key] = $row['rating'];
}

// Fetch completed orders
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, l.listing_id, l.title, l.image, oi.price, oi.quantity
    FROM orders o
    JOIN orderitem oi ON o.order_id = oi.order_id
    JOIN listing l ON oi.listing_id = l.listing_id
    WHERE o.customer_id = ? AND o.status_id = 3
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .orders-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
            font-family: Arial, sans-serif;
        }

        .order-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            background-color: #fafafa;
        }

        .order-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }

        .order-details {
            flex: 1;
        }

        .order-details h3 {
            margin: 0 0 5px;
            color: #ff2256;
        }

        .order-details p {
            margin: 4px 0;
        }

        .star-rating {
            direction: rtl;
            display: inline-flex;
            font-size: 22px;
            user-select: none;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            color: #ccc;
            cursor: pointer;
        }

        .star-rating input[type="radio"]:checked ~ label {
            color: #ffcc00;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffcc00;
        }

        .rating-form button,
        .submit-report-btn {
            margin-top: 8px;
            background-color: #ff2256;
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        .report-btn {
            background: none;
            border: none;
            padding: 0;
            margin-top: 8px;
            cursor: pointer;
        }

        .report-btn img {
            width: 18px;
            height: 18px;
        }

        .report-text {
            display: none;
            width: 100%;
            height: 60px;
            margin-top: 6px;
            padding: 6px;
            font-size: 14px;
        }

        .submit-report-btn {
            display: none;
        }
    </style>

    <script>
        function toggleReportBox(button) {
            const form = button.closest('form');
            const textarea = form.querySelector('.report-text');
            const submitBtn = form.querySelector('.submit-report-btn');

            textarea.style.display = 'block';
            submitBtn.style.display = 'inline-block';
            textarea.focus();
        }

        function validateReport(form) {
            const textarea = form.querySelector('.report-text');
            if (textarea.style.display === 'none' || !textarea.value.trim()) {
                alert("Please write your report reason.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="orders-container">
    <h2 style="text-align:center; color:#ff2256;">Completed Orders</h2>

    <?php if (empty($orders)): ?>
        <p style="text-align:center; color:#777;">No completed orders found.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
            $order_id = $order['order_id'];
            $listing_id = $order['listing_id'];
            $key = $order_id . '-' . $listing_id;
            $existing_rating = $ratings[$key] ?? 0;
            ?>
            <div class="order-card">
                <img src="uploads/<?php echo htmlspecialchars($order['image']); ?>" alt="Product Image">
                <div class="order-details">
                    <h3><?php echo htmlspecialchars($order['title']); ?></h3>
                    <p>Price: RM <?php echo number_format($order['price'], 2); ?></p>
                    <p>Quantity: <?php echo (int)$order['quantity']; ?></p>
                    <p>
                        Order Date:
                        <?php
                        date_default_timezone_set("Asia/Kuala_Lumpur");
                        echo date("d M Y, h:i A", strtotime($order['order_date']));
                        ?>
                    </p>

                    <!-- Star Rating -->
                    <form action="submit-rating.php" method="POST" class="rating-form">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?php echo $i . '_' . $key; ?>" value="<?php echo $i; ?>" <?php echo ($existing_rating == $i) ? 'checked' : ''; ?>>
                                <label for="star<?php echo $i . '_' . $key; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                        <button type="submit">Submit Rating</button>
                    </form>

                    <!-- Report Form -->
                    <form action="report-product.php" method="POST" onsubmit="return validateReport(this);">
                        
                        <button type="button" class="report-btn" onclick="toggleReportBox(this)" title="Report this product">
                            <img src="report.png" alt="Report Icon">
                        </button>

                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                        <textarea name="report_reason" class="report-text" placeholder="Write your report..." required></textarea>

                        <button type="submit" class="submit-report-btn">Submit Report</button>
                    </form>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>
</body>
</html>
