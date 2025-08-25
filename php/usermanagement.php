<?php
// usermanagement.php
// This script now handles reading, creating, updating, and deleting user data from the 'users' table using MySQLi.

// Set header to return JSON content
header('Content-Type: application/json');

// Include the database connection file
require_once 'db.php'; 

// Get the requested action from POST or GET parameters
$action = $_REQUEST['action'] ?? '';

// Use a switch statement to handle different actions
switch ($action) {
    case 'read':
        readUsers($conn);
        break;
    case 'read_single': // New case for fetching a single user
        readSingleUser($conn);
        break;
    case 'create':
        createUser($conn);
        break;
    case 'update': // New case for updating a user
        updateUser($conn);
        break;
    case 'delete':
        deleteUser($conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid or no action specified.']);
        break;
}

/**
 * Reads all users from the database using MySQLi.
 * @param mysqli $conn The database connection object.
 */
function readUsers(mysqli $conn) {
    $query = "SELECT user_id, email, first_name, last_name, phone_number, role, timestamp FROM users ORDER BY user_id ASC";
    
    try {
        $result = $conn->query($query); 

        if ($result) {
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $result->free();

            if (count($users) > 0) {
                echo json_encode(['status' => 'success', 'users' => $users]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No users found in the database.']);
            }
        } else {
            error_log("Database Error in readUsers (query failed): " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve users due to a server-side database error.']);
        }
    } catch (Exception $e) {
        error_log("PHP Exception in readUsers: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred. Please check server logs.']);
    }
}

/**
 * Reads a single user's details from the database by ID using MySQLi.
 * @param mysqli $conn The database connection object.
 */
function readSingleUser(mysqli $conn) {
    $user_id = $_GET['user_id'] ?? 0; // Get user ID from GET request

    // Validate user_id
    if (!filter_var($user_id, FILTER_VALIDATE_INT) || $user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID provided.']);
        return;
    }

    $sql = "SELECT user_id, email, first_name, last_name, phone_number, role, timestamp FROM users WHERE user_id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare statement failed for readSingleUser: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Server error preparing statement.']);
            return;
        }

        $stmt->bind_param("i", $user_id); // 'i' for integer
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set
        $user = $result->fetch_assoc(); // Fetch a single row

        $stmt->close();

        if ($user) {
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        }
    } catch (Exception $e) {
        error_log("PHP Exception in readSingleUser: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred while fetching user details.']);
    }
}


/**
 * Creates a new user in the database using MySQLi prepared statements.
 * @param mysqli $conn The database connection object.
 */
function createUser(mysqli $conn) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($first_name) || empty($phone_number) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        return;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password, first_name, last_name, phone_number, role) VALUES (?, ?, ?, ?, ?, ?)";

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare statement failed for createUser: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Server error preparing statement.']);
            return;
        }
        $stmt->bind_param("ssssss", $email, $hashed_password, $first_name, $last_name, $phone_number, $role);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User created successfully.']);
        } else {
            if ($conn->errno === 1062) {
                echo json_encode(['status' => 'error', 'message' => 'Email address already registered.']);
            } else {
                error_log("Execute statement failed for createUser: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create user due to a database error.']);
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("PHP Exception in createUser: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred during user creation.']);
    }
}

/**
 * Updates an existing user in the database using MySQLi prepared statements.
 * @param mysqli $conn The database connection object.
 */
function updateUser(mysqli $conn) {
    $user_id = $_POST['user_id'] ?? 0;
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // This might be empty if user doesn't change it
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $role = $_POST['role'] ?? '';

    // Basic server-side validation for required fields
    if (empty($user_id) || empty($email) || empty($first_name) || empty($phone_number) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled for update.']);
        return;
    }

    // Validate user_id
    if (!filter_var($user_id, FILTER_VALIDATE_INT) || $user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID for update.']);
        return;
    }

    $sql_parts = [];
    $param_types = "";
    $params = [];

    // Add fields to update dynamically
    $sql_parts[] = "email = ?"; $param_types .= "s"; $params[] = $email;
    $sql_parts[] = "first_name = ?"; $param_types .= "s"; $params[] = $first_name;
    $sql_parts[] = "last_name = ?"; $param_types .= "s"; $params[] = $last_name;
    $sql_parts[] = "phone_number = ?"; $param_types .= "s"; $params[] = $phone_number;
    $sql_parts[] = "role = ?"; $param_types .= "s"; $params[] = $role;

    // Only update password if it's provided (i.e., user actually entered a new one)
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_parts[] = "password = ?";
        $param_types .= "s";
        $params[] = $hashed_password;
    }

    $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE user_id = ?";
    $param_types .= "i"; // Add 'i' for user_id which is an integer
    $params[] = $user_id;

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare statement failed for updateUser: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Server error preparing update statement.']);
            return;
        }

        // Use call_user_func_array to bind parameters dynamically
        $bind_names = array_merge([$param_types], $params);
        call_user_func_array([$stmt, 'bind_param'], refValues($bind_names));

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found or no changes made.']);
            }
        } else {
            if ($conn->errno === 1062) { // Duplicate entry error code
                echo json_encode(['status' => 'error', 'message' => 'Email address already registered for another user.']);
            } else {
                error_log("Execute statement failed for updateUser: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user due to a database error.']);
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("PHP Exception in updateUser: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred during user update.']);
    }
}

/**
 * Helper function for bind_param with dynamic arguments.
 * From php.net documentation.
 */
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}


/**
 * Deletes a user from the database using MySQLi prepared statements.
 * @param mysqli $conn The database connection object.
 */
function deleteUser(mysqli $conn) {
    $user_id = $_POST['user_id'] ?? 0;

    if (empty($user_id)) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required for deletion.']);
        return;
    }

    if (!filter_var($user_id, FILTER_VALIDATE_INT) || $user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid user ID for deletion.']);
        return;
    }

    $sql = "DELETE FROM users WHERE user_id = ?";

    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare statement failed for delete: " . $conn->error);
            echo json_encode(['status' => 'error', 'message' => 'Server error preparing delete statement.']);
            return;
        }

        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'User not found or already deleted.']);
            }
        } else {
            error_log("Execute statement failed for delete: " . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete user due to a database error.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("PHP Exception in deleteUser: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred during user deletion.']);
    }
}

// Close the database connection when the script finishes
$conn->close();
?>
