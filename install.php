<?php
// Restaurant Finder Installation Script
// Run this file once to set up the database and initial configuration

require_once 'config/database.php';

echo "<h1>Restaurant Finder - Installation</h1>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p class='success'>✓ Database connection successful!</p>";
        
        // Check if tables exist
        $tables = ['admins', 'restaurants', 'menu_categories', 'menu_items', 'restaurant_images'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            echo "<p class='success'>✓ All database tables exist!</p>";
        } else {
            echo "<p class='error'>✗ Missing tables: " . implode(', ', $missing_tables) . "</p>";
            echo "<p class='info'>Please import the database/schema.sql file in phpMyAdmin.</p>";
        }
        
        // Check admin user
        $query = "SELECT COUNT(*) as count FROM admins";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "<p class='success'>✓ Admin user exists!</p>";
        } else {
            echo "<p class='error'>✗ No admin user found. Please import the database schema.</p>";
        }
        
        // Check sample data
        $query = "SELECT COUNT(*) as count FROM restaurants";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "<p class='success'>✓ Sample restaurants found!</p>";
        } else {
            echo "<p class='info'>ℹ No sample restaurants found. You can add restaurants through the admin panel.</p>";
        }
        
    } else {
        echo "<p class='error'>✗ Database connection failed!</p>";
        echo "<p class='info'>Please check your database configuration in config/database.php</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h2>File System Check</h2>";

$directories = ['uploads', 'uploads/restaurants', 'uploads/menu'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p class='success'>✓ Directory '$dir' exists and is writable</p>";
        } else {
            echo "<p class='error'>✗ Directory '$dir' exists but is not writable</p>";
        }
    } else {
        echo "<p class='error'>✗ Directory '$dir' does not exist</p>";
    }
}

// Check PHP version
echo "<h2>PHP Configuration</h2>";
echo "<p class='info'>PHP Version: " . PHP_VERSION . "</p>";

if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<p class='success'>✓ PHP version is compatible (7.4+)</p>";
} else {
    echo "<p class='error'>✗ PHP version is too old. Please upgrade to PHP 7.4 or higher.</p>";
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ Extension '$ext' is loaded</p>";
    } else {
        echo "<p class='error'>✗ Extension '$ext' is not loaded</p>";
    }
}

echo "<h2>Installation Complete!</h2>";
echo "<p class='info'>If all checks passed, your Restaurant Finder application is ready to use!</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li><a href='index.php'>Visit the frontend</a></li>";
echo "<li><a href='admin/login.php'>Access admin panel</a> (admin/admin123)</li>";
echo "<li>Delete this install.php file for security</li>";
echo "</ul>";

echo "<h3>Default Admin Credentials:</h3>";
echo "<p><strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123</p>";

echo "<p class='info'><strong>Important:</strong> Please change the default admin password after first login!</p>";
?>
