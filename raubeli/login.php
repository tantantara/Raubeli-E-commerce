<!DOCTYPE html>
<html>
<?php include("icon.php"); ?>
<head>
    <title>Login Page</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(255, 255, 255),rgb(241, 111, 170));
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            width: 700px;
            max-width: 90vw;
            overflow: hidden;
        }

        .branding {
            background-color:rgb(222, 60, 82);
            color: white;
            width: 300px;
            padding: 40px 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .branding img {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
            object-fit: contain;
        }

        .tagline {
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
        }

        .form-container {
            flex: 1;
            padding: 40px 50px;
            text-align: center;
        }

        b {
            display: block;
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0 20px 0;
            border: 1.8px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #ff2256;
            outline: none;
        }

        input[type="submit"].login-btn {
            background-color: #ff2256;
            color: white;
            font-weight: bold;
            border: none;
            padding: 12px 0;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"].login-btn:hover {
            background-color: #e01e4f;
        }

        .create-account-btn {
            display: inline-block;
            margin-top: 15px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .create-account-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 600px) {
            .container {
                flex-direction: column;
                width: 90vw;
            }
            .branding {
                width: 100%;
                padding: 30px 0;
            }
            .form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="branding">
            <img src="logo.png" alt="Brand Logo">
            <div class="tagline">Connecting Students<br>Through Commerce</div>
        </div>

        <div class="form-container">
            <form name="form1" method="post" action="login-process.php">
                <b>Login Page</b>
                <input name="email" type="email" id="email" placeholder="Email" required><br>
                <input name="password" type="password" id="password" placeholder="Password" required><br>
                <input type="submit" name="Submit" value="Login" class="login-btn">
            </form>

            <a href="signup.php" class="create-account-btn">Create New Account</a>
        </div>
    </div>

</body>
</html>
