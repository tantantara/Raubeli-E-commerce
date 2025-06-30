<?php
session_start();
include("icon.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Raubeli Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .top-header {
            background-color: rgb(222, 60, 82);
            color: white;
            display: flex;
            align-items: center;
            padding: 10px 30px;
        }

        .top-header img {
            height: 75px;
            width: auto;
        }

        .top-header .brand-name {
            font-size: 23px;
            font-weight: bold;
            margin-left: 20px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<!-- Top Branding Header -->
<div class="top-header">
    <a href="home.php">
        <img src="logo.png" alt="Raubeli Logo" />
    </a>
    <div class="brand-name">Connecting Students Through Commerce</div>
</div>

<?php

// Load role-based navigation
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
if ($role === 'user') {
    include("user-navigation.php");
} elseif ($role === 'seller') {
    include("seller-navigation.php");
} elseif ($role === 'admin') {
    include("admin-navigation.php");
}
?>
