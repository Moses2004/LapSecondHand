<?php
require_once 'db.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    $sql = "SELECT phone_id, brand, model, price, image_url, stock FROM phones WHERE stock > 0 ORDER BY created_at DESC LIMIT 10"; // နောက်ဆုံးထည့်ထားတဲ့ ၁၀ လုံးကို ပြပါ
    $result = $conn->query($sql);

    if ($result) {
        $phones = [];
        while ($row = $result->fetch_assoc()) {
            $phones[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $phones;
        $response['message'] = 'Featured phones loaded successfully.';
    } else {
        $response['message'] = 'Error fetching phones: ' . $conn->error;
        error_log("Error fetching phones: " . $conn->error);
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    error_log("Exception in get_phone.php: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
?>