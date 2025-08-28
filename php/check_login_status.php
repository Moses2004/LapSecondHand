<?php
session_start();
header('Content-Type: application/json');

$response = ['logged_in' => false];

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $response['logged_in'] = true;
}

echo json_encode($response);
?>