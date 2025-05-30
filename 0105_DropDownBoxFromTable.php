<!DOCTYPE html>
<html>
<head>
    <title>Dropdown from Database</title>
</head>
<body>
    <form action="submit.php" method="post">
        <label for="dropdown">Choose an option:</label>
        <select name="dropdown" id="dropdown">
            <?php
            // Database connection parameters
            // $dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8';
            $dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8';
            $username = 'your_username';
            $password = 'your_password';

            try {
                // Create a PDO instance
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Prepare and execute your SQL query
                $sql = "SELECT id, option_name FROM your_table";
                $stmt = $pdo->query($sql);

                // Loop through the results and output each as an option
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $row['id'] . '">' . $row['option_name'] . '</option>';
                }
            } catch (PDOException $e) {
                // Handle any errors
                echo "Error: " . $e->getMessage();
            }
            ?>
        </select>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
