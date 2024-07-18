<?php
// AdminController.php

include_once '../configs/db.php';

class AdminController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function writeToConsole($message) {
        echo "<script>console.error('PHP Error: " . addslashes($message) . "');</script>";
    }


    public function addPublisher($name, $location) {
        $insert_sql = "INSERT INTO publishers (name, location) VALUES (?, ?)";
        $stmt = $this->conn->prepare($insert_sql);
        $stmt->bind_param("ss", $name, $location);

        if ($stmt->execute()) {
            return "Publisher added successfully";
        } else {
            $this->writeToConsole("Error adding publisher: " . $stmt->error);
            return "Error adding publisher: " . $stmt->error;
        }
    }


    public function addMember($username, $password, $family_name, $first_name, $city, $dob) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO readers (username, password, family_name, first_name, city, dob) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $username, $hashed_password, $family_name, $first_name, $city, $dob);

        if ($stmt->execute()) {
            return "Member added successfully";
        } else {
            $this->writeToConsole("Error adding member: " . $stmt->error);
            return "Error adding member: " . $stmt->error;
        }
    }

    public function viewReaders() {
        // Fetch readers from database and return as JSON
        $sql = "SELECT * FROM readers";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            $readers = [];
            while ($row = $result->fetch_assoc()) {
                $readers[] = $row;
            }
            return json_encode($readers);
        } else {
            return json_encode([]);
        }
    }

    // Add more methods for other admin actions (e.g., deleteBook, editPublisher, etc.)
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new AdminController($conn);

    switch ($_POST['action']) {        
        case 'addPublisher':
            echo $controller->addPublisher(
                $_POST['name'],
                $_POST['location']
            );
            break;
        case 'addMember':
            echo $controller->addMember(
                $_POST['username'],
                $_POST['password'],
                $_POST['family_name'],
                $_POST['first_name'],
                $_POST['city'],
                $_POST['dob']
            );
            break;
        case 'viewReaders':
            echo $controller->viewReaders();
            break;
        // Add cases for deleteBook, editPublisher, etc.
        default:
            echo "Invalid action";
            break;
    }
} else {
    echo "Invalid request method";
}
?>
