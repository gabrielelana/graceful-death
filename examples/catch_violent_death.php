<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// Yes, I can ;-)

GracefulDeath::around(function() {
    try {
        // Avoid to print the error in order to have clean output, don't try this at home :-)
        error_reporting(E_ALL ^ E_ERROR);
        // Creating an instance of an unknown class will cause a fatal error
        new UnknownClass();
    } catch(Exception $e) {
        // A fatal error is uncatchable
        echo "You cannot catch this, AHAHAHAHA!!!\n";
    }
})
->afterViolentDeath("Yes, I can ;-)\n")
->run();
