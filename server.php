<?php
$host = '0.0.0.0';
$port = 5000;
$documentRoot = __DIR__ . '/public';

echo "Starting PHP development server on {$host}:{$port}\n";
echo "Document root: {$documentRoot}\n";
echo "Press Ctrl-C to stop the server\n\n";

passthru("php -S {$host}:{$port} -t {$documentRoot}");
