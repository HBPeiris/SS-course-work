<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "school";

$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT class_id, grade, section FROM class";
$result = $conn->query($sql);

$classes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Overview</title>
    <link rel="stylesheet" href="styles/home.css">
</head>
<body>
    <header>
        <h1>Welcome to the Student Management System</h1>
        <p>Select a grade to view its classes and students.</p>
    </header>

    <main>
        <section class="grades">
            <?php
            
            $grades = [];
            foreach ($classes as $class) {
                $grades[$class['grade']][] = $class;
            }

            
            foreach ($grades as $grade => $classList) {
                echo "<div class='grade'>";
                echo "<h2>Grade $grade</h2>";
                echo "<div class='classes'>";
                foreach ($classList as $class) {
                    echo "<a href='class-details.php?class_id={$class['class_id']}' class='class-box'>Class {$grade}{$class['section']}</a>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Student Management System. All rights reserved.</p>
    </footer>
</body>
</html>
