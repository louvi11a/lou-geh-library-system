<?php
// Ensure session is started only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../configs/db.php';

class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM readers WHERE username=?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['status' => 'error', 'message' => 'SQL prepare error: ' . $this->conn->error]);
        }
    
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['reader_number'] = $user['reader_number'];
                $_SESSION['username'] = $user['username'];
                return json_encode(['status' => 'success', 'role' => $user['is_admin'] == 1 ? 'admin' : 'reader']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'Invalid username']);
        }
    }
    
    
    
    public function getReaderProfile() {
        if (isset($_SESSION['reader_number'])) {
            $readerNumber = $_SESSION['reader_number'];
            $sql = "SELECT * FROM readers WHERE reader_number=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $readerNumber); // Use "i" for integer
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                echo json_encode($user);
            } else {
                echo json_encode(['error' => 'Reader profile not found']);
            }
        } else {
            echo json_encode(['error' => 'Not logged in']);
        }
    }
    
    
    
    
    public function registerReader($username, $password, $family_name, $first_name, $city, $dob) {
        $check_sql = "SELECT reader_number FROM readers WHERE username=?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
    
        if ($check_result->num_rows > 0) {
            return json_encode(['status' => 'error', 'message' => 'Reader already exists']);
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO readers (username, password, family_name, first_name, city, dob) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssss", $username, $hashed_password, $family_name, $first_name, $city, $dob);
    
            if ($insert_stmt->execute()) {
                return json_encode(['status' => 'success', 'message' => 'Registration successful']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Error registering reader: ' . $insert_stmt->error]);
            }
        }
    }


    
    public function logout() {
        session_destroy();
        echo json_encode(["message" => "Logged out"]);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new UserController($conn);

    switch ($_POST['action']) {
        case 'login':
            echo $controller->login($_POST['username'], $_POST['password']);
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
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $controller = new UserController($conn);

    if ($_GET['action'] == 'searchReaders') {
        echo json_encode($controller->searchReaders($_GET['keyword']));
    }
}
?>
