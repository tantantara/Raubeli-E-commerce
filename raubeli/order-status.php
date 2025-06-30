<?php
session_start();

// Redirect to login if not logged in or not a user
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

require 'raubeli.php';

$customer_id = $_SESSION['customer_id'];

// Prepare SQL to get orders
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, os.status_name
    FROM orders o
    JOIN orderstatus os ON o.status_id = os.status_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($order_id, $order_date, $status_name);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        table th {
            background-color: #ff2256;
            color: white;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        a {
            color: #ff2256;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>My Orders</h2>

<table>
    <tr>
        <th>Order ID</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php while ($stmt->fetch()): ?>
        <tr>
            <td><?php echo $order_id; ?></td>
            <td>
                <?php
                    $dt = new DateTime($order_date, new DateTimeZone('UTC'));
                    $dt->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
                    echo $dt->format('d/m/Y, g:i A');
                ?>
            </td>
            <td><?php echo htmlspecialchars($status_name); ?></td>
            <td><a href="receipt.php?order_id=<?php echo $order_id; ?>">View Receipt</a></td>
        </tr>
    <?php endwhile; ?>

</table>

<?php $stmt->close(); ?>

</body>
</html>
