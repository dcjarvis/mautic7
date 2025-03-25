<?php
// Custom autoloading logic
spl_autoload_register(function($class) {
    $prefixes = [
        'Tests\\Support\\' => __DIR__ . '/',
        'Helper\\' => __DIR__ . '/Helper/',
        'Acceptance\\Helper' => __DIR__ . '/Helper/'
    ];
    
    // Debug: Print out the class being loaded
    error_log("Attempting to autoload class: " . $class);
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // Debug: Print out the potential file path
        error_log("Potential file path: " . $file);
        
        if (file_exists($file)) {
            // Debug: Confirm file exists
            error_log("Loading file: " . $file);
            require $file;
            return;
        }
    }
    
    // Debug: If no file found
    error_log("Could not find file for class: " . $class);
});