<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "databaseschool";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$title = $_POST['title'];
$points = $_POST['points'];
$grade = $_POST['grade'];
$student_id = $_POST['student_id'];
$img_option = $_POST['img_option'];
$video_option = $_POST['video_option'];
$date = $_POST['date'];

// Handle image
if ($img_option === 'upload' && isset($_FILES['img_upload'])) {
    $upload_dir = 'uploads/images/';
    $img_filename = basename($_FILES['img_upload']['name']);
    $upload_file = $upload_dir . $img_filename;

    if (move_uploaded_file($_FILES['img_upload']['tmp_name'], $upload_file)) {
        $img = $upload_file;
    } else {
        die("Image upload failed.");
    }
} elseif ($img_option === 'url' && !empty($_POST['img_url'])) {
    $img = $_POST['img_url'];
} else {
    $img = null;
}

// Handle video
if ($video_option === 'upload' && isset($_FILES['video_upload'])) {
    $upload_dir = 'uploads/videos/';
    $video_filename = basename($_FILES['video_upload']['name']);
    $upload_file = $upload_dir . $video_filename;

    if (move_uploaded_file($_FILES['video_upload']['tmp_name'], $upload_file)) {
        $video = $upload_file;
    } else {
        die("Video upload failed.");
    }
} elseif ($video_option === 'url' && !empty($_POST['video_url'])) {
    $video = $_POST['video_url'];
} else {
    $video = null;
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO projects (title, points, grade, student_id, img, video, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssss", $title, $points, $grade, $student_id, $img, $video, $date);

// Execute the statement
if ($stmt->execute()) {
    echo "New project added successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();
?>
