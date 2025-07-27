<?php
session_start();
include 'db.php';

// Get POST data safely
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Simple validation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($email) || empty($password)) {
        echo "Please enter both email and password.";
        exit;
    }

    // Prepare SQL
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // If you hashed password during sign-up, use password_verify
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];

                echo "Login successful! Welcome, " . htmlspecialchars($user['first_name']) . ".";
                // Redirect to homepage or dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                echo "Invalid email or password.";
            }
        } else {
            echo "No account found with that email.";
        }

        $stmt->close();
    } else {
        echo "Database error: " . $conn->error;
    }
}
?>
<form method="post" action="login.php">
  <input type="email" name="email" placeholder="Email" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Login</button>
</form>