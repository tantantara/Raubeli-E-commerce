<?php 
include("raubeli.php");
include("header.php"); 

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];

$totalSales = 0;

// Get per-product sales and total sales
$product_sales = [];
$totalSales = 0;

$sales_sql = "
    SELECT l.title, SUM(oi.quantity) AS total_sold, SUM(oi.price * oi.quantity) AS product_total
    FROM payment p
    JOIN orders o ON p.order_id = o.order_id
    JOIN orderitem oi ON o.order_id = oi.order_id
    JOIN listing l ON oi.listing_id = l.listing_id
    WHERE l.seller_id = ?
    GROUP BY l.title
    ORDER BY product_total DESC
";

$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->bind_param("i", $seller_id);
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();

while ($row = $sales_result->fetch_assoc()) {
    $product_sales[] = $row;
    $totalSales += $row['product_total'];
}

$sales_stmt->close();

// Fetch orders with their items
$sql = "
    SELECT o.order_id, o.order_date, o.status_id, os.status_name, 
           oi.quantity, oi.price, l.title
    FROM orders o
    JOIN orderitem oi ON o.order_id = oi.order_id
    JOIN listing l ON oi.listing_id = l.listing_id
    JOIN orderstatus os ON o.status_id = os.status_id
    WHERE l.seller_id = ?
    ORDER BY o.order_date DESC, o.order_id, l.title
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$statusOptions = [
    1 => 'Pending',
    2 => 'Processing',
    3 => 'Delivered',
    4 => 'Cancelled'
];

$ongoing_orders = [];
$completed_orders = [];

while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];

    if ($row['status_id'] == 1 || $row['status_id'] == 2) {
        $orderList = &$ongoing_orders;
    } elseif ($row['status_id'] == 3) {
        $orderList = &$completed_orders;
    } else {
        continue; // Skip cancelled or unknown statuses
    }

    if (!isset($orderList[$orderId])) {
        $orderList[$orderId] = [
            'order_date' => $row['order_date'],
            'status_id' => $row['status_id'],
            'status_name' => $row['status_name'],
            'items' => []
        ];
    }

    $orderList[$orderId]['items'][] = [
        'title' => $row['title'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    ];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .main-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            color: #ff2256;
            margin: 40px 0 20px;
            font-size: 24px;
        }

        .order-card {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .order-block {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .order-header h4 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .status-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .status-select {
            padding: 6px;
            font-size: 14px;
            width: 150px;
        }

        .update-btn {
            padding: 6px 12px;
            background-color: #ff2256;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            width: 150px;
        }

        .update-btn:hover {
            background-color: #e01e4f;
        }

        .order-info {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .product-line {
            margin-left: 20px;
        }

        .no-orders {
            background: #fff3f5;
            padding: 15px;
            border-left: 4px solid #ff2256;
            margin-bottom: 20px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="main-container">
    <h2 class="section-title">Sales Summary</h2>
    <?php if (empty($product_sales)): ?>
        <div class="no-orders">You haven't made any sales yet.</div>
    <?php else: ?>
        <div class="order-card">
            <?php foreach ($product_sales as $sale): ?>
                <p>
                    <strong><?= htmlspecialchars($sale['title']) ?></strong><br>
                    <?= (int)$sale['total_sold'] ?> sold —
                    RM <?= number_format($sale['product_total'], 2) ?>
                </p>
            <?php endforeach; ?>
            <hr style="margin: 15px 0;">
            <p><strong>Total Sales:</strong> RM <?= number_format($totalSales, 2) ?></p>
        </div>
    <?php endif; ?>
    <h2 class="section-title">Ongoing Orders</h2>
    <?php if (empty($ongoing_orders)): ?>
        <div class="no-orders">You have no ongoing orders.</div>
    <?php else: ?>
        <form method="POST" action="update-order-status.php">
            <div class="order-card">
                <?php foreach ($ongoing_orders as $orderId => $order): ?>
                    <div class="order-block">
                        <div class="order-header">
                            <h4>Order #<?= htmlspecialchars($orderId) ?></h4>
                            <div class="status-container">
                                <select name="status_id[<?= $orderId ?>]" class="status-select">
                                    <?php foreach ($statusOptions as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= ($order['status_id'] == $id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update" value="<?= $orderId ?>" class="update-btn">Update Status</button>
                            </div>
                        </div>
                        <div class="order-info">
                            <p><strong>Date:</strong> <?= date("d/m/Y, g:i A", strtotime($order['order_date'])) ?></p>
                            <?php foreach ($order['items'] as $item): ?>
                                <p class="product-line">
                                    <strong>Product:</strong> <?= htmlspecialchars($item['title']) ?>,
                                    <strong>Quantity:</strong> <?= (int)$item['quantity'] ?>,
                                    <strong>Price:</strong> RM <?= number_format($item['price'], 2) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php endif; ?>


    <h2 class="section-title">Completed Orders</h2>
    <?php if (empty($completed_orders)): ?>
        <div class="no-orders">You have no completed orders.</div>
    <?php else: ?>
        <div class="order-card">
            <?php foreach ($completed_orders as $orderId => $order): ?>
                <div class="order-block">
                    <div class="order-header">
                        <h4>Order #<?= htmlspecialchars($orderId) ?></h4>
                        <div class="status-container">
                            <div style="font-weight: bold; color: #28a745;">
                                <?= htmlspecialchars($order['status_name']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="order-info">
                        <p><strong>Date:</strong> <?= date("d/m/Y, g:i A", strtotime($order['order_date'])) ?></p>
                        <?php foreach ($order['items'] as $item): ?>
                            <p class="product-line">
                                <strong>Product:</strong> <?= htmlspecialchars($item['title']) ?>,
                                <strong>Quantity:</strong> <?= (int)$item['quantity'] ?>,
                                <strong>Price:</strong> RM <?= number_format($item['price'], 2) ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include("footer.php"); ?>

</body>
</html>
