<?php

require __DIR__ . "/../../vendor/autoload.php";

$options = getopt('', ['where:']);

ini_set('error_log', $options['where']);
ini_set('log_errors', 1);

GracefulDeath::around(function() {
    trigger_error('ERROR');
})
->run();
