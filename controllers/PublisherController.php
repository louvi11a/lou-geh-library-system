<?php
include_once '../configs/db.php';

class PublisherController {
    public function addPublisher($name, $location) {
        global $conn;
        $sql = "INSERT INTO publishers (name, location) VALUES ('$name', '$location')";
        if ($conn->query($sql) === TRUE) {
            return "New publisher added successfully";
        } else {
            return "Error adding publisher: " . $conn->error;
        }
    }

    public function editPublisher($id, $name, $location) {
        global $conn;
        $sql = "UPDATE publishers SET name='$name', location='$location' WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            return "Publisher updated successfully";
        } else {
            return "Error updating publisher: " . $conn->error;
        }
    }

    public function deletePublisher($id) {
        global $conn;
        $sql = "DELETE FROM publishers WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            return "Publisher deleted successfully";
        } else {
            return "Error deleting publisher: " . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new PublisherController();

    switch ($_POST['action']) {
        case 'addPublisher':
            $response = $controller->addPublisher($_POST['name'], $_POST['location']);
            echo $response;
            break;
        case 'editPublisher':
            $response = $controller->editPublisher($_POST['id'], $_POST['name'], $_POST['location']);
            echo $response;
            break;
        case 'deletePublisher':
            $response = $controller->deletePublisher($_POST['id']);
            echo $response;
            break;
        // Add cases for other methods as needed
    }
}
?>
