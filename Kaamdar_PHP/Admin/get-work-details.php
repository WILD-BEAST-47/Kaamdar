<?php
session_start();
include('../dbConnection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to return JSON
header('Content-Type: application/json');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get request ID from POST data
$request_id = isset($_POST['request_id']) ? filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT) : null;

if(!$request_id) {
    echo json_encode(['error' => 'Invalid request ID']);
    exit;
}

// Fetch work details
$sql = "SELECT r.*, 
               CASE WHEN a.request_id IS NOT NULL THEN 'Assigned' ELSE 'Pending' END as request_status,
               t.empName as tech_name,
               a.assign_date
        FROM submitrequest_tb r
        LEFT JOIN assignwork_tb a ON r.request_id = a.request_id
        LEFT JOIN technician_tb t ON a.assign_tech = t.empName
        WHERE r.request_id = ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $request_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Format the data
        $data = [
            'request_id' => $row['request_id'],
            'requester_name' => $row['requester_name'],
            'request_date' => date('Y-m-d', strtotime($row['request_date'])),
            'request_info' => $row['request_info'],
            'request_desc' => $row['request_desc'],
            'request_status' => $row['request_status'],
            'tech_name' => $row['tech_name'],
            'assign_date' => $row['assign_date'] ? date('Y-m-d', strtotime($row['assign_date'])) : null,
            'requester_add1' => $row['requester_add1'],
            'requester_add2' => $row['requester_add2'],
            'requester_city' => $row['requester_city'],
            'requester_state' => $row['requester_state'],
            'requester_zip' => $row['requester_zip'],
            'requester_mobile' => $row['requester_mobile'],
            'requester_email' => $row['requester_email']
        ];
        
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Work order not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 