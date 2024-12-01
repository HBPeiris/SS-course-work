<?php
include 'db_connection.php';

function validatePhoneNumber($phone) {
    // Validate phone number: must be between 10-15 digits.
    if (preg_match('/^\d{10,15}$/', $phone)) {
        return $phone;
    } else {
        return false;
    }
}

function validateName($name) {
    // Validate name: must only contain letters and spaces.
    if (preg_match('/^[a-zA-Z ]+$/', $name)) {
        return $name;
    } else {
        return false;
    }
}

$errors = [];

if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    try {
        // Fetch student details
        $sql = "SELECT * FROM students WHERE student_id = :student_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo "Student not found.";
            exit;
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $guardian_name = $_POST['guardian_name'];
        $guardian_phone = $_POST['guardian_phone'];
        $photo = $_FILES['photo'];

        // Validate the form fields
        $valid_first_name = validateName($first_name);
        if (!$valid_first_name) {
            $errors['first_name'] = "First name must contain only letters and spaces.";
        }

        $valid_last_name = validateName($last_name);
        if (!$valid_last_name) {
            $errors['last_name'] = "Last name must contain only letters and spaces.";
        }

        $valid_phone = validatePhoneNumber($phone);
        if (!$valid_phone) {
            $errors['phone'] = "Phone number must be between 10 to 15 digits.";
        }

        $valid_guardian_phone = validatePhoneNumber($guardian_phone);
        if (!$valid_guardian_phone) {
            $errors['guardian_phone'] = "Guardian phone number must be between 10 to 15 digits.";
        }

        // Check if there are any errors before proceeding
        if (empty($errors)) {
            try {
                // Handle photo upload
                $photo_path = $student['photo']; // Retain old photo if no new photo is uploaded
                if ($photo['error'] == 0) {
                    $photo_path = 'uploads/' . basename($photo['name']);
                    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
                        $errors['photo'] = "Error uploading photo.";
                    }
                }

                // If no errors, update the student's information
                if (empty($errors)) {
                    $sql = "UPDATE students SET 
                            first_name = :first_name, 
                            last_name = :last_name, 
                            address = :address, 
                            phone = :phone, 
                            guardian_name = :guardian_name, 
                            guardian_phone = :guardian_phone, 
                            photo = :photo 
                            WHERE student_id = :student_id";

                    $stmt = $conn->prepare($sql);

                    $stmt->bindValue(':first_name', $valid_first_name);
                    $stmt->bindValue(':last_name', $valid_last_name);
                    $stmt->bindValue(':address', $address);
                    $stmt->bindValue(':phone', $valid_phone);
                    $stmt->bindValue(':guardian_name', $guardian_name);
                    $stmt->bindValue(':guardian_phone', $valid_guardian_phone);
                    $stmt->bindValue(':photo', $photo_path);
                    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);

                    $stmt->execute();

                    // Fetch the updated student details
                    $sql = "SELECT * FROM students WHERE student_id = :student_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $student = $stmt->fetch(PDO::FETCH_ASSOC);

                    echo "Student details updated successfully!";
                }
            } catch (PDOException $e) {
                $errors['database'] = "Error updating student: " . $e->getMessage();
            }
        }
    }
} else {
    echo "Student ID not provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group input[type="file"] {
            padding: 5px;
        }

        .form-group button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-size: 12px;
        }

        .photo-preview {
            margin-top: 10px;
        }

        a.btn {
            padding: 10px 20px;
            background-color: #6c757d;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            margin-top: 10px;
        }

        a.btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Student Details</h1>
    <form action="edit_student.php?id=<?php echo $student['student_id']; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
            <?php if (isset($errors['first_name'])): ?>
                <div class="error"><?php echo $errors['first_name']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
            <?php if (isset($errors['last_name'])): ?>
                <div class="error"><?php echo $errors['last_name']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea name="address" id="address" rows="3" required><?php echo htmlspecialchars($student['address']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
            <?php if (isset($errors['phone'])): ?>
                <div class="error"><?php echo $errors['phone']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="guardian_name">Guardian Name</label>
            <input type="text" name="guardian_name" id="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="guardian_phone">Guardian Phone</label>
            <input type="text" name="guardian_phone" id="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone']); ?>" required>
            <?php if (isset($errors['guardian_phone'])): ?>
                <div class="error"><?php echo $errors['guardian_phone']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="photo">Student Photo</label>
            <input type="file" name="photo" id="photo">
            <?php if (isset($errors['photo'])): ?>
                <div class="error"><?php echo $errors['photo']; ?></div>
            <?php endif; ?>

            <?php if ($student['photo']): ?>
                <div class="photo-preview">
                    <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo" width="100">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <button type="submit">Update</button>
        </div>

        <div class="form-group">
            <a href="students.php" class="btn">Back to Students</a>
        </div>
    </form>
</div>

</body>
</html>
