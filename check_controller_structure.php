<?php

// Simple script to check the structure of the AppointmentController

// Include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Get the controller file path
$controllerPath = __DIR__ . '/Modules/Appointment/Http/Controllers/AppointmentController.php';

// Check if the file exists
if (!file_exists($controllerPath)) {
    echo "ERROR: Controller file not found at: $controllerPath\n";
    exit(1);
}

// Read the file content
$content = file_get_contents($controllerPath);

// Check for update method
if (preg_match('/public\s+function\s+update\s*\(\s*Request\s+\$request\s*,\s*\$id\s*\)/i', $content, $matches)) {
    echo "✓ Found update method declaration\n";
} else {
    echo "✗ Could not find update method declaration\n";
}

// Check for proper method structure
$updateMethodCount = substr_count($content, 'public function update');
echo "Number of update method declarations: $updateMethodCount\n";

$destroyMethodCount = substr_count($content, 'public function destroy');
echo "Number of destroy method declarations: $destroyMethodCount\n";

// Check for nested methods (a common error)
if (preg_match('/function\s+[a-zA-Z0-9_]+\s*\([^)]*\)\s*{[^{}]*function\s+/i', $content)) {
    echo "✗ WARNING: Found nested function declarations - this is a syntax error\n";
} else {
    echo "✓ No nested function declarations found\n";
}

// Check for mismatched braces in the update method
$updateStart = strpos($content, 'public function update');
if ($updateStart !== false) {
    $updateContent = substr($content, $updateStart, 5000); // Get a chunk of content starting from the update method
    
    $openBraces = substr_count($updateContent, '{');
    $closeBraces = substr_count($updateContent, '}');
    
    echo "In update method and surrounding code:\n";
    echo "- Open braces: $openBraces\n";
    echo "- Close braces: $closeBraces\n";
    
    if ($openBraces == $closeBraces) {
        echo "✓ Braces are balanced\n";
    } else {
        echo "✗ Braces are not balanced\n";
    }
}

echo "\nController structure check completed.\n";
