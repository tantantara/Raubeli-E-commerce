<style>
    .nav {
        background-color: #f2f2f2;
        padding: 15px 30px;
        display: flex;
        justify-content: flex-end; 
        align-items: center;
        position: relative;
        min-height: 55px;
    }

    .nav-search {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
    }

    .search-form {
        display: flex;
        align-items: center;
    }

    .search-form input[type="text"] {
        padding: 8px 12px;
        width: 700px;
        max-width: 100%;
        border: 1px solid #ccc;
        border-radius: 4px;
    }


    .search-form button {
        padding: 8px 14px;
        margin-left: 10px;
        background-color: #ff2256;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .search-form button:hover {
        background-color: #e01e4f;
    }

    .nav-right a, .nav-right button {
        margin-left: 15px;
        cursor: pointer;
    }

    .nav-right img {
        width: 35px;
        height: 35px;
        border-radius: 4px;
        transition: filter 0.3s ease;
    }

    .nav-right img:hover {
        filter: brightness(0.8);
    }

    .logout-btn {
        background-color: #ff2256;
        border: none;
        color: white;
        padding: 8px 14px;
        font-weight: bold;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .logout-btn:hover {
        background-color: #e01e4f;
    }
</style>

<div class="nav">
    <div class="nav-search">
        <form method="GET" action="search.php" class="search-form">
            <input 
                type="text" 
                name="query" 
                placeholder="Search products..." 
                value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
            >
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="nav-right">
        <a href="verified-seller.php" title="Verified Sellers">
            <img src="approve.png" alt="Approve">

        <a href="admin-reports.php" title="Reports">
            <img src="view-report.png" alt="Report">
        </a>
        <a href="admin-profile.php" title="My Profile">
            <img src="profile.png" alt="Profile">
        </a>
        <form method="POST" action="logout.php" style="display:inline;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</div>
