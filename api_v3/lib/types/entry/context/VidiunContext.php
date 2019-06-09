<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunContext extends VidiunObject
{
    /**
     * Function to validate the context.
     */
    abstract protected function validate ();
}