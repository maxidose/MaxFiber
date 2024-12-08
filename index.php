<?php
require "db_connect.php";

// Error handling for database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $records_per_page;

$sql_count = "SELECT COUNT(id) AS total FROM clients";
$result_count = $conn->query($sql_count);

if ($result_count && $row = $result_count->fetch_assoc()) {
    $total_records = $row['total'];
    $total_pages = ceil($total_records / $records_per_page);
} else {
    $total_records = 0;
    $total_pages = 0;
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($search) {
    // Updated query to search in multiple fields: fullName, location, plan, and contact
    $sql = "SELECT * FROM clients
            WHERE fullName LIKE '%$search%' 
               OR location LIKE '%$search%' 
               OR contact LIKE '%$search%' 
               OR plan LIKE '%$search%'
            LIMIT $start, $records_per_page";

    $sql_count = "SELECT COUNT(id) AS total FROM clients
                  WHERE fullName LIKE '%$search%' 
                     OR location LIKE '%$search%' 
                     OR contact LIKE '%$search%' 
                     OR plan LIKE '%$search%'";
    $result_count = $conn->query($sql_count);
    if ($result_count && $row = $result_count->fetch_assoc()) {
        $total_records = $row['total'];
        $total_pages = ceil($total_records / $records_per_page);
    }
} else {
    $sql = "SELECT * FROM clients
            LIMIT $start, $records_per_page";
}

$result = $conn->query($sql);

$sql_suggestions = "SELECT fullName, location, contact, plan FROM clients";
$result_suggestions = $conn->query($sql_suggestions);
$suggestions = [];
if ($result_suggestions->num_rows > 0) {
    while ($row = $result_suggestions->fetch_assoc()) {
        $suggestions[] = $row['fullName'];
        $suggestions[] = $row['location'];
        $suggestions[] = $row['contact'];
        $suggestions[] = $row['plan'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Max Fiber Client List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleMenu() {
            const sidePanel = document.getElementById('side-panel');
            sidePanel.classList.toggle('-translate-x-full');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            const suggestionsContainer = document.getElementById('suggestions-container');

            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase();
                const suggestions = <?= json_encode($suggestions); ?>;
                suggestionsContainer.innerHTML = '';

                if (query) {
                    const filteredSuggestions = suggestions.filter(suggestion =>
                        suggestion.toLowerCase().includes(query)
                    );

                    filteredSuggestions.forEach(suggestion => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.textContent = suggestion;
                        suggestionItem.className = 'px-4 py-2 cursor-pointer hover:bg-gray-200';
                        suggestionItem.addEventListener('click', () => {
                            searchInput.value = suggestion;
                            suggestionsContainer.innerHTML = '';
                        });
                        suggestionsContainer.appendChild(suggestionItem);
                    });
                }
            });
        });
    </script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <div id="side-panel" class="bg-blue-900 text-white w-64 p-6 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50">
            <div class="flex flex-col items-center mb-6">
                <img src="uploads/logo.png" alt="Max Fiber Logo" class="w-48 h-auto">
            </div>
            <nav>
                <ul>
                    <li class="mb-4">
                        <a href="index.php" class="hover:text-blue-300">Client List</a>
                    </li>
                    <li class="mb-4">
                        <a href="create.php" class="hover:text-blue-300">Add Client</a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="flex-1 p-6 ml-0 md:ml-64">
            <button onclick="toggleMenu()" class="md:hidden bg-blue-500 text-white p-2 rounded focus:outline-none mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>

            <h1 class="text-3xl font-bold mb-6">Client List</h1>

            <div class="flex justify-between items-center mb-6">
                <form action="index.php" method="get" class="flex relative">
                    <input type="text" id="search-input" name="search" placeholder="Search" autocomplete="off" class="border rounded-l px-4 py-2 w-64">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">Search</button>
                    <div id="suggestions-container" class="absolute bg-white border rounded shadow-md w-64 mt-10 z-10"></div>
                </form>
                <a href="create.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add Client</a>
            </div>

            <div class="bg-white rounded shadow">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border px-4 py-2 text-left">ID</th>
                            <th class="border px-4 py-2 text-left">Full Name</th>
                            <th class="border px-4 py-2 text-left">Location</th>
                            <th class="border px-4 py-2 text-left">Contact</th>
                            <th class="border px-4 py-2 text-left">Plan</th>
                            <th class="border px-4 py-2 text-left">Profile</th>
                            <th class="border px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $count = 1 + $start;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-100'>
                                    <td class='border px-4 py-2'>" . $count++ . "</td>
                                    <td class='border px-4 py-2'>" . htmlspecialchars($row['fullName']) . "</td>
                                    <td class='border px-4 py-2'>" . htmlspecialchars($row['location']) . "</td>
                                    <td class='border px-4 py-2'>" . htmlspecialchars($row['contact']) . "</td>
                                    <td class='border px-4 py-2'>" . htmlspecialchars($row['plan']) . "</td>
                                    <td class='border px-4 py-2'><img src='" . htmlspecialchars($row['image']) . "' alt='Profile Image' class='h-16 w-16 object-cover rounded'></td>
                                    <td class='border px-4 py-2'>
                                        <a href='edit.php?id=" . $row['id'] . "' class='text-blue-500 hover:underline'>Edit</a> | 
                                        <a href='delete.php?id=" . $row['id'] . "' class='text-red-500 hover:underline' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='7' class='border px-4 py-2 text-center'>No results found.</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <nav class="flex justify-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <a href="?page=<?= $i ?>" class="px-4 py-2 mx-1 border rounded <?= ($page == $i) ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>
