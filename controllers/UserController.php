<?php
include_once '../configs/db.php';

class UserController {
    public function registerReader($familyName, $firstName, $city, $dob) {
        global $conn;
        $sql = "INSERT INTO readers (family_name, first_name, city, dob) VALUES ('$familyName', '$firstName', '$city', '$dob')";
        if ($conn->query($sql) === TRUE) {
            echo "New reader registered successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new UserController();
    $controller->registerReader($_POST['family_name'], $_POST['first_name'], $_POST['city'], $_POST['dob']);
}
?>
