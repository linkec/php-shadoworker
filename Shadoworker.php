<?php
class Shadoworker
{
    static $count = 0;
    static public function run($func, $title = null, $quitOnExit = true)
    {
        if (!is_callable($func)) {
            throw new Exception('Function not callable.');
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            throw new Exception('Windows not supported.');
        }
        // Ignore signal , otherwise Child Process will defunct
        pcntl_signal(SIGCLD, SIG_IGN);
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            return false;
        } elseif ($pid > 0) {
            ++self::$count;
            return $pid;
        }
        self::setTitle($title ? $title : 'php ghost');
        $func();
        // Kill progress , exit will call Workerman log service.
        posix_kill(posix_getpid(), \SIGKILL);
    }
    static protected function setTitle($title)
    {
        \set_error_handler(function () {
        });
        // >=php 5.5
        if (\function_exists('cli_set_process_title')) {
            \cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        elseif (\extension_loaded('proctitle') && \function_exists('setproctitle')) {
            \setproctitle($title);
        }
        \restore_error_handler();
    }
}
