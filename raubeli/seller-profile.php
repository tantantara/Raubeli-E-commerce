<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];

// Fetch seller data including agreed_terms
$stmt = $conn->prepare("SELECT store_name, name, email, status, verified, agreed_terms FROM seller WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->bind_result($store_name, $name, $email, $status, $verified, $agreed_terms);
$stmt->fetch();
$stmt->close();

// Redirect suspended sellers
if ($status === 'suspended') {
    header("Location: suspended.php");
    exit;
}

// Handle terms agreement (does NOT verify)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agree_terms'])) {
    $agree_stmt = $conn->prepare("UPDATE seller SET agreed_terms = 1 WHERE seller_id = ?");
    $agree_stmt->bind_param("i", $seller_id);
    if ($agree_stmt->execute()) {
        echo "<script>alert('Thank you. Please wait for admin to verify your account.'); window.location.href = 'seller-profile.php';</script>";
        exit;
    }
    $agree_stmt->close();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['store_name'])) {
    $new_store_name = trim($_POST['store_name'] ?? '');
    $new_name = trim($_POST['name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    $update_stmt = $conn->prepare("UPDATE seller SET store_name = ?, name = ?, email = ? WHERE seller_id = ?");
    $update_stmt->bind_param("sssi", $new_store_name, $new_name, $new_email, $seller_id);

    if ($update_stmt->execute()) {
        $store_name = $new_store_name;
        $name = $new_name;
        $email = $new_email;
        echo "<script>alert('Profile updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .seller-container {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 12px #ccc;
            padding: 20px;
        }

        .seller-header h2 {
            color: #ff2256;
            text-align: center;
            margin-bottom: 30px;
        }

        .seller-header h2 img {
            vertical-align: middle;
            margin-left: 8px;
            width: 24px;
            height: 24px;
        }

        .seller-info > div {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        strong {
            width: 100px;
            flex-shrink: 0;
        }

        .display-text {
            flex: 1;
        }

        input[type=text], input[type=email] {
            flex: 1;
            padding: 6px 10px;
            font-size: 16px;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        .hidden {
            display: none !important;
        }

        .edit-icon {
            width: 20px;
            height: 20px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .edit-icon:hover {
            opacity: 1;
        }

        .save-btn {
            background-color: #ff2256;
            border: none;
            color: #fff;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 10px auto 0;
        }

        .save-btn:hover {
            background-color: #d11744;
        }

        .agree-btn {
            background-color: #ff2256;
            border: none;
            color: #fff;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 10px 0 0 auto;
        }

        .terms-box {
            background: #fff0f0;
            padding: 15px;
            margin: 20px auto;
            border-left: 4px solid #ff2256;
            border-radius: 6px;
        }
    </style>
    <script>
        function toggleEdit(icon) {
            const container = icon.closest("div");
            const span = container.querySelector(".display-text");
            const input = container.querySelector("input");

            span.classList.toggle("hidden");
            input.classList.toggle("hidden");
            input.readOnly = !input.readOnly;
        }
    </script>
</head>
<body>

<a href="home.php" style="display:inline-block; margin:20px 0 20px 20px;">
    <img src="home.png" alt="Home" style="width: 40px; height: 40px;">
</a>

<div class="seller-container">

    <div class="seller-header">
        <h2>
            My Seller Profile
            <?php if ($verified): ?>
                <img src="verified.png" alt="Verified" title="Verified Seller">
            <?php endif; ?>
        </h2>
    </div>

    <form method="POST">
        <div class="seller-info">
            <div>
                <strong>Store Name:</strong>
                <span class="display-text"><?= htmlspecialchars($store_name); ?></span>
                <input type="text" name="store_name" value="<?= htmlspecialchars($store_name); ?>" readonly class="hidden">
                <img src="edit.png" class="edit-icon" onclick="toggleEdit(this)" title="Edit Store Name">
            </div>
            <div>
                <strong>Name:</strong>
                <span class="display-text"><?= htmlspecialchars($name); ?></span>
                <input type="text" name="name" value="<?= htmlspecialchars($name); ?>" readonly class="hidden">
                <img src="edit.png" class="edit-icon" onclick="toggleEdit(this)" title="Edit Name">
            </div>
            <div>
                <strong>Email:</strong>
                <span class="display-text"><?= htmlspecialchars($email); ?></span>
                <input type="email" name="email" value="<?= htmlspecialchars($email); ?>" readonly class="hidden">
                <img src="edit.png" class="edit-icon" onclick="toggleEdit(this)" title="Edit Email">
            </div>
            <div>
                <strong>Phone :</strong>
                <span><?= htmlspecialchars($seller_id); ?></span>
            </div>
        </div>
        <button type="submit" class="save-btn">Save Changes</button>
    </form>

    <?php if (!$verified): ?>
        <div class="terms-box">
            <?php if (!$agreed_terms): ?>
                <h3 style="color: #d11744;">Complete Verification</h3>
                <p>To become a verified seller, you must agree to our Terms and Conditions</a>.</p>
                    <form method="POST">
                        <label>
                            <input type="checkbox" name="agree_terms" required>
                            I have read and agree to the Terms and Conditions<br><br>
                            <div style="text-align: right;">
                                <button type="submit" class="agree-btn">Agree</button>
                            </div>
                        </label>
                        <br><br>
                    </form>
            <?php else: ?>
                <p style="color: #888;">You have agreed to the terms. Your account is pending admin verification.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php include("footer.php"); ?>
</body>
</html>
