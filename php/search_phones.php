<?php
header('Content-Type: application/json');
require_once 'db.php'; // Updated to use your db.php file name

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'data' => []
];

// Get search term and category from the request
$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Sanitize inputs
$search_query = trim($search_query);
$category = trim($category);

try {
    // Construct the base SQL query
    $sql = "SELECT phone_id, brand, model, price, stock, image_url FROM phones WHERE 1=1";
    $params = [];
    $types = '';

    // Add search query condition if it's not empty
    if (!empty($search_query)) {
        $sql .= " AND (LOWER(brand) LIKE ? OR LOWER(model) LIKE ?)";
        $search_term_lower = '%' . strtolower($search_query) . '%';
        $params[] = $search_term_lower;
        $params[] = $search_term_lower;
        $types .= 'ss';
    }

    // Add category condition if it's not 'All' or empty
    if (!empty($category) && $category !== 'All') {
        $sql .= " AND LOWER(brand) = LOWER(?)";
        $category_lower = strtolower($category);
        $params[] = $category_lower;
        $types .= 's';
    }

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters only if there are any
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if any results were found
    if ($result->num_rows > 0) {
        $phones = [];
        while ($row = $result->fetch_assoc()) {
            $phones[] = $row;
        }
        $response['success'] = true;
        $response['message'] = 'Phones found successfully.';
        $response['data'] = $phones;
    } else {
        $response['success'] = true;
        $response['message'] = 'No phones found matching the criteria.';
        $response['data'] = [];
    }

    $stmt->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>