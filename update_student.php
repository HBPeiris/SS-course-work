<?php

include 'db_connection.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $student_id = $_POST['student_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $photo = $_FILES['photo']['name'];

    
    if ($photo) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($photo);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
    }

    try {
        
        $sql = "UPDATE students SET first_name = :first_name, last_name = :last_name, address = :address, 
                phone = :phone, guardian_name = :guardian_name, guardian_phone = :guardian_phone, 
                photo = :photo WHERE student_id = :student_id";

        $stmt = $conn->prepare($sql);

        
        $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':guardian_name', $guardian_name, PDO::PARAM_STR);
        $stmt->bindValue(':guardian_phone', $guardian_phone, PDO::PARAM_STR);
        $stmt->bindValue(':photo', $photo, PDO::PARAM_STR);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);

        
        $stmt->execute();

        
        header("Location: classroom.php");
        exit;

    } catch (PDOException $e) {
        
        die("Error: " . $e->getMessage());
    }
}
?>
