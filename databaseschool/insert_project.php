<!DOCTYPE html>
<html>
<head>
    <title>Add Project</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
    <h2>Add Project</h2>
    <form action="insert_project_action.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="points">Points:</label>
            <input type="number" id="points" name="points" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="grade">Grade:</label>
            <input type="text" id="grade" name="grade" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="student_id">Select Student Name:</label>
            <select id="student_id" name="student_id" class="form-control" required>
                <option value="">Select a student</option>
                
                <?php
                // Fetch students from database
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "databaseschool";

                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $students_sql = "SELECT id, name FROM students";
                $students_result = $conn->query($students_sql);

                if ($students_result->num_rows > 0) {
                    while ($row = $students_result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No students available</option>";
                }

                $conn->close();
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="img_option">Image Option:</label>
            <select id="img_option" name="img_option" class="form-control" onchange="toggleImgInput()" required>
                <option value="url">Image URL</option>
                <option value="upload">Upload Image</option>
                <option value="none">No Image</option>
            </select>
        </div>

        <div class="form-group" id="img_url_div">
            <label for="img_url">Image URL:</label>
            <input type="text" id="img_url" name="img_url" class="form-control">
        </div>

        <div class="form-group" id="img_upload_div" style="display:none;">
            <label for="img_upload">Upload Image:</label>
            <input type="file" id="img_upload" name="img_upload" class="form-control">
        </div>

        <div class="form-group">
            <label for="video_option">Video Option:</label>
            <select id="video_option" name="video_option" class="form-control" onchange="toggleVideoInput()" required>
                <option value="url">Video URL</option>
                <option value="upload">Upload Video</option>
                <option value="none">No Video</option>
            </select>
        </div>

        <div class="form-group" id="video_url_div">
            <label for="video_url">Video URL:</label>
            <input type="text" id="video_url" name="video_url" class="form-control">
        </div>

        <div class="form-group" id="video_upload_div" style="display:none;">
            <label for="video_upload">Upload Video:</label>
            <input type="file" id="video_upload" name="video_upload" class="form-control">
        </div>

        <div class="form-group">
            <label for="date">Project Date:</label>
            <input type="date" id="date" name="date" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Add Project</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function toggleImgInput() {
    var imgOption = document.getElementById("img_option").value;
    if (imgOption === "upload") {
        document.getElementById("img_url_div").style.display = "none";
        document.getElementById("img_upload_div").style.display = "block";
    } else {
        document.getElementById("img_url_div").style.display = imgOption === "url" ? "block" : "none";
        document.getElementById("img_upload_div").style.display = "none";
    }
}

function toggleVideoInput() {
    var videoOption = document.getElementById("video_option").value;
    if (videoOption === "upload") {
        document.getElementById("video_url_div").style.display = "none";
        document.getElementById("video_upload_div").style.display = "block";
    } else {
        document.getElementById("video_url_div").style.display = videoOption === "url" ? "block" : "none";
        document.getElementById("video_upload_div").style.display = "none";
    }
}
</script>
</body>
</html>
