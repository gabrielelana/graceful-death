<?php

require __DIR__ . "/../vendor/autoload.php";

GracefulDeath::around(function() {
    try {
        error_reporting(E_ALL ^ E_ERROR);
        // Instance an unknown class cause a fatal error
        new UnknownClass();
    } catch(Exception $e) {
        echo "You cannot catch this, AHAHAHAHA!!!\n";
    }
})
->afterViolentDeath("Yes, I can ;-)\n")
->run();
