<?php
include_once '../configs/db.php';

class PublisherController {
    public function addPublisher($name, $location) {
        global $conn;
        $sql = "INSERT INTO publishers (name, location) VALUES ('$name', '$location')";
        if ($conn->query($sql) === TRUE) {
            return json_encode(["status" => "success", "message" => "New publisher added successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => "Error adding publisher: " . $conn->error]);
        }
    }


    public function getAllPublishers() {
        global $conn;
        $sql = "SELECT * FROM publishers";
        $result = $conn->query($sql);
        $publishers = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $publishers[] = $row;
            }
            return json_encode(["status" => "success", "data" => $publishers]);
        } else {
            return json_encode(["status" => "error", "message" => "No publishers found"]);
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json'); // Set content type to JSON
    $controller = new PublisherController();

    $response = null;
    switch ($_POST['action']) {
        case 'addPublisher':
            $response = $controller->addPublisher($_POST['name'], $_POST['location']);
            break;
        case 'getAllPublishers':
            $response = $controller->getAllPublishers();
            break;
        // Add cases for other methods as needed
    }
    
    echo $response;
}
?>
