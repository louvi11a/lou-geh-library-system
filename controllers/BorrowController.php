<?php
ob_start(); // Start output buffering
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../configs/db.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the database connection is successful
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

class BorrowController {
    private $conn;

    public function __construct($conn) {
        if ($conn->connect_error) {
            error_log('Database connection failed: ' . $conn->connect_error);
            die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
        }
        $this->conn = $conn;
        error_log('BorrowController initialized');
    }
    public function getBorrowedBooks($reader_number) {
        error_log('Fetching borrowed books for reader_number: ' . $reader_number);

        $sql = "
            SELECT br.borrow_id, b.title, b.author, c.isbn, c.copy_number, br.return_date
            FROM borrows br
            JOIN copies c ON br.copy_number = c.copy_number AND br.isbn = c.isbn
            JOIN books b ON c.isbn = b.isbn
            WHERE br.reader_number = ? AND br.return_date IS NULL
        ";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $reader_number);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowedBooks = [];
            while ($row = $result->fetch_assoc()) {
                $borrowedBooks[] = $row;
            }
            $stmt->close();
            error_log('Borrowed books fetched successfully');

            return json_encode($borrowedBooks);
        } else {
            error_log('Failed to prepare statement for getBorrowedBooks: ' . $this->conn->error);
            return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        }
    }
    
    public function checkAvailability($isbn) {
        $sql = "
            SELECT COUNT(*) AS available_count 
            FROM copies 
            WHERE isbn = ? AND copy_number NOT IN (
                SELECT copy_number FROM borrows WHERE return_date IS NULL
            )
        ";
    
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $stmt->bind_result($available_count);
            $stmt->fetch();
            $stmt->close();
    
            $availability = ['isbn' => $isbn, 'available' => $available_count > 0];
            return json_encode($availability);
        } else {
            error_log('Failed to prepare statement for checkAvailability: ' . $this->conn->error);
            return json_encode(['isbn' => $isbn, 'available' => false]);
        }
    }
    
    
    public function borrowBook($reader_number, $isbn) {
        error_log('borrowBook called with reader_number: ' . $reader_number . ' and isbn: ' . $isbn);
        error_log('Checking availability for ISBN: ' . $isbn);
    
        // Ensure $isbn is not empty
        if (empty($isbn)) {
            error_log('ISBN is not provided');
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No ISBN provided.']);
            exit;
        }
    
        // Call checkAvailability and decode the JSON response
        $availability = $this->checkAvailability($isbn);
        error_log('Availability result: ' . $availability);
        $availability = json_decode($availability, true); // Convert JSON to array
    
        // Ensure availability response is valid and contains the 'available' key
        if (is_array($availability) && isset($availability['available']) && $availability['available']) {
            $sql = "
                INSERT INTO borrows (reader_number, copy_number, isbn, borrow_date) 
                SELECT ?, copy_number, ?, CURDATE() 
                FROM copies 
                WHERE isbn = ? AND copy_number NOT IN (SELECT copy_number FROM borrows WHERE return_date IS NULL) 
                LIMIT 1
            ";
    
            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("iss", $reader_number, $isbn, $isbn);
                if ($stmt->execute()) {
                    error_log('Book borrowed successfully');
                    $stmt->close();
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'message' => 'Book borrowed successfully.']);
                    exit;
                } else {
                    error_log('Failed to execute statement for borrowBook: ' . $stmt->error);
                    $stmt->close();
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Failed to borrow book.']);
                    exit;
                }
            } else {
                error_log('Failed to prepare statement for borrowBook: ' . $this->conn->error);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
                exit;
            }
        } else {
            error_log('No available copies to borrow or invalid availability response');
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No available copies to borrow or invalid availability response.']);
            exit;
        }
    }
    
    
    public function returnBook($borrow_id) {
        error_log('returnBook called with borrow_id: ' . $borrow_id); // Log borrow_id
        $sql = "UPDATE borrows SET return_date = NOW() WHERE borrow_id = ?";
        
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $borrow_id);
            $stmt->execute();
            
            // Log affected rows and check if the update was successful
            error_log('Affected rows: ' . $stmt->affected_rows);
            
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return json_encode(["status" => "success", "message" => "Book returned successfully."]);
            } else {
                $stmt->close();
                error_log('No rows affected. Likely because the borrow_id does not exist.');
                return json_encode(["status" => "error", "message" => "Failed to return book."]);
            }
        } else {
            error_log('Failed to prepare statement for returnBook: ' . $this->conn->error);
            return json_encode(["status" => "error", "message" => "Failed to prepare statement"]);
        }
    }
    
    public function getBorrowHistory($reader_number) {
        $sql = "
            SELECT b.title, b.author, c.isbn, c.copy_number, br.borrow_date, br.return_date
            FROM borrows br
            JOIN copies c ON br.copy_number = c.copy_number AND br.isbn = c.isbn
            JOIN books b ON c.isbn = b.isbn
            WHERE br.reader_number = ?
        ";
    
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $reader_number);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowHistory = [];
            while ($row = $result->fetch_assoc()) {
                $borrowHistory[] = $row;
            }
            $stmt->close();
            return json_encode($borrowHistory);
        } else {
            error_log('Failed to prepare statement for getBorrowHistory: ' . $this->conn->error);
            return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        }
    }
    
    
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    error_log('POST request received with action: ' . $_POST['action']);
    $controller = new BorrowController($conn);

    switch ($_POST['action']) {
        case 'borrowBook':
            echo $controller->borrowBook($_POST['reader_number'], $_POST['isbn']);
            break;
        case 'returnBook':
            echo $controller->returnBook($_POST['borrow_id']);
            break;
        case 'getBorrowedBooks':
            echo $controller->getBorrowedBooks($_POST['reader_number']);
            break;
        case 'getBorrowHistory':
            echo $controller->getBorrowHistory($_POST['reader_number']);
            break;
        default:
            error_log('Invalid action: ' . $_POST['action']);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} else {
    error_log('Invalid request method');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}


?>
