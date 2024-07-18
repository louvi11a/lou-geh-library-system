<?php
include_once '../configs/db.php';

// Query to fetch categories
$sql = "SELECT category_id, name FROM categories";
$result = $conn->query($sql);

$categories = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Return categories as JSON
header('Content-Type: application/json');
echo json_encode($categories);

// Close database connection
$conn->close();
?>
