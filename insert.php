<?php
require "db_connect.php";

$fullName = $_POST['fullName'];
$location = $_POST['location'];
$contact = $_POST['contact'];
$plan = $_POST['plan'];

// Handle file upload
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $targetDir = "uploads/";
    $imagePath = $targetDir . basename($_FILES['image']['name']);
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        die("Failed to upload image.");
    }
}

// Insert data
$sql = "INSERT INTO clients (fullName, location, contact, plan, image)
        VALUES ('$fullName', '$location', '$contact', '$plan', '$imagePath')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
