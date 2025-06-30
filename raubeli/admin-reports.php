<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Set timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

// Fetch all reports with listing and seller details
$sql = "SELECT r.report_id, r.listing_id, r.report_reason, r.report_date,
               l.title, l.image, s.name AS seller_name, s.seller_id, s.status AS seller_status
        FROM report r
        JOIN listing l ON r.listing_id = l.listing_id
        JOIN seller s ON l.seller_id = s.seller_id
        WHERE r.report_reason IS NOT NULL
        ORDER BY r.report_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
    <style>
        .report-box {
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 900px;
            font-family: Arial, sans-serif;
        }
        .report-item {
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
            display: flex;
            gap: 20px;
        }
        .report-item:last-child {
            border-bottom: none;
        }
        .report-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }
        .report-details {
            flex: 1;
        }
        .report-actions form {
            display: inline-block;
            margin-top: 10px;
        }
        .report-actions button {
            padding: 8px 12px;
            margin-right: 8px;
            background: #ff2256;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .report-actions button:hover {
            background: #e01e4f;
        }
    </style>
</head>
<body>

<div class="report-box">
    <h2 style="text-align:center; color:#ff2256;">Reported Products</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="report-item">
                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="Product Image" class="report-image">
                <div class="report-details">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p>
                        <strong>Reported by:</strong> Customer<br>
                        <strong>Seller:</strong> <?= htmlspecialchars($row['seller_name']) ?> (<?= ucfirst($row['seller_status']) ?>)<br>
                        <strong>Reason:</strong> <?= htmlspecialchars($row['report_reason']) ?><br>
                        <strong>Date:</strong>
                        <?= date("d M Y, h:i A", strtotime($row['report_date'])) ?>
                    </p>

                    <div class="report-actions">
                        <!-- Suspend / Unsuspend Button -->
                        <form method="POST" action="admin-handle-report.php">
                            <input type="hidden" name="seller_id" value="<?= $row['seller_id'] ?>">
                            <input type="hidden" name="listing_id" value="<?= $row['listing_id'] ?>">
                            <?php if ($row['seller_status'] === 'active'): ?>
                                <button type="submit" name="suspend">Suspend Seller</button>
                            <?php else: ?>
                                <button type="submit" name="unsuspend">Unsuspend Seller</button>
                            <?php endif; ?>
                        </form>

                        <!-- Dismiss Report Button -->
                        <form method="POST" action="admin-handle-report.php">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <button type="submit" name="dismiss">Dismiss Report</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:#777;">No reports found.</p>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>
</body>
</html>
