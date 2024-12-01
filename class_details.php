<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "school";

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize class_id parameter
if (isset($_GET['class_id']) && is_numeric($_GET['class_id'])) {
    $class_id = (int)$_GET['class_id']; // Cast to integer to prevent injection
} else {
    die("Invalid class ID.");
}

// Query to get class details
$classQuery = "SELECT grade, section FROM class WHERE class_id = ?";
$stmt = $conn->prepare($classQuery);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$classResult = $stmt->get_result();

// Check if class details are found
if ($classResult->num_rows > 0) {
    $classDetails = $classResult->fetch_assoc();
} else {
    die("Class not found.");
}

// Query to get students in the class
$studentQuery = "SELECT * FROM student WHERE class_id = ?";
$stmt = $conn->prepare($studentQuery);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$studentResult = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class <?php echo htmlspecialchars($classDetails['grade'] . $classDetails['section']); ?> Students</title>
    <link rel="stylesheet" href="styles/class.css">
</head>
<body>
    <header>
        <h1>Students in Class <?php echo htmlspecialchars($classDetails['grade'] . $classDetails['section']); ?></h1>
    </header>

    <main>
        <section class="students">
            <?php
            if ($studentResult->num_rows > 0) {
                while ($student = $studentResult->fetch_assoc()) {
                    echo "<div class='student-box'>";
                    echo "<img src='uploads/" . htmlspecialchars($student['photo']) . "' alt='Student Photo'>";
                    echo "<p>" . htmlspecialchars($student['first_name']) . " " . htmlspecialchars($student['last_name']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No students found for this class.</p>";
            }
            ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Student Management System</p>
    </footer>
</body>
</html>
