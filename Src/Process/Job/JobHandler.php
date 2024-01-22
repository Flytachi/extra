<?php

namespace Extra\Src\Process\Job;

use Extra\Src\Log\Log;

trait JobHandler
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
        Log::alert(static::class . ' INTERRUPTED');
    }

    protected function asTermination(): void
    {
        Log::critical(static::class . ' TERMINATION');
    }

    protected function asClose(): void
    {
        Log::critical(static::class . ' CLOSE TERMINAL');
    }
}