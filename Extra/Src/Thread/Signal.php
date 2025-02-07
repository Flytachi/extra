<?php

namespace Flytachi\Extra\Src\Thread;

final class Signal
{
    /**
     * Sends an interrupt signal to a process with the specified process ID.
     *
     * @param int $pid The process ID to send the interrupt signal to.
     *
     * @return bool Returns true if the interrupt signal was successfully sent, false otherwise.
     */
    public static function interrupt(int $pid): bool
    {
        return posix_kill($pid, SIGINT);
    }

    /**
     * Sends a termination signal to a process with the specified process ID.
     *
     * @param int $pid The process ID to send the termination signal to.
     * @return bool Returns true if the termination signal was successfully sent, false otherwise.
     */
    public static function termination(int $pid): bool
    {
        return posix_kill($pid, SIGTERM);
    }

    /**
     * Sends a hangup signal to a process with the specified process ID.
     *
     * @param int $pid The process ID to send the hangup signal to.
     * @return bool Returns true if the hangup signal was successfully sent, false otherwise.
     */
    public static function close(int $pid): bool
    {
        return posix_kill($pid, SIGHUP);
    }
}
