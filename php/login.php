<?php
session_start();
include 'db.php';

// Handle login submission
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: ../html/invoices.html");
                    } elseif ($user['role'] === 'customer') {
                        header("Location: ../html/index.html");
                    }
                    exit;
                } else {
                    $error_message = "Invalid email or password.";
                }
            } else {
                $error_message = "No account found with that email.";
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #edededff, #6E48AA);
    }

    header, footer {
      text-align: center;
      padding: 40px;
      color: white;
    }

    header {
      background: linear-gradient(to right, #003366, #cc0000);
      color: white;
      padding: 15px;
    }

    footer {
      background-color: #001F5B;
      font-size: 15px;
      padding: 20px;
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

   

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 160px);
      padding: 20px;
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

    .error {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
</style>
</head>
<body>

<header>
    </head>
<body>

<header>
  <img src="mit.jpg" class="logo" alt="Logo" />

  Lap Second Hand Phone Shop
  <div class="top-right-login">
    
  </div>
</header>
</header>

<div class="container">
    <div class="form-box">
        <h2>Login</h2>
        <?php if (!empty($error_message)) { ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php } ?>
        <form method="post" action="">
          Email  <input type="email" name="email" placeholder="Email" required>
           Password <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="login-link">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> LAPSECONDHAND Phone
</footer>

</body>
</html>
