<!DOCTYPE html>
<html lang="en">
  <?php include("icon.php"); ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Up</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f7f9fc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    form {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 15px rgba(0,0,0,0.1);
      width: 350px;
      max-width: 90vw;
      text-align: left;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      border: 1.8px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
      margin-top: 15px;
      transition: border-color 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: #ff2256;
      outline: none;
      box-shadow: 0 0 8px #ff2256aa;
      background-color: white;
    }
    label {
      display: block;
      margin-top: 15px;
      margin-bottom: 6px;
      font-weight: 600;
      color: #333;
    }
    select {
      width: 100%;
      padding: 10px 12px;
      border: 1.8px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
      margin-top: 8px;
      transition: border-color 0.3s ease;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background: white url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2212%22%20height%3D%227%22%20viewBox%3D%220%200%2012%207%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M6%207L0%200h12L6%207z%22%20fill%3D%22%23666%22/%3E%3C/svg%3E") no-repeat right 12px center;
      background-size: 12px 7px;
      cursor: pointer;
    }
    select:focus {
      border-color: #ff2256;
      outline: none;
      box-shadow: 0 0 8px #ff2256aa;
      background-color: white;
    }
    input[type="submit"] {
      margin-top: 25px;
      width: 100%;
      padding: 12px 0;
      background-color: #ff2256;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      font-size: 18px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    input[type="submit"]:hover {
      background-color: #e01e4f;
    }
  </style>
</head>
<body>
  <form method="POST" action="signup-process.php" style="position: relative;">
  <!-- Back button -->
  <button type="button" onclick="history.back()" 
    style="
      position: absolute;
      left: 15px;
      top: 15px;
      background-color: #ff2256;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      padding: 6px 12px;
      border-radius: 5px;
      box-shadow: 0 3px 6px rgba(255, 34, 86, 0.4);
      transition: background-color 0.3s ease;
      line-height: 1;
      user-select: none;
    "
    onmouseover="this.style.backgroundColor='#e01e4f'"
    onmouseout="this.style.backgroundColor='#ff2256'"
    aria-label="Go back"

    >
    &larr;
    </button>


  <h3 style="text-align: center; margin-bottom: 30px;">Sign Up</h3>

  <input id="name" type="text" name="name" placeholder="Name" required>

  <input id="email" type="email" name="email" placeholder="Email" required>

  <input id="number" type="text" name="number" placeholder="Phone Number" required>

  <input id="password" type="password" name="password" placeholder="Password" required>

  <label for="role">Select Role:</label>
  <select id="role" name="role" required>
    <option value="" disabled selected>Select Role</option>
    <option value="user">Customer</option>
    <option value="seller">Seller</option>
  </select>

  <input type="submit" name="submit" value="Sign Up">
</form>
</body>
</html>
