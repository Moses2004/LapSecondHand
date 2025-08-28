<?php


// Include database connection
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Response function
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    sendResponse(false, 'Database connection failed. Please try again later.');
}

// Validate and sanitize input data
$required_fields = ['brand', 'model', 'color', 'price', 'stock'];
$phone_data = [];

// Check required fields
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        sendResponse(false, "Field '{$field}' is required");
    }
    $phone_data[$field] = trim($_POST[$field]);
}

// Validate and sanitize data
$errors = [];

// Brand validation
if (strlen($phone_data['brand']) > 50) {
    $errors[] = 'Brand name must be less than 50 characters';
}

// Model validation
if (strlen($phone_data['model']) > 100) {
    $errors[] = 'Model name must be less than 100 characters';
}

// Color validation
if (strlen($phone_data['color']) > 50) {
    $errors[] = 'Color must be less than 50 characters';
}

// Price validation
$price = filter_var($phone_data['price'], FILTER_VALIDATE_FLOAT);
if ($price === false || $price < 0 || $price > 999999.99) {
    $errors[] = 'Price must be a valid number between 0 and 999,999.99';
}
$phone_data['price'] = number_format($price, 2, '.', '');

// Stock validation
$stock = filter_var($phone_data['stock'], FILTER_VALIDATE_INT);
if ($stock === false || $stock < 0 || $stock > 4294967295) {
    $errors[] = 'Stock must be a valid positive integer';
}
$phone_data['stock'] = $stock;

// Description (optional)
$phone_data['description'] = isset($_POST['description']) ? trim($_POST['description']) : '';

// Handle image upload
$image_url = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = handleImageUpload($_FILES['image']);
    if ($upload_result['success']) {
        $image_url = $upload_result['url'];
    } else {
        $errors[] = $upload_result['message'];
    }
}

// Check for validation errors
if (!empty($errors)) {
    sendResponse(false, implode('. ', $errors));
}

// Check for duplicate phone (same brand, model, color)
$check_sql = "SELECT phone_id FROM phones WHERE brand = ? AND model = ? AND color = ?";
$check_stmt = $conn->prepare($check_sql);

if ($check_stmt) {
    $check_stmt->bind_param("sss", $phone_data['brand'], $phone_data['model'], $phone_data['color']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(false, 'A phone with this brand, model, and color already exists');
    }
    $check_stmt->close();
} else {
    error_log("Duplicate check failed: " . $conn->error);
    sendResponse(false, 'Error checking for duplicates');
}

// Insert phone into database
$sql = "INSERT INTO phones (brand, model, color, price, stock, description, image_url, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sssdiss", 
        $phone_data['brand'],
        $phone_data['model'], 
        $phone_data['color'],
        $phone_data['price'],
        $phone_data['stock'],
        $phone_data['description'],
        $image_url
    );
    
    if ($stmt->execute()) {
        $phone_id = $conn->insert_id;
        sendResponse(true, 'Phone added successfully!', ['phone_id' => $phone_id]);
    } else {
        error_log("Insert failed: " . $stmt->error);
        sendResponse(false, 'Failed to add phone to database');
    }
    $stmt->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    sendResponse(false, 'Error preparing database query');
}

/**
 * Handle image upload
 */
function handleImageUpload($file) {
    // Updated path to use your uploadimages folder
    $upload_dir = '../uploadimages/';
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }
    }
    
    // Validate file type
    $file_type = mime_content_type($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed'];
    }
    
    // Validate file size
    if ($file['size'] > $max_file_size) {
        return ['success' => false, 'message' => 'File size must be less than 5MB'];
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('phone_', true) . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Optimize image (optional - requires GD extension)
        optimizeImage($upload_path, $file_type);
        
        return [
            'success' => true,
            'url' => $upload_path,
            'filename' => $new_filename
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to upload image'];
    }
}

/**
 * Optimize uploaded image (requires GD extension)
 */
function optimizeImage($file_path, $mime_type) {
    if (!extension_loaded('gd')) {
        return; // Skip optimization if GD extension is not available
    }
    
    $max_width = 800;
    $max_height = 600;
    $quality = 85;
    
    // Get original image dimensions
    list($orig_width, $orig_height) = getimagesize($file_path);
    
    // Calculate new dimensions
    $ratio = min($max_width / $orig_width, $max_height / $orig_height);
    
    if ($ratio < 1) {
        $new_width = (int)($orig_width * $ratio);
        $new_height = (int)($orig_height * $ratio);
        
        // Create new image resource
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Load original image based on type
        switch ($mime_type) {
            case 'image/jpeg':
                $orig_image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $orig_image = imagecreatefrompng($file_path);
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                break;
            case 'image/gif':
                $orig_image = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                $orig_image = imagecreatefromwebp($file_path);
                break;
            default:
                return;
        }
        
        if (!$orig_image) {
            return; // Failed to create image resource
        }
        
        // Resize image with high quality
        imagecopyresampled($new_image, $orig_image, 0, 0, 0, 0, 
                          $new_width, $new_height, $orig_width, $orig_height);
        
        // Save optimized image
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($new_image, $file_path, $quality);
                break;
            case 'image/png':
                imagepng($new_image, $file_path, 6);
                break;
            case 'image/gif':
                imagegif($new_image, $file_path);
                break;
            case 'image/webp':
                imagewebp($new_image, $file_path, $quality);
                break;
        }
        
        // Free memory
        imagedestroy($orig_image);
        imagedestroy($new_image);
    }
}

// Close database connection
$conn->close();
?>