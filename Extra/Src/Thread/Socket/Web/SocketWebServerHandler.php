<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Thread\Socket\Web;

trait SocketWebServerHandler
{
    /**
     * Signs an interrupt and terminates the execution of the current script.
     *
     * This method is used to handle interruptions in the code execution flow.
     * It performs the necessary actions to process an interrupt and then terminates
     * the script execution.
     *
     * @return never This method does not return any value.
     */
    private function signInterrupt(): never
    {
        $this->asInterrupt();
        $this->endRun();
        exit();
    }

    /**
     * Signs the termination of the program.
     *
     * This method sets the program state as "termination" and ends the program execution.
     * It also sets the exit code to 1.
     *
     * @return never
     */
    private function signTermination(): never
    {
        $this->asTermination();
        $this->endRun();
        exit(1);
    }

    /**
     * Closes the sign and ends the run.
     *
     * @return never
     */
    private function signClose(): never
    {
        $this->asClose();
        $this->endRun();
        exit(1);
    }

    protected function asInterrupt(): void
    {
        $this->socketClose();
        static::$logger->alert("INTERRUPTED");
    }

    protected function asTermination(): void
    {
        $this->socketClose();
        static::$logger->critical("TERMINATION");
    }

    protected function asClose(): void
    {
        $this->socketClose();
        static::$logger->alert("CLOSE");
    }
}