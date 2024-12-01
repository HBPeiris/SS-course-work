<?php
include 'db_connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $student_id = $_GET['id'];

    try {
        // Check if student exists
        $sqlCheck = "SELECT * FROM students WHERE student_id = :student_id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() === 0) {
            die("Student not found.");
        }

        // Proceed to delete student
        $sql = "DELETE FROM students WHERE student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect to classroom
        header("Location: classroom.php");
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());  // Log error
        die("An error occurred while processing the request.");
    }
} else {
    die("Invalid student ID.");
}
?>
