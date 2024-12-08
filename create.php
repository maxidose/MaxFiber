<?php
require "db_connect.php";

// Initialize error messages and variables
$errors = [];
$fullName = $location = $contact = $plan = "";
$imagePath = "uploads/default.png";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $location = trim($_POST['location']);
    $contact = trim($_POST['contact']);
    $plan = $_POST['plan'];

    // Validate inputs
    if (empty($fullName)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($location)) {
        $errors[] = "Location is required.";
    }
    if (empty($contact)) {
        $errors[] = "Contact is required.";
    }
    if (empty($plan)) {
        $errors[] = "Plan selection is required.";
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $imageName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = $targetFilePath;
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO clients (fullName, location, contact, plan, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullName, $location, $contact, $plan, $imagePath);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to add client: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Add New Client</h1>

        <?php if (!empty($errors)) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="create.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
            <div class="mb-4">
                <label for="fullName" class="block text-gray-700 font-bold mb-2">Full Name</label>
                <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($fullName) ?>" class="border rounded w-full py-2 px-4" placeholder="Enter full name">
            </div>
            <div class="mb-4">
                <label for="location" class="block text-gray-700 font-bold mb-2">Location</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($location) ?>" class="border rounded w-full py-2 px-4" placeholder="Enter location">
            </div>
            <div class="mb-4">
                <label for="contact" class="block text-gray-700 font-bold mb-2">Contact</label>
                <input type="text" name="contact" id="contact" value="<?= htmlspecialchars($contact) ?>" class="border rounded w-full py-2 px-4" placeholder="Enter contact details">
            </div>
            <div class="mb-4">
                <label for="plan" class="block text-gray-700 font-bold mb-2">Plan</label>
                <select name="plan" id="plan" class="border rounded w-full py-2 px-4">
                    <option value="">Select a plan</option>
                    <option value="500|10mbps" <?= $plan === "500|10mbps" ? "selected" : "" ?>>500 | 10mbps</option>
                    <option value="1000|20mbps" <?= $plan === "1000|20mbps" ? "selected" : "" ?>>1000 | 20mbps</option>
                    <option value="1500|30mbps" <?= $plan === "1500|30mbps" ? "selected" : "" ?>>1500 | 30mbps</option>
                    <option value="2000|50mbps" <?= $plan === "2000|50mbps" ? "selected" : "" ?>>2000 | 50mbps</option>
                    <option value="3000|100mbps" <?= $plan === "3000|100mbps" ? "selected" : "" ?>>3000 | 100mbps</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-gray-700 font-bold mb-2">Profile Image</label>
                <input type="file" name="image" id="image" class="border rounded w-full py-2 px-4">
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Client</button>
                <a href="index.php" class="text-blue-500 hover:underline">Back to List</a>
            </div>
        </form>
    </div>
</body>

</html>
