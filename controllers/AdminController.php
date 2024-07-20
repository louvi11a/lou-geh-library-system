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


    public function addUser($username, $password, $family_name, $first_name, $city, $dob) {
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

// AdminController.php

public function getCopiesCount($isbn) {
    // Database connection (adjust according to your setup)
    $pdo = new PDO('mysql:host=localhost;dbname=library', 'username', 'password');
    
    // Prepare and execute SQL query
    $stmt = $pdo->prepare('SELECT COUNT(*) AS copy_count FROM copies WHERE isbn = :isbn');
    $stmt->execute(['isbn' => $isbn]);
    
    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Return JSON response
    echo json_encode(['copy_count' => $result['copy_count']]);
}
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
        case 'addUser':
            echo $controller->addUser(
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
// Add this case to your switch statement in AdminController.php
case 'viewBorrowedBooks':
    $sql = "SELECT b.borrow_id, bk.title AS book_title, CONCAT(r.first_name, ' ', r.family_name) AS reader_name, b.borrow_date, b.return_date
            FROM borrows b
            JOIN books bk ON b.isbn = bk.isbn
            JOIN readers r ON b.reader_number = r.reader_number";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $borrowedBooks = array();
        while ($row = $result->fetch_assoc()) {
            $borrowedBooks[] = $row;
        }
        echo json_encode($borrowedBooks);
    } else {
        echo json_encode(array());
    }
    break;

        default:
            echo "Invalid action";
            break;
    }
} else {
    echo "Invalid request method";
}
?>
