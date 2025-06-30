<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];
$listing_id = $_GET['listing_id'] ?? null;

if (!$listing_id) {
    die("Invalid listing ID.");
}

// Fetch listing details
$stmt = $conn->prepare("SELECT title, description, price, stock, image, category FROM listing WHERE listing_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $listing_id, $seller_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $stock, $image, $category);
if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission.");
}
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title'] ?? '');
    $new_description = trim($_POST['description'] ?? '');
    $new_price = $_POST['price'] ?? '';
    $new_stock = $_POST['stock'] ?? '';
    $new_category = $_POST['category'] ?? '';
    $new_image = $image;

    if ($new_title === '' || $new_description === '' || !$new_category || !is_numeric($new_price) || $new_price < 0 || !ctype_digit($new_stock) || $new_stock < 0) {
        $errors[] = "Please fill in all fields correctly.";
    }

    $upload_dir = __DIR__ . '/uploads/';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed_exts)) {
            $new_image = uniqid('img_', true) . '.' . $ext;
            $target_path = $upload_dir . $new_image;
            if (!move_uploaded_file($tmp_name, $target_path)) {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid image format.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE listing SET title = ?, description = ?, price = ?, stock = ?, image = ?, category = ? WHERE listing_id = ? AND seller_id = ?");
        $stmt->bind_param("ssdsssii", $new_title, $new_description, $new_price, $new_stock, $new_image, $new_category, $listing_id, $seller_id);
        if ($stmt->execute()) {
            header("Location: seller-profile.php");
            exit();
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Listing</title>
    <style>
        .edit-listing-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 0 10px #ccc;
            font-family: Arial, sans-serif;
        }

        .edit-listing-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .edit-listing-form input[type="text"],
        .edit-listing-form input[type="number"],
        .edit-listing-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .edit-listing-form textarea {
            resize: none;
            height: 80px;
        }

        .edit-listing-form input[type="file"] {
            margin-bottom: 15px;
        }

        .edit-listing-form button {
            background-color: #ff2256;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .edit-listing-form button:hover {
            background-color: #e01e4f;
        }

        .message {
            max-width: 600px;
            margin: 10px auto;
            padding: 10px;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }

        .error {
            background-color: #ffdddd;
            border: 1px solid #ff5c5c;
            color: #900;
        }

        .back-link img {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }

        .button-container {
        text-align: center;
    }
        .button-container button {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<a href="add.php" class="back-link">
    <img src="back.png" alt="Back">
</a>

<h2 style="text-align:center; color:#ff2256; font-family: Arial, sans-serif;">Edit Listing</h2>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="" method="post" enctype="multipart/form-data" class="edit-listing-form">
    <label>Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

    <label>Description:</label>
    <textarea name="description" required><?php echo htmlspecialchars($description); ?></textarea>

    <label>Price (RM):</label>
    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($price); ?>" required>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?php echo htmlspecialchars($stock); ?>" required>

    <label>Category:</label>
    <select name="category" required>
        <option value="">-- Select Category --</option>
        <option value="Electronics" <?php if ($category === 'Electronics') echo 'selected'; ?>>Electronics</option>
        <option value="Books" <?php if ($category === 'Books') echo 'selected'; ?>>Books</option>
        <option value="Foods & Drinks" <?php if ($category === 'Foods & Drinks') echo 'selected'; ?>>Foods & Drinks</option>
        <option value="Services" <?php if ($category === 'Services') echo 'selected'; ?>>Services</option>
        <option value="Others" <?php if ($category === 'Others') echo 'selected'; ?>>Others</option>
    </select><br><br>

    <label>Current Image:</label><br>
    <img src="uploads/<?php echo htmlspecialchars($image); ?>" style="width:200px; height:auto; margin-bottom:12px;"><br>

    <label>Change Image (optional):</label>
    <input type="file" name="image" accept="image/*">

    <div class="button-container">
        <button type="submit">Save Changes</button>
    </div>

</form>

</body>
</html>
