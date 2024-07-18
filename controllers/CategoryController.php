<?php
include_once '../configs/db.php';

class CategoryController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addCategory($categoryName, $parentCategory) {
        $insert_sql = "INSERT INTO categories (name, parent_category_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($insert_sql);

        if (empty($parentCategory)) {
            $parentCategory = null;
        }

        $stmt->bind_param("si", $categoryName, $parentCategory);

        if ($stmt->execute()) {
            return "Category added successfully";
        } else {
            $this->writeToConsole("Error adding category: " . $stmt->error);
            return "Error adding category: " . $stmt->error;
        }
    }

    public function editCategory($id, $name) {
        $sql = "UPDATE categories SET name = ? WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);

        if ($stmt->execute()) {
            return "Category updated successfully";
        } else {
            return "Error updating category: " . $stmt->error;
        }
    }

    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return "Category deleted successfully";
        } else {
            return "Error deleting category: " . $stmt->error;
        }
    }

    public function writeToConsole($message) {
        echo "<script>console.error('PHP Error: " . addslashes($message) . "');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once '../configs/db.php';
    $controller = new CategoryController($conn);

    switch ($_POST['action']) {
        case 'addCategory':
            $response = $controller->addCategory($_POST['categoryName'], $_POST['parentCategory']);
            echo $response;
            break;
        case 'editCategory':
            $response = $controller->editCategory($_POST['id'], $_POST['name']);
            echo $response;
            break;
        case 'deleteCategory':
            $response = $controller->deleteCategory($_POST['id']);
            echo $response;
            break;
        // Add cases for other methods as needed
    }
}
?>
