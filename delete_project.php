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

// Process deletion
if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Project deleted successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>No project ID provided</div>";
}

$conn->close();

// Redirect back to the list of projects
header("Location: fetch_data.php");
exit();
?>
