<?php
require "db_connect.php";

$id = $_POST['id'];
$fullName = $_POST['fullName'];
$location = $_POST['location'];
$contact = $_POST['contact'];
$plan = $_POST['plan'];

$imagePath = null;
if (!empty($_FILES['profilePicture']['name'])) {
    $targetDir = "uploads/";
    $imagePath = $targetDir . basename($_FILES['profilePicture']['name']);
    if (!move_uploaded_file($_FILES['profilePicture']['tmp_name'], $imagePath)) {
        die("Failed to upload image.");
    }
    $sql = "UPDATE clients 
            SET fullName = '$fullName', location = '$location', contact = '$contact', plan = '$plan', image = '$imagePath' 
            WHERE id = $id";
} else {
    $sql = "UPDATE clients 
            SET fullName = '$fullName', location = '$location', contact = '$contact', plan = '$plan' 
            WHERE id = $id";
}

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
