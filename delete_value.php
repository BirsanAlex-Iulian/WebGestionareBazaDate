<!DOCTYPE html>
<html>
<head>
    <title>Ștergere înregistrare</title>
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
            background-color: #f44336;
            color: white;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
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
    <h2>Ștergere înregistrare</h2>

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
                echo '<ul>';
                foreach ($row as $column => $value) {
                    echo '<li><strong>' . htmlspecialchars($column) . ':</strong> ' . htmlspecialchars($value) . '</li>';
                }
                echo '</ul>';

                // Form to confirm deletion
                echo '<form action="" method="POST">';
                echo '<input type="hidden" name="table" value="' . $table_name . '">';
                echo '<input type="hidden" name="id" value="' . $id . '">';
                echo '<div class="button-container">';
                echo '<input type="submit" name="submit" value="Șterge" style="background-color: #f44336; color: white; border: none; padding: 10px 20px; text-decoration: none; border-radius: 4px; cursor: pointer;">';
                echo '&nbsp;&nbsp;';
                echo '<a href="show_table.php?table=' . $table_name . '" class="back-btn">Anulează</a>'; // Redirects to show_table.php
                echo '</div>';
                echo '</form>';

                // Check if the form is submitted
                if (isset($_POST['submit'])) {
                    try {
                        // Perform deletion
                        $delete_query = "DELETE FROM $table_name WHERE {$columns[0]} = :id";
                        $delete_stmt = $pdo->prepare($delete_query);
                        $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $delete_stmt->execute();

                        // Check if the deletion was successful
                        if ($delete_stmt->rowCount() > 0) {
                            echo '<script>
                                    document.getElementById("messageContainer").innerHTML = "<p class=\"success\">Înregistrarea a fost ștearsă cu succes.</p>";
                                    setTimeout(function() {
                                        window.location.href = "show_table.php?table=' . $table_name . '";
                                    }, 2000); // 2000 milliseconds = 2 seconds
                                  </script>';
                        } else {
                            echo '<script>document.getElementById("messageContainer").innerHTML = "<p class=\"error\">Eroare la ștergerea înregistrării.</p>";</script>';
                        }
                    } catch (PDOException $e) {
                        $errorCode = $e->errorInfo[1];
                        switch ($errorCode) {
                            case 1451:
                                echo '<p style="text-align: center;">Eroare la ștergerea înregistrării: Există restricții care împiedică ștergerea acestei înregistrări.</p>';
                                break;
                            case 1062:
                                echo '<p style="text-align: center;">Eroare la ștergerea înregistrării: Există deja o înregistrare cu aceste valori unice.</p>';
                                break;
                            default:
                                echo '<p style="text-align: center;">Eroare la conectare sau operare cu baza de date.</p>';
                        }
                    }
                }
            } else {
                echo '<p style="text-align: center;">Înregistrarea nu a fost găsită.</p>';
            }
        } else {
            echo '<p style="text-align: center;">Parametri invalizi.</p>';
        }
    } catch(PDOException $e) {
        echo '<p style="text-align: center;">Eroare la conectare sau operare cu baza de date.</p>';
    }

    // Close connection
    $pdo = null;
    ?>

</div>

</body>
</html>
