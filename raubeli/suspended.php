<?php
session_start();
include("icon.php");
include("raubeli.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];
$report_reason = "No reason provided.";

// Get the latest report reason related to this seller's listings
$stmt = $conn->prepare("
    SELECT r.report_reason 
    FROM report r
    JOIN listing l ON r.listing_id = l.listing_id
    WHERE l.seller_id = ?
    AND r.report_reason IS NOT NULL
    ORDER BY r.report_date DESC
    LIMIT 1
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->bind_result($reason);
if ($stmt->fetch()) {
    $report_reason = $reason;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Account Suspended</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  .suspended-container {
    max-width: 600px;
    background: #fff;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 0 12px #ccc;
    text-align: center;
  }

  .suspended-container h1 {
    color: #ff2256;
    font-size: 2.8rem;
    margin-bottom: 20px;
  }

  .suspended-container p {
    font-size: 1.2rem;
    color: #333;
    margin: 12px 0;
  }

  .btn-logout {
    display: inline-block;
    margin-top: 30px;
    background-color: #ff2256;
    color: #fff;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 4px;
    text-decoration: none;
    box-shadow: 0 0 12px #ff2256aa;
    transition: background-color 0.3s ease;
  }

  .btn-logout:hover {
    background-color: #d11744;
    box-shadow: 0 0 18px #d11744cc;
  }

  a.email-link {
    color: #ff2256;
    text-decoration: none;
    font-weight: 600;
  }

  .reason-box {
    margin-top: 15px;
    padding: 15px;
    border: 1px solid #eee;
    background-color: #fdf2f4;
    border-radius: 6px;
    color: #555;
  }
</style>
</head>
<body>

<div class="suspended-container">
  <h1>Account Suspended</h1>
  <p>Your seller account has been suspended.</p>
  <p>Please contact support at 
    <a class="email-link" href="mailto:admin@raubeli.com">admin@raubeli.com</a> 
    for assistance.</p>

  <strong><div class="reason-box">
    Reason: <?= htmlspecialchars($report_reason) ?>
  </div></strong>

  <a href="logout.php" class="btn-logout">Logout</a>
</div>

</body>
</html>
