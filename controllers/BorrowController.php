<?php
include_once '../configs/db.php';

class BorrowController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getBorrowedBooks() {
        session_start();
        $reader_number = $_SESSION['reader_number'];
        $sql = "SELECT b.title, b.author, b.isbn, c.copy_number, br.return_date 
                FROM borrowed_books br 
                JOIN copies c ON br.copy_number = c.copy_number AND br.isbn = c.isbn 
                JOIN books b ON c.isbn = b.isbn 
                WHERE br.reader_number=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $reader_number);
        $stmt->execute();
        $result = $stmt->get_result();

        $borrowedBooks = [];
        while ($row = $result->fetch_assoc()) {
            $borrowedBooks[] = $row;
        }
        echo json_encode($borrowedBooks);
    }

    public function getBorrowHistory() {
        session_start();
        $reader_number = $_SESSION['reader_number'];
        $sql = "SELECT b.title, b.author, b.isbn, br.borrow_date, br.return_date 
                FROM borrowed_books br 
                JOIN books b ON br.isbn = b.isbn 
                WHERE br.reader_number=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $reader_number);
        $stmt->execute();
        $result = $stmt->get_result();

        $borrowHistory = [];
        while ($row = $result->fetch_assoc()) {
            $borrowHistory[] = $row;
        }
        echo json_encode($borrowHistory);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new BorrowController($conn);

    switch ($_POST['action']) {
        case 'getBorrowedBooks':
            $controller->getBorrowedBooks();
            break;
        case 'getBorrowHistory':
            $controller->getBorrowHistory();
            break;
        default:
            echo "Invalid action";
            break;
    }
}
?>
