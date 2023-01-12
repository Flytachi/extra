<?php

namespace Extra\Src;

/**
 *  Warframe collection
 * 
 *  ModelInterface from:
 *  * Model Class - private mode
 *  * ModelEasy Class - public mode
 * 
 *  @version 1.0
 *  @author itachi
 *  @package Extra\Src
 */
interface ModelInterface
{
    /**
     * Model Class to string format
     * 
     * @return string
     */
    function __toString(): string;
    /**
     * Model constructor
     */
    public function __construct(?array $data = null);
    /**
     * Construct Array data to Model data
     * 
     * @param ?array $data
     * 
     * @return void
     * 
     * @throws TypeError error data property
     */
    public function reConstruct(?array $data = null): void;
}
