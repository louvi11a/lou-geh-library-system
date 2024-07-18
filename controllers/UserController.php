<?php
include_once '../configs/db.php';

class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM readers WHERE username=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                if ($user['is_admin'] == 1) {
                    session_start();
                    $_SESSION['reader_number'] = $user['reader_number'];
                    $_SESSION['username'] = $user['username'];
                    return 'admin'; // Return 'admin' for admin user
                } else {
                    session_start();
                    $_SESSION['reader_number'] = $user['reader_number'];
                    $_SESSION['username'] = $user['username'];
                    return 'reader'; // Return 'reader' for regular user
                }
            } else {
                return 'error'; // Invalid password
            }
        } else {
            return 'error'; // Invalid username
        }
    }
    
    
    public function registerReader($username, $password, $family_name, $first_name, $city, $dob) {
        // Check if username already exists
        $check_sql = "SELECT reader_number FROM readers WHERE username=?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
    
        if ($check_result->num_rows > 0) {
            // Username exists, return an error message
            return "Reader already exists";
        } else {
            // Username does not exist, create new reader record
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO readers (username, password, family_name, first_name, city, dob) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssss", $username, $hashed_password, $family_name, $first_name, $city, $dob);
    
            if ($insert_stmt->execute()) {
                return "Registration successful";
            } else {
                return "Error registering reader: " . $insert_stmt->error;
            }
        }
    }

    // Other methods...

    public function editReader($reader_number, $family_name, $first_name, $city, $dob) {
        $sql = "UPDATE readers SET family_name=?, first_name=?, city=?, dob=? WHERE reader_number=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $family_name, $first_name, $city, $dob, $reader_number);
        
        if ($stmt->execute()) {
            return "Reader information updated successfully";
        } else {
            return "Error updating reader information: " . $stmt->error;
        }
    }

    public function deleteReader($reader_number) {
        $sql = "DELETE FROM readers WHERE reader_number=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $reader_number);
        
        if ($stmt->execute()) {
            return "Reader deleted successfully";
        } else {
            return "Error deleting reader: " . $stmt->error;
        }
    }

    public function searchReaders($keyword) {
        $sql = "SELECT * FROM readers WHERE family_name LIKE ? OR first_name LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $searchKeyword = "%{$keyword}%";
        $stmt->bind_param("ss", $searchKeyword, $searchKeyword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $readers = [];
            while ($row = $result->fetch_assoc()) {
                $readers[] = $row;
            }
            return $readers;
        } else {
            return [];
        }
    }
    public function getReaderProfile() {
        session_start();
        $reader_number = $_SESSION['reader_number'];
        $sql = "SELECT family_name, first_name, city, dob FROM readers WHERE reader_number=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $reader_number);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode([]);
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(["message" => "Logged out"]);
    }
    
    
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new UserController($conn);

    switch ($_POST['action']) {
        case 'login':
            $response = $controller->login($_POST['username'], $_POST['password']);
            echo $response;
            break;
        case 'getReaderProfile':
            $controller->getReaderProfile();
            break;
        case 'logout':
            $controller->logout();
            break;

    
        
        case 'registerReader':
            echo $controller->registerReader(
                $_POST['username'],
                $_POST['password'],
                $_POST['family_name'],
                $_POST['first_name'],
                $_POST['city'],
                $_POST['dob']
            );
            break;
        case 'editReader':
            echo $controller->editReader(
                $_POST['reader_number'],
                $_POST['family_name'],
                $_POST['first_name'],
                $_POST['city'],
                $_POST['dob']
            );
            break;
        case 'deleteReader':
            echo $controller->deleteReader($_POST['reader_number']);
            break;
        case 'searchReaders':
            echo json_encode($controller->searchReaders($_POST['keyword']));
            break;
        default:
            echo "Invalid action";
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $controller = new UserController($conn);

    if ($_GET['action'] == 'searchReaders') {
        echo json_encode($controller->searchReaders($_GET['keyword']));
    }
}
?>
