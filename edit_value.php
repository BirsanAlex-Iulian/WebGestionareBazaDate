<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare înregistrare</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .message {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #4CAF50;
            color: white;
        }
        .message.error {
            background-color: #4CAF50;
            color: white;
        }
        form {
            margin-top: 20px;
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
        }
        label {
            display: inline-block;
            width: 150px;
            text-align: right;
            margin-right: 10px;
        }
        input[type="text"] {
            padding: 8px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button-container {
            margin-top: 20px;
            text-align: center;
        }
        .back-btn {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .back-btn:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Editare înregistrare</h2>

    <?php
    // Database connection variables
    $db_host = "localhost";
    $db_name = "studeldist2";
    $db_user = "ele_user";
    $db_pass = "xFo9YY-I_qXX193C";
    require_once 'auth_check.php';

    try {
        // Connect to MySQL database using PDO
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if table and id parameters are provided in the URL
        if (isset($_GET['table']) && isset($_GET['id'])) {
            $table_name = $_GET['table'];
            $id = $_GET['id'];

            // Retrieve column names of the table
            $stmt = $pdo->prepare("DESCRIBE $table_name");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Retrieve data for the specific row to display details
            $stmt = $pdo->prepare("SELECT * FROM $table_name WHERE {$columns[0]} = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Display record details
                echo '<div class="message error" id="messageContainer"></div>';
                echo '<form action="edit_value.php?table=' . $table_name . '&id=' . $id . '" method="POST">';
                echo '<input type="hidden" name="table" value="' . $table_name . '">';
                echo '<input type="hidden" name="id" value="' . $id . '">';

                echo '<ul>';
                foreach ($row as $column => $value) {
                    echo '<li>';
                    echo '<label for="' . htmlspecialchars($column) . '">' . htmlspecialchars($column) . ':</label>';
                    echo '<input type="text" id="' . htmlspecialchars($column) . '" name="' . htmlspecialchars($column) . '" value="' . htmlspecialchars($value) . '">';
                    echo '</li>';
                }
                echo '</ul>';

                // Form to confirm update
                echo '<div class="button-container">';
                echo '<input type="submit" name="submit" value="Actualizează" style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; text-decoration: none; border-radius: 4px; cursor: pointer;">';                
                echo '&nbsp;&nbsp;';
                echo '<a href="show_table.php?table=' . $table_name . '" class="back-btn">Anulează</a>';
                echo '</div>';
                echo '</form>';

                // Check if the form is submitted
                if (isset($_POST['submit'])) {
                    // Update record
                    $update_query = "UPDATE $table_name SET ";
                    $update_params = [];
                    foreach ($row as $column => $value) {
                        $update_query .= "`$column` = ?, ";
                        $update_params[] = $_POST[$column];
                    }
                    $update_query = rtrim($update_query, ', ') . " WHERE {$columns[0]} = ?";
                    $update_params[] = $id;

                    $update_stmt = $pdo->prepare($update_query);
                    if ($update_stmt->execute($update_params)) {
                        echo '<script>
                                document.getElementById("messageContainer").innerHTML = "<p class=\"success\">Înregistrarea a fost actualizată cu succes.</p>";
                                setTimeout(function() {
                                    window.location.href = "show_table.php?table=' . $table_name . '";
                                }, 2000); // 2000 milliseconds = 2 seconds
                              </script>';
                    } else {
                        echo '<script>document.getElementById("messageContainer").innerHTML = "<p class=\"error\">Eroare la actualizarea înregistrării.</p>";</script>';
                    }
                }
            } else {
                echo '<p style="text-align: center;">Înregistrarea nu a fost găsită.</p>';
            }
        } else {
            echo '<p style="text-align: center;">Parametrii invalidi.</p>';
        }
    } catch(PDOException $e) {
        $errorCode = $e->errorInfo[1];

        switch ($errorCode) {
            case 1451:
                echo '<p style="text-align: center;">Eroare la actualizarea înregistrării: Există restricții în alte tabele care împiedică actualizarea acestei înregistrări.</p>';
                break;
            case 1062:
                echo '<p style="text-align: center;">Eroare la actualizarea înregistrării: Există deja o înregistrare cu aceste valori unice.</p>';
                break;
            default:
                echo '<p style="text-align: center;">Eroare la conectare sau operare cu baza de date: ' . $e->getMessage() . '</p>';
        }
    }

    // Close connection
    $pdo = null;
    ?>

</div>

</body>
</html>
