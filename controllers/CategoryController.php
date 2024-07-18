<?php
include_once '../configs/db.php';

class CategoryController {
    public function addCategory($categoryName, $parentCategory) {
        $insert_sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($insert_sql);
        $stmt->bind_param("si", $categoryName, $parentCategory);

        if ($stmt->execute()) {
            return "Category added successfully";
        } else {
            $this->writeToConsole("Error adding category: " . $stmt->error);
            return "Error adding category: " . $stmt->error;
        }
    }


    public function editCategory($id, $name) {
        global $conn;
        $sql = "UPDATE categories SET name='$name' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            return "Category updated successfully";
        } else {
            return "Error updating category: " . $conn->error;
        }
    }

    public function deleteCategory($id) {
        global $conn;
        $sql = "DELETE FROM categories WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            return "Category deleted successfully";
        } else {
            return "Error deleting category: " . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new CategoryController();

    switch ($_POST['action']) {
        case 'addCategory':
            $response = $controller->addCategory($_POST['name'], $_POST['parent_category_id']);
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
