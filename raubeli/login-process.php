<?php
session_start();
include("raubeli.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query customer by email
    $sql_customer = "SELECT * FROM customer WHERE email = '$email'";
    $result_customer = mysqli_query($conn, $sql_customer);

    // Query seller by email
    $sql_seller = "SELECT * FROM seller WHERE email = '$email'";
    $result_seller = mysqli_query($conn, $sql_seller);

    // Query admin by email
    $sql_admin = "SELECT * FROM admin WHERE email = '$email'";
    $result_admin = mysqli_query($conn, $sql_admin);

    $customer = mysqli_num_rows($result_customer) === 1 ? mysqli_fetch_assoc($result_customer) : null;
    $seller = mysqli_num_rows($result_seller) === 1 ? mysqli_fetch_assoc($result_seller) : null;
    $admin = mysqli_num_rows($result_admin) === 1 ? mysqli_fetch_assoc($result_admin) : null;

    // If no user found at all
    if (!$customer && !$seller && !$admin) {
        echo "<script>
            alert('Invalid email or password.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }

    // Check passwords
    $customer_pass_ok = $customer && $customer['password'] === $password;
    $seller_pass_ok = $seller && $seller['password'] === $password;
    $admin_pass_ok = $admin && $admin['password'] === $password;

    // If no valid password match
    if (!$customer_pass_ok && !$seller_pass_ok && !$admin_pass_ok) {
        echo "<script>
            alert('Invalid email or password.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }

    // Admin login
    if ($admin_pass_ok && $admin['name'] === "admin") {
        $_SESSION['username'] = "admin";
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: home.php");
        exit();
    }

    // If user exists in multiple roles with correct password for both, ask to choose
    $valid_roles = [];
    if ($customer_pass_ok) $valid_roles['user'] = ['id' => $customer['customer_id'], 'name' => $customer['name']];
    if ($seller_pass_ok) $valid_roles['seller'] = ['id' => $seller['seller_id'], 'name' => $seller['name']];
    if ($admin_pass_ok) $valid_roles['admin'] = ['id' => $admin['admin_id'], 'name' => $admin['name']];

    if (count($valid_roles) > 1) {
        $_SESSION['email_temp'] = $email;
        $_SESSION['valid_roles_temp'] = $valid_roles;
        header("Location: choose_role.php");
        exit();
    }

    // If exactly one role matches, log in directly
    if ($customer_pass_ok && !$seller_pass_ok && !$admin_pass_ok) {
        $_SESSION['username'] = $customer['name'];
        $_SESSION['role'] = 'user';
        $_SESSION['customer_id'] = $customer['customer_id'];
        header("Location: home.php");
        exit();
    }

    if ($seller_pass_ok && !$customer_pass_ok && !$admin_pass_ok) {
        $_SESSION['username'] = $seller['name'];
        $_SESSION['role'] = 'seller';
        $_SESSION['seller_id'] = $seller['seller_id'];
        header("Location: seller-profile.php");
        exit();
    }

    if ($admin_pass_ok && !$customer_pass_ok && !$seller_pass_ok) {
        $_SESSION['username'] = $admin['name'];
        $_SESSION['role'] = 'admin';
        $_SESSION['admin_id'] = $admin['admin_id'];
        header("Location: admin-profile.php");
        exit();
    }

    // Catch-all fallback
    echo "<script>
        alert('An error occurred. Please try again.');
        window.location.href = 'login.php';
    </script>";
    exit();
}
?>
