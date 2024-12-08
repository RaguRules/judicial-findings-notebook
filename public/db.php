<?php
try {
    // Connect to SQLite3 using PDO
    $pdo = new PDO('sqlite:test.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create a table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT)");

    // Insert some test data
    $pdo->exec("INSERT INTO users (name) VALUES ('John Doe')");
    $pdo->exec("INSERT INTO users (name) VALUES ('Jane Smith')");

    // Fetch and display the data
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " - Name: " . $row['name'] . "<br>";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
