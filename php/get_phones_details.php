<?php
require_once 'db.php';

header('Content-Type: application/json'); 

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $phone_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($phone_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone ID.']);
        if (isset($conn) && $conn instanceof mysqli) { 
            $conn->close();
        }
        exit();
    }

    $sql = "SELECT phone_id, brand, model, color, price, stock, description, image_url FROM phones WHERE phone_id = ?";
    $stmt = $conn->prepare($sql); 

    if ($stmt) {
        $stmt->bind_param("i", $phone_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $phone = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $phone]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Phone not found.']);
        }
        $stmt->close();
    } else {
        error_log("Database query preparation failed: " . $conn->error); 
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No phone ID provided.']);
}

if (isset($conn) && $conn instanceof mysqli) { 
    $conn->close();
}
?>