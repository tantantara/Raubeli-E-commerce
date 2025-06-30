<?php
include("raubeli.php");

if (isset($_POST['submit'])) {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['number']; 
    $password = $_POST['password'];

    if ($role === 'user') {
        $table = "customer";
        $idField = "customer_id";
    } elseif ($role === 'seller') {
        $table = "seller";
        $idField = "seller_id";
    } else {
        die("Invalid role selected.");
    }

    // Prepare and check for existing phone number
    $sqlPhone = "SELECT $idField FROM $table WHERE $idField = ?";
    $stmtPhone = mysqli_prepare($conn, $sqlPhone);
    mysqli_stmt_bind_param($stmtPhone, "s", $phone);
    mysqli_stmt_execute($stmtPhone);
    mysqli_stmt_store_result($stmtPhone);
    $phoneExists = mysqli_stmt_num_rows($stmtPhone);
    mysqli_stmt_close($stmtPhone);

    if ($phoneExists != 0) {
        echo "<script>
            alert('Record with this phone number already exists');
            window.location.href = 'signup.php';
        </script>";
        exit();
    }

    // Prepare and check for existing email in the same table (role)
    $sqlEmail = "SELECT email FROM $table WHERE email = ?";
    $stmtEmail = mysqli_prepare($conn, $sqlEmail);
    mysqli_stmt_bind_param($stmtEmail, "s", $email);
    mysqli_stmt_execute($stmtEmail);
    mysqli_stmt_store_result($stmtEmail);
    $emailExists = mysqli_stmt_num_rows($stmtEmail);
    mysqli_stmt_close($stmtEmail);

    if ($emailExists != 0) {
        echo "<script>
            alert('Email is already registered for this role');
            window.location.href = 'signup.php';
        </script>";
        exit();
    }

    // Insert new record
    $sqlInsert = "INSERT INTO $table (name, email, $idField, password) VALUES (?, ?, ?, ?)";
    $stmtInsert = mysqli_prepare($conn, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, "ssss", $name, $email, $phone, $password);

    if (mysqli_stmt_execute($stmtInsert)) {
        echo "<script>
            alert('Data has been saved');
            window.location.href = 'login.php';
        </script>";
        mysqli_stmt_close($stmtInsert);
        mysqli_close($conn);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
