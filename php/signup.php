<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone      = $_POST['phone'];
    $role       = 'customer';

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone_number, role) 
                            VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password, $phone, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Signup successful!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Signup</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #edededff, #6E48AA);
    }

    header, footer {
      text-align: center;
      padding: 25px;
      color: white;
    }

    header {
  background: linear-gradient(to right, #003366, #cc0000);
  color: white;
  padding: 25px;
    }

    footer {
      background-color: #001F5B;
      font-size: 14px;
    }

    .logo {
      position: absolute;
      top: 5px;
      left: 15px;
      height: 60px;
      border-radius: 50%;
    }

    .top-right-login {
      position: absolute;
      top:20px;
      right: 20px;
    }

    .top-right-login a {
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

.container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: calc(100vh - 160px); /* Adjust based on your header + footer height */
  padding: 20px;
}

    .left {
      flex: 1;
    }

    .right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-box {
      background: white;
      padding: 50px;
      border-radius: 30px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 350px;
    }

    h2 {
      text-align: center;
      color: #001F5B;
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 95%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 20px;
      border: none;
      background: #e0dede;
    }

    button {
      width: 100%;
      background: red;
      color: white;
      border: none;
      padding: 12px;
      margin-top: 10px;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
    }

    .login-link a {
      color: #001F5B;
      text-decoration: none;
    }
  </style>
</head>
<body>

<header>
  <img src="mit.jpg" class="logo" alt="Logo" />

  Lap Second Hand Phone Shop
  <div class="top-right-login">
    <a href="login.php">Login</a>
  </div>
</header>

<div class="container">

    <div class="form-box">
      <h2>Signup</h2>
      <form method="POST" action="signup.php">
        First name
        <input type="text" name="first_name" placeholder="Enter your first name" required>
        Last name<input type="text" name="last_name" placeholder="Enter your last name" required>
       Phone number <input type="text" name="phone" placeholder="Enter your phone number" required>
        Email<input type="email" name="email" placeholder="Enter your email" required>
        Password<input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit">Signup</button>
      </form>
      <div class="login-link">
        Already have account? <a href="login.php">Login</a>
      </div>
    </div>
  
</div>

<footer>
  Contact us: lap@secondhand
</footer>

</body>
</html>
