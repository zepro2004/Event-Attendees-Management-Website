<?php
// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'database/db_connect.php';

// Test password
$test_password = 'Password123';

// 1. Generate a new hash for our test password
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "<h3>Password Testing</h3>";
echo "New hash generated for 'Password123': " . $new_hash . "<br><br>";

// 2. Check if our test hash from the SQL script works
$test_hash = '$2y$10$i8r5.W4eYy9nUNyFXXu8TuVE8h8OvDyRjlock6AXb0FUiOjw5vhXW';
$verification = password_verify($test_password, $test_hash);
echo "Test hash verification result: " . ($verification ? "SUCCESS" : "FAIL") . "<br><br>";

// 3. Check existing users in the database
echo "<h3>Database User Verification</h3>";
$result = $mysqli->query("SELECT id, username, password FROM users LIMIT 5");

echo "<table border='1'>
      <tr>
        <th>User ID</th>
        <th>Username</th>
        <th>Hash Works?</th>
        <th>Updated Hash</th>
      </tr>";

while ($user = $result->fetch_assoc()) {
    $hash_works = password_verify($test_password, $user['password']);
    echo "<tr>
            <td>{$user['id']}</td>
            <td>{$user['username']}</td>
            <td>" . ($hash_works ? "YES" : "NO") . "</td>
            <td>";
    
    // 4. For any user where the hash doesn't work, show a SQL statement to fix
    if (!$hash_works) {
        echo "UPDATE users SET password = '$new_hash' WHERE id = {$user['id']};";
    } else {
        echo "Hash is good";
    }
    
    echo "</td></tr>";
}
echo "</table><br>";

// 5. Provide a form to update all passwords at once if needed
echo "<h3>Fix All Passwords</h3>";
echo "<p>If most hashes are failing verification, use this button to reset all passwords to 'Password123' with proper hashing:</p>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='update_all_passwords'>";
echo "<button type='submit'>Update All Passwords</button>";
echo "</form>";

// 6. Process the form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_all_passwords') {
    // Update all users with a new hash for 'Password123'
    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE users SET password = ?");
    $stmt->bind_param("s", $new_hash);
    
    if ($stmt->execute()) {
        $affected = $mysqli->affected_rows;
        echo "<div style='color: green; margin-top: 15px;'>Successfully updated passwords for $affected users!</div>";
    } else {
        echo "<div style='color: red; margin-top: 15px;'>Failed to update passwords: " . $mysqli->error . "</div>";
    }
    
    $stmt->close();
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { padding: 8px; text-align: left; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    h3 { margin-top: 30px; }
    button { padding: 10px 15px; cursor: pointer; }
</style>