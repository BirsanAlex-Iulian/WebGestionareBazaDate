<?php
// Start session
require_once 'auth_check.php';

// Check if the user is not logged in
if (!isset($_SESSION['email'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit;
}

// Check if the user wants to logout
if (isset($_GET['logout'])) {
    // If logout parameter is set, destroy the session
    session_destroy();
    // Redirect to the login page to clear URL parameters
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de tabele</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <style>
        html {
            font-family: Tahoma, Geneva, sans-serif;
            padding: 0;
            margin: 0;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
            position: relative;
        }
        .container {
            text-align: center;
        }
        .button {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        table {
            border-collapse: collapse;
            width: 500px;
            margin: 0 auto;
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
            text-decoration: none;
            padding: 10px;
            color: #ffffff;
            font-weight: bold;
            font-size: 13px;
        }
        th a i {
            margin-left: 5px;
            color: rgba(255, 255, 255, 0.4);
        }
        td, th {
            padding: 15px; /* Increased padding */
            border: 1px solid #dddfe1;
        }
        tr {
            background-color: #ffffff;
        }
    </style>
</head>
<body>


<button onclick="location.href='index.php'; logout();" type="button" class="button">Deconectare</button>

<div class="container">
    <h2>Lista de tabele</h2>

    <?php
    // Database connection variables
    $db_host = "localhost";
    $db_name = "studeldist2";
    $db_user = "ele_user";
    $db_pass = "xFo9YY-I_qXX193C";

    try {
        // Connect to MySQL database using PDO
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get list of tables from the database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            // Output list of tables in an HTML table
            echo "<table>";
            echo "<thead><tr></tr></thead>";
            echo "<tbody>";
            foreach ($tables as $table) {
                // Checks if the user has admin privileges
                if ($table !== 'user' || strpos($_SESSION['email'], "@student.tuiasi.ro") === false) {
                    echo "<tr><td><a href=\"show_table.php?table=$table\">$table</a></td></tr>";
                }
            }
            echo "</tbody></table>";
        } else {
            echo "No tables found in the database.";
        }

    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

    // Close connection
    $pdo = null;
    ?>
</div>

</body>
</html>
