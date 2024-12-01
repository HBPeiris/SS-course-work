<?php
include 'db_connection.php';

// Function to validate phone numbers (10-15 digits)
function validatePhoneNumber($phone) {
    return preg_match('/^\d{10,15}$/', $phone);
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

try {
    // Query to retrieve students from the database
    $sql = "SELECT student_id, first_name, last_name, address, phone, photo, guardian_name, guardian_phone FROM students";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_GET['delete'])) {
    // Ensure that a student ID is provided for deletion
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $student_id = $_GET['id'];

        // Validate if the student exists
        $checkSql = "SELECT * FROM students WHERE student_id = :student_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            try {
                // Deleting student record
                $deleteSql = "DELETE FROM students WHERE student_id = :student_id";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                $deleteStmt->execute();
                header("Location: student_records.php"); // Redirect after deletion
                exit;
            } catch (PDOException $e) {
                die("Error deleting student: " . $e->getMessage());
            }
        } else {
            die("Student not found.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classroom - Student Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        header {
            background-color: #343a40;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #343a40;
            color: white;
        }

        td img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover; 
        }

        .actions a {
            margin: 0 10px;
            text-decoration: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .edit-btn {
            background-color: #28a745;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .actions a:hover {
            opacity: 0.8;
        }

        .empty-table-message {
            text-align: center;
            padding: 20px;
            color: #777;
        }
    </style>
</head>
<body>

<header>
    <h1>Student Records - Classroom</h1>
</header>

<div class="container">
    <a href="admin_log.php" class="btn">View Admin Logs</a>
    <a href="add_student.php" class="btn">Add New Student</a>

    <?php if (empty($students)): ?>
        <div class="empty-table-message">
            <p>No students available in the classroom. Please add new students.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Guardian Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                foreach ($students as $student) {
                    echo '<tr>';
                    echo '<td>' . $counter++ . '</td>';
                    echo '<td><img src="' . htmlspecialchars($student['photo']) . '" class="student-photo" alt="Student Photo"></td>';
                    echo '<td>' . htmlspecialchars($student['first_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['last_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['phone']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['guardian_name']) . '</td>';
                    echo '<td class="actions">
    <a href="edit_student.php?id=' . $student['student_id'] . '" class="edit-btn">Edit</a>
    <a href="delete_student.php?id=' . $student['student_id'] . '" class="delete-btn" onclick="return confirm(\'Are you sure you want to delete this student?\')">Delete</a>
</td>
';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
