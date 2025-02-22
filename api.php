<?php
header("Content-Type: application/json");

// Expected API Key (Stored securely)
$valid_api_key = "0lMASqy7S1jSSVE98Vc2rNrbQkBUkzxw"; 

// Get API Key from headers
$headers = apache_request_headers();
$received_api_key = isset($headers['Authorization']) ? str_replace("Bearer ", "", $headers['Authorization']) : null;

// Validate API Key
if ($received_api_key !== $valid_api_key) {
    http_response_code(403); // 403 Forbidden
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Get request data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['employee_id'], $data['first_name'], $data['last_name'], $data['email'], $data['role'], $data['password'])) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["error" => "Invalid request data"]);
    exit;
}

// Database connection (Replace with your database credentials)
$host = "localhost";
$dbname = "hr3_mfinance";
$username = "hr3_mfinance";
$password = "bgn^C8sHe8k*aPC6";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

// Insert user into database
try {
    $stmt = $pdo->prepare("INSERT INTO users (employee_id, first_name, last_name, email, role, password) VALUES (:employee_id, :first_name, :last_name, :email, :role, :password)");
    $stmt->execute([
        ':employee_id' => $data['employee_id'],
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':email' => $data['email'],
        ':role' => $data['role'],
        ':password' => password_hash($data['password'], PASSWORD_BCRYPT), // Hash the password
    ]);

    http_response_code(201); // 201 Created
    echo json_encode(["message" => "User created successfully"]);
} catch (PDOException $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
