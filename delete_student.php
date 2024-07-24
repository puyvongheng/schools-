<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "databaseschool";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Delete student record
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        header("Location: add_student_project.php");
        exit();
    } else {
        die("Error: " . htmlspecialchars($stmt->error));
    }

    $stmt->close();
}

$conn->close();
?>
