<?php
include_once '../configs/db.php';

// Query to get all publishers
$sql = "SELECT publisher_id as id, name FROM publishers";
$result = $conn->query($sql);

$publishers = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $publishers[] = $row;
    }
}

// Return publishers as JSON
header('Content-Type: application/json');
echo json_encode($publishers);

// Close database connection
$conn->close();
?>
