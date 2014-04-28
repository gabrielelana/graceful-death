<?php

require __DIR__ . "/../../vendor/autoload.php";

GracefulDeath::around(function() {
    echo 'THIS SHOULD NOT BE PRINTED';
})
->doNotEchoOutput()
->run();
