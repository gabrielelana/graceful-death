<?php

require __DIR__ . "/../vendor/autoload.php";

// The output will be something like:
//
// CTRL-C to terminate
// [17497]: it's using 473.77(kb) of memory
// [17497]: it's using 1.46(mb) of memory
// [17497]: it's using 2.46(mb) of memory
// [17497]: it's using 3.46(mb) of memory
// [17497]: it's using 4.46(mb) of memory
// [17497]: it's using 5.46(mb) of memory
// [17497]: it's using 6.46(mb) of memory
// [17497]: it's using 7.46(mb) of memory
// [17497]: it's using 8.46(mb) of memory
// [17497]: it's using 9.46(mb) of memory
// [17497]: it's using 10.46(mb) of memory
// [17497]: memory limit reached! respawn
// [17554]: it's using 475.17(kb) of memory
// [17554]: it's using 1.46(mb) of memory
// [17554]: it's using 2.46(mb) of memory
// [17554]: it's using 3.46(mb) of memory
// [17554]: it's using 4.46(mb) of memory
// [17554]: it's using 5.47(mb) of memory
// [17554]: it's using 6.47(mb) of memory
// ^C

echo "CTRL-C to terminate\n";

GracefulDeath::around(function($life) {
    while (!$life->askedToStop()) {
        $usedMemory = memory_get_usage();
        printf("[%d]: it's using %s of memory\n", posix_getpid(), format($usedMemory));
        if ($usedMemory > 10485760) { // 10MB
            printf("[%d]: memory limit reached! respawn\n", posix_getpid());
            exit(5);
        }
        // Let's seize a 1MB of memory
        $aLotOfMemory[] = str_repeat('*', 1048576);
        usleep(1000 * 200);
    }
})
->doNotCaptureOutput()
->catchSignals([SIGTERM, SIGQUIT, SIGINT])
->reanimationPolicy(true)
->run();


function format($size) {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . '(' . $units[$power] . ')';
}
