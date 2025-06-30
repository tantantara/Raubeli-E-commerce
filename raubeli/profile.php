<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'] ?? null;
if (!$customer_id) {
    header("Location: login.php");
    exit();
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['name', 'email', 'address'];
    foreach ($fields as $field) {
        if (isset($_POST["update_$field"])) {
            $value = $_POST[$field] ?? '';
            $stmt = $conn->prepare("UPDATE customer SET $field = ? WHERE customer_id = ?");
            $stmt->bind_param("si", $value, $customer_id);
            $stmt->execute();
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

$stmt = $conn->prepare("SELECT name, email, address, customer_id FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $phone);
$stmt->fetch();
$stmt->close();

// Fetch customer orders and statuses
$order_sql = "
    SELECT o.order_id, o.order_date, os.status_name
    FROM orders o
    JOIN orderstatus os ON o.status_id = os.status_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

$orders = [];
while ($order_row = $order_result->fetch_assoc()) {
    $orders[] = $order_row;
}
$order_stmt->close();
$conn->close();
?>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="home.png" alt="Home" style="width: 40px; height: 40px;">
</a>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Profile - Raubeli</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; margin:0; padding:0; }
        .profile-container {
            max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 12px #ccc;
        }
        h2 { color: #ff2256; text-align: center; margin-bottom: 30px; }
        .profile-row { margin-bottom: 20px; display: flex; align-items: center; }
        .label { width: 120px; font-weight: bold; color: #333; }
        .value { font-size: 16px; color: #555; margin-right: 10px; }
        input[type="text"], textarea {
            font-size: 16px; padding: 4px 8px; margin-right: 10px;
            border: 1px solid #ccc; border-radius: 4px;
            width: 100%;
            max-width: 350px;
        }
        textarea { resize: vertical; height: 60px; }
        .hidden { display: none; }
        .visible-inline { display: inline-block; }
        .visible-block { display: block; }
        .save-btn {
            background: #ff2256; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .save-btn:hover { background: #e01e4f; }
        .edit-icon {
            width: 20px; height: 20px; margin-left: 10px; cursor: pointer; opacity: 0.6; transition: opacity 0.3s ease;
        }
        .edit-icon:hover { opacity: 1; }
        .orders-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 12px #ccc;
            font-family: Arial, sans-serif;
        }
        .orders-container h3 {
            color: #ff2256;
            margin-bottom: 20px;
        }
        .orders-container ul {
            list-style: none;
            padding-left: 0;
        }
        .orders-container li {
            margin-bottom: 12px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .orders-container li strong {
            display: block;
            margin-bottom: 4px;
        }
        .status-delivered {
            color: green;
            font-weight: bold;
        }
        .status-other {
            color: #ff2256;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px; text-align: center; color: #999; padding: 10px 0; border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>My Profile</h2>

    <div class="profile-row">
        <div class="label">Phone:</div>
        <div class="value"><?php echo htmlspecialchars($phone ?? 'N/A'); ?></div>
    </div>

    <!-- Name -->
    <form method="POST" class="profile-row" onsubmit="return validateName()">
        <div class="label">Name:</div>
        <div class="value" id="name-display"><?php echo htmlspecialchars($name); ?></div>
        <input type="text" name="name" id="name-input" value="<?php echo htmlspecialchars($name); ?>" class="hidden">
        <button type="submit" name="update_name" class="save-btn hidden" id="name-save-btn">Save</button>
        <img src="edit.png" alt="Edit" class="edit-icon" id="name-edit-btn" title="Edit name">
    </form>

    <!-- Email -->
    <form method="POST" class="profile-row" onsubmit="return validateEmail()">
        <div class="label">Email:</div>
        <div class="value" id="email-display"><?php echo htmlspecialchars($email); ?></div>
        <input type="text" name="email" id="email-input" value="<?php echo htmlspecialchars($email); ?>" class="hidden">
        <button type="submit" name="update_email" class="save-btn hidden" id="email-save-btn">Save</button>
        <img src="edit.png" alt="Edit" class="edit-icon" id="email-edit-btn" title="Edit email">
    </form>

    <!-- Address -->
    <form method="POST" class="profile-row" onsubmit="return validateAddress()">
        <div class="label">Address:</div>
        <div class="value" id="address-display"><?php echo nl2br(htmlspecialchars($address)); ?></div>
        <textarea name="address" id="address-input" class="hidden"><?php echo htmlspecialchars($address); ?></textarea>
        <button type="submit" name="update_address" class="save-btn hidden" id="address-save-btn">Save</button>
        <img src="edit.png" alt="Edit" class="edit-icon" id="address-edit-btn" title="Edit address">
    </form>
</div>

<!-- Orders section -->
<div class="orders-container">
    <h3>My Orders</h3>
    <?php if (empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <?php
        $delivered = [];
        $not_delivered = [];

        foreach ($orders as $order) {
            if (strtolower($order['status_name']) === 'delivered') {
                $delivered[] = $order;
            } else {
                $not_delivered[] = $order;
            }
        }
        ?>
        <ul>
            <?php foreach ($not_delivered as $order): ?>
                <?php $statusClass = strtolower($order['status_name']) === 'delivered' ? 'status-delivered' : 'status-other'; ?>
                <li>
                    <strong>Order #<?= htmlspecialchars($order['order_id']) ?></strong>
                    Date: <?= date("d/m/Y, g:i A", strtotime($order['order_date'])) ?><br>
                    Status: <span class="<?= $statusClass ?>"><?= htmlspecialchars($order['status_name']) ?></span>
                </li>
            <?php endforeach; ?>

            <?php if (!empty($delivered)): ?>
                <li style="border: none; margin-top: 20px; font-weight: bold; color: #333;">
                    <hr>
                    Delivered Orders
                    <hr>
                </li>
            <?php endif; ?>

            <?php foreach ($delivered as $order): ?>
                <li>
                    <strong>Order #<?= htmlspecialchars($order['order_id']) ?></strong>
                    Date: <?= date("d/m/Y, g:i A", strtotime($order['order_date'])) ?><br>
                    Status: <span class="status-delivered"><?= htmlspecialchars($order['status_name']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>

<script>
    function toggleEdit(field) {
        document.getElementById(field + '-display').classList.add('hidden');
        const input = document.getElementById(field + '-input');
        input.classList.remove('hidden');

        if (field === 'address') {
            input.classList.add('visible-block');
            input.classList.remove('visible-inline');
        } else {
            input.classList.add('visible-inline');
            input.classList.remove('visible-block');
        }

        document.getElementById(field + '-save-btn').classList.remove('hidden');
        document.getElementById(field + '-edit-btn').style.display = 'none';
    }

    ['name', 'email', 'address'].forEach(field => {
        document.getElementById(field + '-edit-btn').addEventListener('click', () => toggleEdit(field));
    });

    function validateName() {
        const val = document.getElementById('name-input').value.trim();
        if (!val) { alert('Name cannot be empty.'); return false; }
        return true;
    }

    function validateEmail() {
        const val = document.getElementById('email-input').value.trim();
        if (!val) { alert('Email cannot be empty.'); return false; }
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(val)) { alert('Please enter a valid email address.'); return false; }
        return true;
    }

    function validateAddress() {
        const val = document.getElementById('address-input').value.trim();
        if (!val) { alert('Address cannot be empty.'); return false; }
        return true;
    }
</script>

</body>
</html>
