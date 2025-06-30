<?php
include("raubeli.php");
include("header.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['seller_id'];

$listings = [];
$result = $conn->query("SELECT listing_id, title, price, stock, image, hidden FROM listing WHERE seller_id = $seller_id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}
$conn->close();
?>

<style>
    .listing-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 12px #ccc;
    }

    .listing-container > div {
        text-align: center;
        margin-bottom: 15px;
    }

    .add-listing-btn {
        background-color: #ff2256;
        color: #fff;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 18px;
        display: inline-block;
    }

    .add-listing-btn:hover {
        background-color: #d11744;
    }

    h1 {
        color: #ff2256;
        text-align: center;
        margin-top: 0;
    }

    .listings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        justify-items: center;
    }

    .listing-card {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: #fafafa;
        transition: box-shadow 0.2s ease;
        width: 100%;
        max-width: 280px;
    }

    .image-wrapper {
        position: relative;
        width: 100%;
        padding-top: 100%;
        overflow: hidden;
        cursor: pointer;
    }

    .image-wrapper img {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .listing-card .info {
        padding: 12px 15px;
        text-align: center;
    }

    .listing-card h4 {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #333;
        line-height: 1.2;
    }

    .listing-card p {
        margin: 4px 0;
        font-size: 15px;
        color: #555;
    }

    .simple-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 10px;
    }

    .icon-simple {
        width: 20px;
        height: 20px;
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }

    .icon-simple:hover {
        opacity: 1;
    }

    /* Hidden listing styling */
    .hidden-listing {
        opacity: 0.5;
        filter: grayscale(80%);
        position: relative;
    }

    .hidden-listing::after {
        content: "Hidden";
        position: absolute;
        top: 8px;
        left: 8px;
        background: #ff2256;
        color: white;
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 4px;
        font-weight: bold;
    }
</style>

<div class="listing-container">
    <br><h1>My Listings</h1>
    <div>
        <a href="add-listing.php" class="add-listing-btn">+ Add New Listing</a><br><br>
    <div class="listings-grid">
        <?php foreach ($listings as $listing): ?>
            <div class="listing-card <?php echo ($listing['hidden'] ? 'hidden-listing' : ''); ?>">
                <div class="image-wrapper" onclick="window.location.href='edit-listing.php?listing_id=<?php echo $listing['listing_id']; ?>'">
                    <img src="uploads/<?php echo htmlspecialchars($listing['image']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                </div>
                <div class="info">
                    <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                    <p>Price: RM <?php echo number_format($listing['price'], 2); ?></p>
                    <p>Stock: <?php echo (int)$listing['stock']; ?></p>
                    <div class="simple-actions">
                        <a href="edit-listing.php?listing_id=<?php echo $listing['listing_id']; ?>" title="Edit">
                            <img src="edit.png" alt="Edit" class="icon-simple">
                        </a>
                        <a href="toggle-hide-listing.php?listing_id=<?php echo $listing['listing_id']; ?>" onclick="return confirm('<?php echo $listing['hidden'] ? 'Unhide' : 'Hide'; ?> this listing?')" title="<?php echo $listing['hidden'] ? 'Unhide' : 'Hide'; ?>">
                            <img src="hide.png" alt="Hide" class="icon-simple">
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleEdit(icon) {
    const parentDiv = icon.parentElement;
    const span = parentDiv.querySelector('.display-text');
    const input = parentDiv.querySelector('input');

    if (input.hasAttribute('readonly')) {
        input.removeAttribute('readonly');
        input.classList.remove('hidden');
        span.classList.add('hidden');
        input.focus();
        icon.style.opacity = 1;
    } else {
        input.setAttribute('readonly', true);
        input.classList.add('hidden');
        span.classList.remove('hidden');
        icon.style.opacity = 0.7;
    }
}
</script>

<?php include("footer.php"); ?>
</body>
</html>
