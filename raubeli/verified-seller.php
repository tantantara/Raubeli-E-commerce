<?php
include("raubeli.php");
include("header.php");

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle verification
if (isset($_GET['verify_id']) && is_numeric($_GET['verify_id'])) {
    $verify_id = $_GET['verify_id'];

    // Check if already verified
    $check = $conn->prepare("SELECT verified FROM seller WHERE seller_id = ?");
    $check->bind_param("i", $verify_id);
    $check->execute();
    $check->bind_result($already_verified);
    $check->fetch();
    $check->close();

    if (!$already_verified) {
        $stmt = $conn->prepare("UPDATE seller SET verified = 1 WHERE seller_id = ?");
        $stmt->bind_param("i", $verify_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: verified-seller.php");
    exit;
}

// Fetch sellers who agreed to terms
$result = $conn->query("SELECT seller_id, store_name, name, email, verified FROM seller WHERE agreed_terms = 1 ORDER BY seller_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Sellers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }

        h2 {
            color: #ff2256;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #ff2256;
            color: white;
        }

        .verify-btn {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }

        .verify-btn:hover {
            background-color: #218838;
        }

        .verified-badge {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Verify Sellers</h2>

    <table>
        <thead>
            <tr>
                <th>Seller ID</th>
                <th>Store Name</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['seller_id']) ?></td>
                    <td><?= htmlspecialchars($row['store_name']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php if ($row['verified']): ?>
                            <span class="verified-badge">Verified</span>
                        <?php else: ?>
                            <a href="verified-seller.php?verify_id=<?= $row['seller_id'] ?>" class="verify-btn" onclick="return confirm('Verify this seller?')">Verify</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include("footer.php"); ?>
</body>
</html>