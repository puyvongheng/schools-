<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "databaseschool";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch project data if ID is provided
if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT id, title, points, grade, student_id FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $project = $result->fetch_assoc();
    } else {
        die("Project not found.");
    }

    $stmt->close();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = intval($_POST['id']);
    $title = $_POST['title'];
    $points = intval($_POST['points']);
    $grade = $_POST['grade'];
    $student_id = intval($_POST['student_id']);

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE projects SET title = ?, points = ?, grade = ?, student_id = ? WHERE id = ?");
    $stmt->bind_param("siisi", $title, $points, $grade, $student_id, $project_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Project updated successfully</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Project</h2>
        <form action="edit_project.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($project['id']); ?>">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($project['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="points">Points:</label>
                <input type="number" id="points" name="points" class="form-control" value="<?php echo htmlspecialchars($project['points']); ?>" required>
            </div>
            <div class="form-group">
                <label for="grade">Grade:</label>
                <input type="text" id="grade" name="grade" class="form-control" value="<?php echo htmlspecialchars($project['grade']); ?>" required>
            </div>
            <div class="form-group">
                <label for="student_id">Select Student:</label>
                <select id="student_id" name="student_id" class="form-control" required>
                    <?php
                    // Fetch students for the dropdown
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    $students_sql = "SELECT id, name FROM students";
                    $students_result = $conn->query($students_sql);
                    
                    if ($students_result->num_rows > 0) {
                        while ($row = $students_result->fetch_assoc()) {
                            $selected = ($row['id'] == $project['student_id']) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Project</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
