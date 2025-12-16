<?php
// gen_pass.php
echo "<h3>Generated Hashes (Copy these into your SQL)</h3>";

$admin_pass = password_hash('admin', PASSWORD_DEFAULT);
$user_pass = password_hash('user', PASSWORD_DEFAULT);
$common_pass = password_hash('1234', PASSWORD_DEFAULT);

echo "<strong>admin:</strong> " . $admin_pass . "<br><br>";
echo "<strong>user:</strong> " . $user_pass . "<br><br>";
echo "<strong>1234:</strong> " . $common_pass . "<br><br>";
?>