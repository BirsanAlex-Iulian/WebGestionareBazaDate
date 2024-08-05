<!DOCTYPE html>
<html>
<head>
    <title>Continut tabel</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <style>
        html, body {
            font-family: Tahoma, Geneva, sans-serif;
            padding: 0;
            margin: 0;
            height: 100%;
        }
        table {
            border-collapse: collapse;
            width: 70%;
            float: left;
        }
        th {
            background-color: #54585d;
            color: #ffffff;
            border: 1px solid #54585d;
            cursor: pointer; /* Add cursor pointer for sorting */
        }
        th:hover {
            background-color: #64686e;
        }
        th a {
            display: block;
            text-decoration:none;
            padding: 5px;
            color: #ffffff;
            font-weight: bold;
            font-size: 14px;
        }
        th a i {
            margin-left: 5px;
            color: rgba(255,255,255,0.4);
        }
        td,
        th {
            padding: 10px;
            color: #636363;
            border: 1px solid #dddfe1;
        }
        tr {
            background-color: #ffffff;
        }
        /* Sticky navigation bar styles */
        .navbar {
            overflow: hidden;
            background-color: #333;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }
        
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        /* Button styles */
        .download-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        .download-btn:hover {
            background-color: #45a049;
        }

        /* Form styles */
        .add-form {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 300px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .add-form input {
            width: calc(100% - 20px);
            padding: 8px;
            margin-bottom: 10px;
        }

        .add-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-form input[type="submit"]:hover {
            background-color: #45a049;
        }


        /* Message styles */
        .message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }

        .message.success {
            color: #4CAF50;
        }

        .message.error {
            color: #F44336;
        }
    </style>
</head>
<body>

<div class="navbar" id="navbar">
    <a href="getlist.php">Acasă</a>
    <a href="index.php" onclick="logout()">Deconectare</a>
</div>  

<?php
// Start session
require_once 'auth_check.php';

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

// Check if the user wants to logout
if (isset($_GET['logout'])) {
    // If logout parameter is set, destroy the session
    session_destroy();
    header("Location: index.php");
    exit;
}

// Database connection variables
$db_host = "localhost";
$db_name = "studeldist2";
$db_user = "ele_user";
$db_pass = "xFo9YY-I_qXX193C";

$is_student = (strpos($_SESSION['email'], "@student.tuiasi.ro") !== false);

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $table_name = $_GET['table'];

    $stmt = $pdo->prepare("DESCRIBE $table_name");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get the column to sort and the sort order from the URL parameters
    $sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : null;
    $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : null;

$sql = "SELECT * FROM $table_name";

$search_conditions = [];
foreach ($columns as $column) {
    if (!empty($_GET[$column])) {
        $search_conditions[] = "$column LIKE :$column";
    }
}

if (!empty($search_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $search_conditions);
}

// Add sorting to the SQL query if sort column and sort order are provided
if ($sort_column && $sort_order) {
    $sql .= " ORDER BY ";
    switch ($sort_order) {
        case 'ASC':
            $sql .= "CASE WHEN $sort_column IS NULL THEN 1 
                      WHEN $sort_column REGEXP '^[0-9]+$' THEN 2 
                      ELSE 3 END,
                      $sort_column ASC, " . $columns[0] . " ASC";
            break;
        case 'DESC':
            $sql .= "CASE WHEN $sort_column IS NULL THEN 3 
                      WHEN $sort_column REGEXP '^[0-9]+$' THEN 2 
                      ELSE 1 END,
                      $sort_column DESC, " . $columns[0] . " DESC";
            break;
        default:
            $sql .= "$sort_column, " . $columns[0] . " ASC";
            break;
    }
}

$stmt = $pdo->prepare($sql);

// Bind search parameters
foreach ($columns as $column) {
    if (!empty($_GET[$column])) {
        $stmt->bindValue(":$column", '%' . $_GET[$column] . '%');
    }
}

$stmt->execute();


            // Search form to filter table contents
        echo '<div class="search-form" style="padding-left: 20px;">';
        echo '<h3>Căutare înregistrare</h3>';
        echo '<form action="" method="GET">';
        echo '<input type="hidden" name="table" value="' . $table_name . '">';

            // Add hidden inputs for sorting parameters
        if ($sort_column && $sort_order) {
            echo '<input type="hidden" name="sort_column" value="' . $sort_column . '">';
            echo '<input type="hidden" name="sort_order" value="' . $sort_order . '">';
        }

        // Textboxes
        foreach ($columns as $column) {
            $value = isset($_GET[$column]) ? htmlspecialchars($_GET[$column]) : '';
            echo "<div style=\"margin-bottom: 10px;\">";
            echo "<label for=\"$column\">" . ucfirst($column) . ":</label>";
            echo "<input type=\"text\" name=\"$column\" id=\"$column\" value=\"$value\" style=\"margin-left: 5px;\">";
            echo "</div>";
        }

        echo '<input type="submit" value="Caută">';
        echo '</form>';
        echo '</div>';

            // Form to add new value
            // Check if the user's email is from a student
                $user_email = $_SESSION['email'];
                if (substr($user_email, -18) !== "@student.tuiasi.ro") {
        echo '<div class="add-form">';
        echo '<h3>Adăugare înregistrare</h3>';
        echo '<form action="" method="POST">';
        echo '<input type="hidden" name="table" value="' . $table_name . '">';

        foreach ($columns as $index => $column) {
            // Skip primary key
            if ($index === 0) {
                continue;
            }
            echo "<label for=\"$column\">" . ucfirst($column) . ":</label>";
            echo "<input type=\"text\" name=\"$column\" id=\"$column\"><br>";
        }

        echo '<input type="submit" name="submit" value="Trimiteți">';
        echo '</form>';
        echo '</div>';

// Check if the form is submitted
if (isset($_POST['submit'])) {
    try {
        // INSERT query
        $insert_query = "INSERT INTO $table_name (";
        $insert_query .= implode(', ', array_slice($columns, 1)) . ") VALUES (";

        $insert_values = [];
        foreach (array_slice($columns, 1) as $column) {
            $insert_values[] = ":$column";
        }
        $insert_query .= implode(', ', $insert_values) . ")";

        $insert_stmt = $pdo->prepare($insert_query);

        foreach (array_slice($columns, 1) as $column) {
            $insert_stmt->bindValue(":$column", $_POST[$column]);
        }

        if ($insert_stmt->execute()) {
            echo '<script>window.location.href = "show_table.php?table=' . $table_name . '";</script>';
            exit;
        } else {
            echo '<p style="text-align: center; color: red;">Înregistrarea nu a putut fi adăugată</p>';
        }
    } catch(PDOException $e) {
        echo '<p style="text-align: center; color: red;">Înregistrarea nu a putut fi adăugată</p>';
    }

}

        }

      // Button to download table as Excel file

      echo '<button class="download-btn" onclick="downloadExcel()">Descărcați Excel</button>';
      echo '<div style="margin-top: 20px;"></div>';

      if ($stmt->rowCount() > 0) {
        echo "<h2 style=\"text-align: center;\">{$table_name}</h2>";

        echo "<table>";
    echo "<thead><tr>";
    foreach ($columns as $column) {
        // Determine the sort order
        $next_order = ($sort_column === $column && $sort_order === 'ASC') ? 'DESC' : 'ASC';
        echo "<th><a href=\"?table=$table_name&sort_column=$column&sort_order=$next_order";

        foreach ($columns as $search_column) {
            if (!empty($_GET[$search_column])) {
                echo "&$search_column=" . urlencode($_GET[$search_column]);
            }
        }
        echo "\">$column";
        if ($sort_column === $column) {
            echo ' <i class="fas fa-sort-' . strtolower($sort_order) . '"></i>';
        } else {
            echo ' <i class="fas fa-sort"></i>';
        }
        echo "</a></th>";
    }
    echo "</tr></thead>";

    echo "<tbody>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<td>" . htmlspecialchars($row[$column]) . "</td>";
        }
		if (strpos($_SESSION['email'], "@student.tuiasi.ro") == false) {
			echo "<td><a href=\"edit_value.php?table=$table_name&id={$row[$columns[0]]}\">Editați</a></td>";
			echo ' <td><a href="delete_value.php?table=' . $table_name . '&id=' . $row[$columns[0]] . '" onclick="return confirm(\'Sigur doriți să ștergeți înregistrarea?\');">Ștergeți</a></td>';
		}
        echo "</tr>";
    }
    echo "</tbody></table>";
    } else {
        echo "Nu au fost găsite înregistrări în tabel.";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Close connection
$pdo = null;
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
<script>
    function downloadExcel() {
        const table = document.querySelector('table');
        const clonedTable = table.cloneNode(true);

        <?php if (!$is_student): ?>
        const rows = clonedTable.querySelectorAll('tr');
        rows.forEach((row, rowIndex) => {
        // Skip the header row and the last two columns which have the edit and delete buttons
            if (rowIndex === 0) {
                
                return;
            }
            row.removeChild(row.lastElementChild);
            row.removeChild(row.lastElementChild);
        });
        <?php endif; ?>

        const ws = XLSX.utils.table_to_sheet(clonedTable);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Table Data");
        XLSX.writeFile(wb, "table_data.xlsx");
    }
</script>


</body>
</html>
