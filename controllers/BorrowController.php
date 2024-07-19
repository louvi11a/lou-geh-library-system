<?php
include_once '../configs/db.php';

class BorrowController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function checkAvailability($isbn) {
        $sql = "
            SELECT COUNT(*) AS available_copies 
            FROM copies c 
            LEFT JOIN borrows b ON c.copy_number = b.copy_number AND b.return_date IS NULL 
            WHERE c.isbn = ? AND b.borrow_id IS NULL
        ";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $stmt->bind_result($available_copies);
            $stmt->fetch();
            $stmt->close();

            return $available_copies > 0;
        } else {
            return false;
        }
    }

    public function borrowBook($reader_number, $isbn) {
        $availability = $this->checkAvailability($isbn);

        if ($availability) {
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
                    $stmt->close();
                    return json_encode(['status' => 'error', 'message' => 'Failed to borrow book.']);
                }
            } else {
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
                $stmt->close();
                return json_encode(['status' => 'error', 'message' => 'Failed to return book.']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $controller = new BorrowController($conn);

    switch ($_POST['action']) {
        case 'borrowBook':
            echo $controller->borrowBook($_POST['reader_number'], $_POST['isbn']);
            break;
        case 'returnBook':
            echo $controller->returnBook($_POST['borrow_id']);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}
