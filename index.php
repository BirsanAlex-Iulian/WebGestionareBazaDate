<?php
// Start session
session_start();

// Database connection variables
$db_host = "localhost";
$db_name = "studeldist2";
$db_user = "ele_user";
$db_pass = "xFo9YY-I_qXX193C";

// Initialize variables
$email = "";
$password = "";
$error = "";
unset($_SESSION['email']);
unset($_SESSION['password']);


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Connect to MySQL database using PDO
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQL query to check if the email and password match a record in the "user" table
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email AND password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['email'] = $email;
            header("Location: getlist.php");
            exit;
        } else {
            $error = "Email sau parola invalidă.";
            $password = "";
        }
    } catch(PDOException $e) {
        $error = "Connection failed: " . $e->getMessage();
    }

    // Close connection
    $pdo = null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        form {
            margin: 50px auto;
            width: 300px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="email"],
        input[type="password"],
        input[type="submit"],
        input[type="button"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="email">E-mail:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
    <label for="password">Parolă:</label>
    <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
    <input type="submit" value="Conectare">
</form>

<?php
if (!empty($error)) {
    echo "<p>$error</p>";
}
?>

</body>
</html>