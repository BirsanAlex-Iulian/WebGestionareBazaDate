<!-- language: lang-html -->
<?php
// Database connection variables
$db_host = "localhost";
$db_name = "studeldist2";
$db_user = "ele_user";
$db_pass = "xFo9YY-I_qXX193C";

try {
    // Conectarea la baza de date MySQL
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Recuperarea numelui tabelului din URL
    $table_name = $_GET['table'];
    // Variabile temporare pentru interogare
    $columns = implode(", ", array_keys($_POST));
    $placeholders = ":" . implode(", :", array_keys($_POST));
    // Executăm interogarea pentru a adăuga o înregistrare nouă
    $sql = "INSERT INTO $table_name ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($_POST);

    // Redirect back to show_table.php after successful insertion
    header("Location: show_table.php?table=$table_name");
    exit;
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
