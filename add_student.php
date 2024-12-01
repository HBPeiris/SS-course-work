<?php 
include 'db_connection.php';

session_start();

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $guardianName = htmlspecialchars(trim($_POST['guardianName']));
    $guardianPhone = htmlspecialchars(trim($_POST['guardianPhone']));
    $address = htmlspecialchars(trim($_POST['address']));

    
    if (
        empty($firstName) || empty($lastName) || empty($phone) ||
        empty($guardianName) || empty($guardianPhone) || empty($address)
    ) {
        $errorMessage = 'All fields are required.';
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $firstName) || !preg_match('/^[a-zA-Z ]+$/', $lastName)) {
        $errorMessage = 'First and Last Names must contain only letters and spaces.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone) || !preg_match('/^[0-9]{10}$/', $guardianPhone)) {
        $errorMessage = 'Phone numbers must be 10 digits.';
    }

    
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photoDir = 'uploads/';
        if (!file_exists($photoDir)) {
            mkdir($photoDir, 0777, true); 
        }

        
        $photoName = basename($_FILES['photo']['name']);
        $photoName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $photoName); 
        $photoPath = $photoDir . uniqid() . '_' . $photoName; 
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $errorMessage = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) { 
            $errorMessage = 'File size exceeds the 2MB limit.';
        } else {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                $photo = $photoPath; 
            } else {
                $errorMessage = 'Error: Failed to move the uploaded file.';
            }
        }
    }

    
    if (empty($errorMessage)) {
        try {
            $sql = "INSERT INTO students (first_name, last_name, phone, guardian_name, guardian_phone, address, photo) 
                    VALUES (:first_name, :last_name, :phone, :guardian_name, :guardian_phone, :address, :photo)";
            $stmt = $conn->prepare($sql);

            
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':guardian_name', $guardianName);
            $stmt->bindParam(':guardian_phone', $guardianPhone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':photo', $photo);

            $stmt->execute();

            $_SESSION['success_message'] = "Student added successfully!";
            echo "<script>
                    alert('Student registered successfully!');
                    window.location.href = 'classroom.php'; // Redirect to classroom page
                  </script>";
            exit();
        } catch (PDOException $e) {
            
            error_log($e->getMessage());
            $errorMessage = 'Error: Unable to add student. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
    <link rel="stylesheet" href="add_student.css">
</head>
<body>
    <div class="container">
        <h2>Add New Student</h2>

        
        <?php if (!empty($errorMessage)) { ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php } ?>

        
        <?php if (isset($_SESSION['success_message'])) { ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
        <?php } ?>

        
        <form action="add_student.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" placeholder="Enter student's first name" required>
            </div>
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" placeholder="Enter student's last name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter student's phone number" required>
            </div>
            <div class="form-group">
                <label for="guardianName">Guardian Name:</label>
                <input type="text" id="guardianName" name="guardianName" placeholder="Enter guardian's name" required>
            </div>
            <div class="form-group">
                <label for="guardianPhone">Guardian Phone:</label>
                <input type="tel" id="guardianPhone" name="guardianPhone" placeholder="Enter guardian's phone number" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" placeholder="Enter student's address" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Student Photo:</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
            <button type="submit">Add Student</button>
        </form>
    </div>
</body>
</html>
