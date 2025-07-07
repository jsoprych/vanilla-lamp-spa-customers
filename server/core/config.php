<?php
// Use a path relative to the dist directory when running the server
$distRoot = realpath(__DIR__ . '/../../dist/'); // Resolve to /path/to/project/dist/
return [
    'debug_mode' => true,
    'audit_logging' => true,
    'verbose_read_logging' => false,
    'log_read_to_database' => true,
    'sensitive_fields' => ['password', 'token'],
    'log_path' => $distRoot . '/logs/',
    'error_log_file' => $distRoot . '/logs/php_errors.log'
];