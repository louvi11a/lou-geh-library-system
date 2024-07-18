<?php
include_once '../configs/db.php';

class BookController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function writeToConsole($message) {
        echo "<script>console.error('PHP Error: " . addslashes($message) . "');</script>";
    }

    public function addBook($isbn, $title, $author, $publication_year, $number_of_pages, $publisher_id, $category_ids) {
        $this->writeToConsole("Adding book with ISBN: $isbn, Title: $title, Author: $author");

        $check_sql = "SELECT isbn FROM books WHERE isbn = ?";
        $stmt_check = $this->conn->prepare($check_sql);
        $stmt_check->bind_param("s", $isbn);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            $this->writeToConsole("ISBN $isbn already exists.");
            return "Failed to add book. ISBN already exists.";
        }
        $stmt_check->close();

        $this->writeToConsole("ISBN $isbn is unique. Proceeding to insert.");

        $insert_sql = "INSERT INTO books (isbn, title, author, publication_year, number_of_pages, publisher_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($insert_sql);
        $stmt->bind_param("ssssii", $isbn, $title, $author, $publication_year, $number_of_pages, $publisher_id);

        if ($stmt->execute()) {
            $this->writeToConsole("Book inserted successfully with ISBN: $isbn");

            foreach ($category_ids as $category_id) {
                $check_category_sql = "SELECT category_id FROM categories WHERE category_id = ?";
                $stmt_category_check = $this->conn->prepare($check_category_sql);
                $stmt_category_check->bind_param("i", $category_id);
                $stmt_category_check->execute();
                $stmt_category_check->store_result();

                if ($stmt_category_check->num_rows == 0) {
                    $this->writeToConsole("Category with ID $category_id does not exist.");
                    continue;
                }
                $stmt_category_check->close();

                $insert_category_sql = "INSERT INTO book_categories (isbn, category_id) VALUES (?, ?)";
                $stmt_category = $this->conn->prepare($insert_category_sql);
                $stmt_category->bind_param("si", $isbn, $category_id);

                if ($stmt_category->execute()) {
                    $this->writeToConsole("Inserted category ID: $category_id for book ISBN: $isbn");
                } else {
                    $this->writeToConsole("Error inserting category ID: $category_id for book ISBN: $isbn: " . $stmt_category->error);
                }
                $stmt_category->close();
            }

            return "Book added successfully.";
        } else {
            $this->writeToConsole("Error adding book: " . $stmt->error);
            return "Error adding book: " . $stmt->error;
        }
    }


    public function editBook($isbn, $title, $author, $publication_year, $number_of_pages, $publisher_id) {
        $sql = "UPDATE books SET title=?, author=?, publication_year=?, number_of_pages=?, publisher_id=? WHERE isbn=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiii", $title, $author, $publication_year, $number_of_pages, $publisher_id, $isbn);

        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Book updated successfully.']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Error updating book: ' . $stmt->error]);
        }
    }

    public function deleteBook($isbn) {
        $sql = "DELETE FROM books WHERE isbn=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $isbn);

        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Book deleted successfully.']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'Error deleting book: ' . $stmt->error]);
        }
    }

    public function searchBooks($keyword) {
        $sql = "SELECT * FROM books WHERE isbn LIKE ? OR title LIKE ? OR author LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $keyword = "%$keyword%";
        $stmt->bind_param("sss", $keyword, $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        return json_encode($books);
    }

    public function getAllBooks() {
        $sql = "SELECT * FROM books";
        $result = $this->conn->query($sql);

        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        return json_encode($books);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new BookController($conn);

    switch ($_POST['action']) {
        case 'addBook':
            $category_ids = isset($_POST['category']) ? $_POST['category'] : [];
            echo $controller->addBook(
                $_POST['isbn'],
                $_POST['title'],
                $_POST['author'],
                $_POST['publication_year'],
                $_POST['number_of_pages'],
                $_POST['publisher_id'],
                $category_ids
            );
            break;

        case 'editBook':
            echo $controller->editBook(
                $_POST['isbn'],
                $_POST['title'],
                $_POST['author'],
                $_POST['publication_year'],
                $_POST['number_of_pages'],
                $_POST['publisher_id']
            );
            break;
        case 'deleteBook':
            echo $controller->deleteBook($_POST['isbn']);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Handle GET requests if necessary (e.g., for getAllBooks action)
}
