<?php

require __DIR__ . "/../../vendor/autoload.php";

ini_set('display_errors', 0);

GracefulDeath::around(function() {
    trigger_error('ERROR');
})
->run();
