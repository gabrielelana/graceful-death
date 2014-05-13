<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be:
//
// CTRL-C to terminate
// I will live forever!!!
// I will live forever!!!
// I will live forever!!!
// ...
// Maybe not... :-(

echo "CTRL-C to terminate\n";

GracefulDeath::around(function($life) {
    while (!$life->askedToStop()) {
        echo "I will live forever!!!\n";
        // Let's pretend to do something useful :-)
        sleep(1);
    }
})
->doNotCaptureOutput()
->catchSignals([SIGTERM, SIGQUIT, SIGINT])
->afterNaturalDeath("Maybe not... :-(\n")
->run();
