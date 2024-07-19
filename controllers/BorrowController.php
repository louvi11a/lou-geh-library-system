<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../configs/db.php';

class BorrowController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getBorrowedBooks($reader_number) {
        $sql = "
            SELECT b.title, b.author, c.isbn, c.copy_number, br.return_date
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
            return json_encode($borrowedBooks);
        } else {
            error_log('Failed to prepare statement for getBorrowedBooks');
            return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        }
    }
    

    public function checkAvailability($isbn) {
        $sql = "SELECT COUNT(*) AS available_copies FROM copies WHERE isbn = ? AND copy_number NOT IN (SELECT copy_number FROM borrows WHERE return_date IS NULL)";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $stmt->bind_result($available_copies);
            $stmt->fetch();
            $stmt->close();
            return json_encode(['available' => $available_copies > 0]);
        } else {
            error_log('Failed to prepare statement for checkAvailability');
            return json_encode(['available' => false]);
        }
    }
    

    public function borrowBook($reader_number, $isbn) {
        $availability = $this->checkAvailability($isbn);
        $availability = json_decode($availability, true); // Convert JSON to array
    
        if ($availability['available']) {
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
                    $stmt->close();
                    return json_encode(['status' => 'success', 'message' => 'Book borrowed successfully.']);
                } else {
                    error_log('Failed to execute statement for borrowBook');
                    $stmt->close();
                    return json_encode(['status' => 'error', 'message' => 'Failed to borrow book.']);
                }
            } else {
                error_log('Failed to prepare statement for borrowBook');
                return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'No available copies to borrow.']);
        }
    }
    

    public function returnBook($borrow_id) {
        $sql = "UPDATE borrows SET return_date = CURDATE() WHERE borrow_id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $borrow_id);
            if ($stmt->execute()) {
                $stmt->close();
                return json_encode(['status' => 'success', 'message' => 'Book returned successfully.']);
            } else {
                error_log('Failed to execute statement for returnBook');
                $stmt->close();
                return json_encode(['status' => 'error', 'message' => 'Failed to return book.']);
            }
        } else {
            error_log('Failed to prepare statement for returnBook');
            return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
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
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}

