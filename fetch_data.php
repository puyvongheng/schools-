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

// Define the number of results per page
$rows_per_page = 10;

// Check if the filter is applied
$filter_with_projects = isset($_GET['filter_with_projects']) ? $_GET['filter_with_projects'] : 'off';

// Determine the SQL WHERE clause based on the filter
$where_clause = $filter_with_projects == 'on' ? "WHERE projects.id IS NOT NULL" : "";

// Find out the number of results stored in the database
$result = $conn->query("SELECT COUNT(DISTINCT students.id) AS total FROM students
                        LEFT JOIN projects ON students.id = projects.student_id
                        $where_clause");

if (!$result) {
    die("Query failed: " . $conn->error);
}

$row = $result->fetch_assoc();
$nr_of_rows = $row['total'];

// Determine the total number of pages available
$pages = ceil($nr_of_rows / $rows_per_page);

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$start = ($page - 1) * $rows_per_page;

// Handle sorting/filtering
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'project_id'; // Default sort
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'asc'; // Default order
$valid_sort_columns = ['student_name', 'points', 'grade', 'project_id']; // Allowable sort columns
$valid_sort_orders = ['asc', 'desc']; // Allowable sort orders

if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'project_id'; // Fallback if invalid sort column
}
if (!in_array($sort_order, $valid_sort_orders)) {
    $sort_order = 'asc'; // Fallback if invalid sort order
}

// Retrieve the selected results from the database
$sql = "SELECT projects.id AS project_id, students.name AS student_name, projects.title, projects.points, projects.grade
        FROM students
        LEFT JOIN projects ON students.id = projects.student_id
        $where_clause
        ORDER BY $sort_by $sort_order
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $rows_per_page);
$stmt->execute();
$projects_result = $stmt->get_result();

// Calculate the showing range
$end = min(($start + $rows_per_page), $nr_of_rows);

// SQL query to fetch students and their associated projects
$students_sql = "SELECT students.id AS student_id, students.name AS student_name, COUNT(projects.id) AS total_projects
                 FROM students
                 LEFT JOIN projects ON students.id = projects.student_id
                 GROUP BY students.id, students.name";
if ($filter_with_projects == 'on') {
    $students_sql .= " HAVING total_projects > 0";
}
$students_result = $conn->query($students_sql);

// Count total students
$total_students_sql = "SELECT COUNT(*) AS total_students FROM students";
$total_students_result = $conn->query($total_students_sql);
$total_students_row = $total_students_result->fetch_assoc();
$total_students = $total_students_row['total_students'];

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Projects</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .total-projects {
            margin-bottom: 20px;
        }
        body {
            padding-top: 56px; /* Adjust if needed to avoid content being hidden under the navbar */
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
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
        <h2>Welcome to the Student Projects Database</h2>

        <!-- Total Students -->
        <div class="alert alert-info">
            <strong>Total Students:</strong> <?php echo htmlspecialchars($total_students); ?>
        </div>
        
        <!-- Filter and Sort -->
        <form method="get" class="mb-3">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <label class="mr-2" for="sort_by">Sort By:</label>
                    <select name="sort_by" id="sort_by" class="form-control" onchange="this.form.submit()">
                        <option value="student_name" <?php if ($sort_by == 'student_name') echo 'selected'; ?>>Name</option>
                        <option value="points" <?php if ($sort_by == 'points') echo 'selected'; ?>>Points</option>
                        <option value="grade" <?php if ($sort_by == 'grade') echo 'selected'; ?>>Grade</option>
                        <option value="project_id" <?php if ($sort_by == 'project_id') echo 'selected'; ?>>Project ID</option>
                    </select>
                </div>
                <div class="col-auto mt-2">
                    <label class="mr-2" for="sort_order">Order:</label>
                    <select name="sort_order" id="sort_order" class="form-control" onchange="this.form.submit()">
                        <option value="asc" <?php if ($sort_order == 'asc') echo 'selected'; ?>>A to Z</option>
                        <option value="desc" <?php if ($sort_order == 'desc') echo 'selected'; ?>>Z to A</option>
                    </select>
                </div>
                <div class="col-auto mt-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="filter_with_projects" name="filter_with_projects" value="on" <?php if ($filter_with_projects == 'on') echo 'checked'; ?>>
                        <label class="form-check-label" for="filter_with_projects">Hide students with no projects</label>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Display projects -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Student Name</th>
                    <th>Project Title</th>
                    <th>Points</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($projects_result->num_rows > 0) {
                    while ($row = $projects_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['project_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['points']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
                        echo "<td>";
                        echo "<a href='edit_project.php?id=" . htmlspecialchars($row['project_id']) . "' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Edit</a> ";
                        echo "<a href='delete_project.php?id=" . htmlspecialchars($row['project_id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this project?\")'><i class='fas fa-trash-alt'></i> Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Showing X of Y -->
        <div class="mb-3">
            Showing <?php echo $start + 1; ?> to <?php echo $end; ?> of <?php echo $nr_of_rows; ?>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&sort_by=<?php echo htmlspecialchars($sort_by); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>&filter_with_projects=<?php echo htmlspecialchars($filter_with_projects); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&sort_by=<?php echo htmlspecialchars($sort_by); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>&filter_with_projects=<?php echo htmlspecialchars($filter_with_projects); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&sort_by=<?php echo htmlspecialchars($sort_by); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>&filter_with_projects=<?php echo htmlspecialchars($filter_with_projects); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
