<?php

session_start();


include 'db_connection.php';


if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'admin') {
   
    header("Location: login.php");
    exit();
}


function getAdminLogs($conn) {
    try {
        
        $sql = "SELECT log_id, admins.username, activity, timestamp 
                FROM admin_activity_log 
                INNER JOIN admins ON admin_activity_log.admin_id = admins.admin_id
                ORDER BY timestamp DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        
        error_log("Database error: " . $e->getMessage()); 
        return [];
    }
}


$admin_logs = getAdminLogs($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .btn-back {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Activity Logs</h1>
    
    <?php if (!empty($admin_logs)): ?>
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Admin Username</th>
                    <th>Activity</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['activity']); ?></td>
                        <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No activity logs found.</p>
    <?php endif; ?>

    <a href="classroom.php" class="btn-back">Back to Dashboard</a>
</div>

</body>
</html>
