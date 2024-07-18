<?php
include_once '../configs/db.php';

class BorrowController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getBorrowedBooks($readerNumber) {
        $query = "
            SELECT b.title, b.author, b.isbn, bc.copy_number, br.return_date
            FROM borrows br
            JOIN books b ON br.isbn = b.isbn
            JOIN copies bc ON br.copy_number = bc.copy_number
            WHERE br.reader_number = ?
        ";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $readerNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $books = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return json_encode($books);
        } else {
            return json_encode(["error" => "Failed to prepare statement"]);
        }
    }

    public function getBorrowHistory($readerNumber) {
        $sql = "
            SELECT b.title, b.author, b.isbn, br.borrow_date, br.return_date
            FROM borrows br
            JOIN books b ON br.isbn = b.isbn
            WHERE br.reader_number = ?
        ";
        
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("i", $readerNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $borrowHistory = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return json_encode($borrowHistory);
        } else {
            return json_encode(["error" => "Failed to prepare statement"]);
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $controller = new BorrowController($conn);

    session_start();
    $readerNumber = $_SESSION['reader_number'];

    switch ($_POST['action']) {
        case 'getBorrowedBooks':
            echo $controller->getBorrowedBooks($readerNumber);
            break;
        case 'getBorrowHistory':
            echo $controller->getBorrowHistory($readerNumber);
            break;
        default:
            header('Content-Type: application/json');
            echo json_encode(["error" => "Invalid action"]);
            break;
    }
}
?>
