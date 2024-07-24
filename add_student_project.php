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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_name = $_POST['name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO students (name) VALUES (?)");
    $stmt->bind_param("s", $student_name);

    // Execute the statement
    if ($stmt->execute()) {
        $student_id = $stmt->insert_id;
        $message = "New student added successfully.<br>ID: " . htmlspecialchars($student_id) . "<br>Name: " . htmlspecialchars($student_name);
    } else {
        $message = "Error: " . htmlspecialchars($stmt->error);
    }

    // Close the statement
    $stmt->close();
}

// Fetch sorting and pagination parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 10;

// Calculate the offset
$start = ($page - 1) * $rows_per_page;

// Fetch students with pagination and sorting
$sql = "SELECT id, name FROM students ORDER BY $sort LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $rows_per_page);
$stmt->execute();
$students_result = $stmt->get_result();

// Count total rows for pagination
$total_sql = "SELECT COUNT(*) AS total FROM students";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Student Projects</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="add_student_project.php">Add New Student</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="insert_project.php">Add New Project</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="fetch_data.php">View Student Projects</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <h2>Add Student</h2>
    <form action="add_student_project.php" method="POST">
        <div class="form-group">
            <label for="name">Student Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
    </form>

    <?php
    if (isset($message)) {
        echo "<div class='alert alert-info mt-3'>$message</div>";
    }
    ?>

    <h2 class="mt-4">Existing Students</h2>
    <form action="add_student_project.php" method="GET" class="form-inline mb-3">
        <div class="form-group mr-2">
            <label for="sort" class="mr-2">Sort By:</label>
            <select id="sort" name="sort" class="form-control" onchange="this.form.submit()">
                <option value="id" <?php echo $sort == 'id' ? 'selected' : ''; ?>>ID</option>
                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
            </select>
        </div>
    </form>

    <?php
    if ($students_result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $students_result->fetch_assoc()) {
            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>"
                . "ID: " . htmlspecialchars($row["id"]) . " - Name: " . htmlspecialchars($row["name"]) . " "
                . "<div>"
                . "<a href='edit_student.php?id=" . htmlspecialchars($row["id"]) . "' class='btn btn-warning btn-sm'>Edit</a> "
                . "<a href='delete_student.php?id=" . htmlspecialchars($row["id"]) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\")'>Delete</a>"
                . "</div>"
                . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No students found.</p>";
    }
    ?>

    <div class="mt-4">
        Showing <?php echo $start + 1; ?> to <?php echo min($start + $rows_per_page, $total_rows); ?> of <?php echo $total_rows; ?>
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-3">
            <!-- First Page -->
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=1&sort=<?php echo $sort; ?>" aria-label="First">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            </li>
            
            <!-- Previous Page -->
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>" aria-label="Previous">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>

            <!-- Page Numbers -->
            <?php
            $page_range = 2; // Number of page buttons to show on each side of the current page
            $start_page = max(1, $page - $page_range);
            $end_page = min($total_pages, $page + $page_range);

            for ($i = $start_page; $i <= $end_page; $i++) {
                echo "<li class='page-item " . ($i == $page ? 'active' : '') . "'>"
                    . "<a class='page-link' href='?page=$i&sort=$sort'>$i</a>"
                    . "</li>";
            }
            ?>

            <!-- Next Page -->
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>" aria-label="Next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            
            <!-- Last Page -->
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $total_pages; ?>&sort=<?php echo $sort; ?>" aria-label="Last">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
