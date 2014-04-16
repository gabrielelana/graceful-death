<?php

require __DIR__ . "/../vendor/autoload.php";

$signalsToCatch = [SIGTERM, SIGQUIT, SIGINT];

GracefulDeath::around(function() use($signalsToCatch) {
    $askedToStop = false;

    foreach ($signalsToCatch as $signal) {
        pcntl_signal($signal, function($signal) use(&$askedToStop) {
            echo "Politely asked to stop\n";
            $askedToStop = true;
        });
    }

    echo "CTRL-C to terminate\n";

    while (!$askedToStop) {
        echo "I will live forever!!!\n";
        // Let's pretend to do something useful :-)
        sleep(1);
        // This needs to be called to dispatch the signals received by the current
        // process. The PHP process doesn't do that by default, if you have some piece
        // of code that it's executed periodically then this is the best method. As
        // an alternative you can use `declare(ticks=1);`
        pcntl_signal_dispatch();
    }
})
->doNotCaptureOutput()
// This is needed otherwise the signal will terminate the parent process and
// the `afterDeath` code will not be executed
->catchAndIgnoreSignals($signalsToCatch)
->afterNaturalDeath("Maybe not... :-(\n")
->run();
