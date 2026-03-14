<?php

// Direct database connection approach
$host = '127.0.0.1';
$dbname = 'ticketing_savanna';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Creating Test Admin User ===\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@test.com']);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "Admin user already exists: admin@test.com\n";
        echo "Updating password...\n";
        
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, 'admin@test.com']);
        
        echo "✅ Password updated for admin@test.com\n";
    } else {
        echo "Creating new admin user...\n";
        
        $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, email_verified_at, team_type_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), 1, NOW(), NOW())");
        $stmt->execute(['Administrator', 'admin@test.com', $hashedPassword]);
        
        echo "✅ Admin user created successfully!\n";
    }
    
    echo "\nLogin credentials:\n";
    echo "Email: admin@test.com\n";
    echo "Password: password\n";
    
    // Show all users
    echo "\n=== All Users ===\n";
    $stmt = $pdo->query("SELECT id, name, email FROM users");
    while ($user = $stmt->fetch()) {
        echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
