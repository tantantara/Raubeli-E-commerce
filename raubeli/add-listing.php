<?php include("raubeli.php"); ?>
<?php include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];
$errors = [];

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';
$category = $_POST['category'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$title || !$description || !$category || !is_numeric($price) || $price < 0 || !ctype_digit($stock) || $stock < 0) {
        $errors[] = "Please fill in all fields correctly.";
    }

    $image_name = '';
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
            $image_name = uniqid('img_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid image format.";
        }
    } else {
        $errors[] = "Please select an image.";
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO listing (seller_id, title, description, price, stock, image, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdiss", $seller_id, $title, $description, $price, $stock, $image_name, $category);
        if ($stmt->execute()) {
            header("Location: seller-profile.php");
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Add Listing</title>
    <style>
        .add-listing-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 0 10px #ccc;
            font-family: Arial, sans-serif;
        }

        .add-listing-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .add-listing-form input[type="text"],
        .add-listing-form input[type="number"],
        .add-listing-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .add-listing-form textarea {
            resize: none;
            height: 80px;
        }

        .add-listing-form input[type="file"] {
            margin-bottom: 15px;
        }

        .add-listing-form button {
            background-color: #ff2256;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .add-listing-form button:hover {
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

        .success {
            background-color: #ddffdd;
            border: 1px solid #5cba5c;
            color: #060;
        }

        .back-link img {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }

        .button-container {
            text-align: center;
        }
    </style>
</head>

<body>
    <a href="seller-profile.php" class="back-link">
        <img src="back.png" alt="Back">
    </a>

    <h2 style="text-align:center; color:#ff2256; font-family: Arial, sans-serif;">Add New Listing</h2>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="add-listing-form">
        <label>Name:</label>
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

        <label>Product Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <div class="button-container">
            <button type="submit">Save Changes</button>
        </div>
    </form>
</body>

</html>