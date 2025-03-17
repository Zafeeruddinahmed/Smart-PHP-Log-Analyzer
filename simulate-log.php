<?php
require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/app.log', Logger::DEBUG));

$logLevels = [
    Logger::INFO => "INFO: Application running smoothly.",
    Logger::WARNING => "WARNING: High memory usage detected.",
    Logger::ERROR => "ERROR: Database connection failed."
];

$previousLevel = null;

while (true) {
    // Pick a log level that is different from the previous one
    do {
        $randomLevel = array_rand($logLevels);
    } while ($randomLevel === $previousLevel);

    $log->log($randomLevel, $logLevels[$randomLevel]);
    echo $logLevels[$randomLevel] . "\n";

    $previousLevel = $randomLevel;
    
    // Wait for 5 seconds before generating the next log entry
    sleep(5);
}