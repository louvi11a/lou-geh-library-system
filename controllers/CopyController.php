<?php
include_once '../configs/db.php';

class CopyController {
    public function assignCopy($copy_number, $isbn, $location, $shelf) {
        global $conn;
        $sql = "INSERT INTO copies (copy_number, isbn, location, shelf) 
                VALUES ('$copy_number', '$isbn', '$location', '$shelf')";
        if ($conn->query($sql) === TRUE) {
            return "Copy assigned successfully";
        } else {
            return "Error assigning copy: " . $conn->error;
        }
    }

    public function updateCopyLocation($copy_number, $location, $shelf) {
        global $conn;
        $sql = "UPDATE copies SET location='$location', shelf='$shelf' WHERE copy_number='$copy_number'";
        if ($conn->query($sql) === TRUE) {
            return "Copy location updated successfully";
        } else {
            return "Error updating copy location: " . $conn->error;
        }
    }

    public function markCopyBorrowed($copy_number) {
        global $conn;
        $sql = "UPDATE copies SET status='Borrowed' WHERE copy_number='$copy_number'";
        if ($conn->query($sql) === TRUE) {
            return "Copy marked as borrowed successfully";
        } else {
            return "Error marking copy as borrowed: " . $conn->error;
        }
    }

    public function markCopyReturned($copy_number) {
        global $conn;
        $sql = "UPDATE copies SET status='Available' WHERE copy_number='$copy_number'";
        if ($conn->query($sql) === TRUE) {
            return "Copy marked as returned successfully";
        } else {
            return "Error marking copy as returned: " . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new CopyController();

    switch ($_POST['action']) {
        case 'assignCopy':
            $response = $controller->assignCopy($_POST['copy_number'], $_POST['isbn'], $_POST['location'], $_POST['shelf']);
            echo $response;
            break;
        case 'updateCopyLocation':
            $response = $controller->updateCopyLocation($_POST['copy_number'], $_POST['location'], $_POST['shelf']);
            echo $response;
            break;
        case 'markCopyBorrowed':
            $response = $controller->markCopyBorrowed($_POST['copy_number']);
            echo $response;
            break;
        case 'markCopyReturned':
            $response = $controller->markCopyReturned($_POST['copy_number']);
            echo $response;
            break;
        // Add cases for other methods as needed
    }
}
?>
