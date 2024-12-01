<?php
include 'db_connection.php';

function validatePhoneNumber($phone) {
    
    if (preg_match('/^\d{10,15}$/', $phone)) {
        return $phone;
    } else {
        return false;
    }
}

function validateName($name) {
    
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

        
        if (empty($errors)) {
            try {
                
                $photo_path = $student['photo'];
                if ($photo['error'] == 0) {
                    $photo_path = 'uploads/' . basename($photo['name']);
                    if (!move_uploaded_file($photo['tmp_name'], $photo_path)) {
                        $errors['photo'] = "Error uploading photo.";
                    }
                }

                
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

                    
                    header("Location: classroom.php");
                    exit;
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
            <label for="guardian_name">Guardian's Name</label>
            <input type="text" name="guardian_name" id="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="guardian_phone">Guardian's Phone</label>
            <input type="text" name="guardian_phone" id="guardian_phone" value="<?php echo htmlspecialchars($student['guardian_phone']); ?>" required>
            <?php if (isset($errors['guardian_phone'])): ?>
                <div class="error"><?php echo $errors['guardian_phone']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="photo">Upload Photo</label>
            <input type="file" name="photo" id="photo" accept="image/*">
            <div class="photo-preview">
                <?php if ($student['photo']): ?>
                    <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Current Photo">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <button type="submit">Save Changes</button>
        </div>
    </form>

    <a href="classroom.php" class="btn-back">Back to Students</a>
</div>

</body>
</html>
