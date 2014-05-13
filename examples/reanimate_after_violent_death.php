<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// I will live forever!!!
// I will live forever!!!
// I will live forever!!!
// ...
// I will live forever!!!
// I will live forever!!!
// Maybe not... :-(

$startedAt = time();
GracefulDeath::around(function() {
    echo "I will live forever!!!\n";
    // Let's pretend to do something useful :-)
    usleep(50000);
    // Avoid to print the error in order to have clean output, don't try this at home :-)
    error_reporting(E_ALL ^ E_ERROR);
    // Creating an instance of an unknown class will cause a fatal error
    new UnknownClass();
})
->reanimationPolicy(function($status, $attempts, $output) use($startedAt) {
    return (time() - $startedAt) < 5;
})
->afterViolentDeath("Maybe not... :-(\n")
->run();
