<?php
header("Content-Type: application/json");

// Expected API Key (Stored securely)
$valid_api_key = "0lMASqy7S1jSSVE98Vc2rNrbQkBUkzxw"; 

// Get API Key from headers
$headers = apache_request_headers();
$received_api_key = isset($headers['Authorization']) ? str_replace("Bearer ", "", $headers['Authorization']) : null;

// Validate API Key
if ($received_api_key !== $valid_api_key) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['employee_id'], $data['fName'], $data['lName'], $data['email'], $data['role'], $data['password_hash'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request data"]);
    exit;
}

// Store user in database (Example - Adjust for your DB)
$pdo = new PDO("mysql:host=localhost;dbname=hr3_mfinance", "hr3_mfinance", "bgn^C8sHe8k*aPC6");
$stmt = $pdo->prepare("INSERT INTO users (employee_id, fName, lName, email, role, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
$success = $stmt->execute([$data['employee_id'], $data['fName'], $data['lName'], $data['email'], $data['role'], password_hash($data['password_hash'], PASSWORD_BCRYPT)]);

// Send response
if ($success) {
    http_response_code(201);
    echo json_encode(["message" => "User created successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database error"]);
}
?>