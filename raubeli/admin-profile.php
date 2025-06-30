<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin profile details
$stmt = $conn->prepare("SELECT admin_id, name, email FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_id, $name, $email);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="home.png" alt="Home" style="width: 40px; height: 40px;">
</a>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Profile - Raubeli</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; margin:0; padding:0; }
        .profile-container {
            max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 12px #ccc;
        }
        h2 { color: #ff2256; text-align: center; margin-bottom: 30px; }
        .profile-row { margin-bottom: 20px; display: flex; align-items: center; }
        .label { width: 120px; font-weight: bold; color: #333; }
        .value { font-size: 16px; color: #555; }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>Admin Profile</h2>

    <div class="profile-row">
        <div class="label">Phone:</div>
        <div class="value"><?php echo htmlspecialchars($admin_id); ?></div>
    </div>

    <div class="profile-row">
        <div class="label">Name:</div>
        <div class="value"><?php echo htmlspecialchars($name); ?></div>
    </div>

    <div class="profile-row">
        <div class="label">Email:</div>
        <div class="value"><?php echo htmlspecialchars($email); ?></div>
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
