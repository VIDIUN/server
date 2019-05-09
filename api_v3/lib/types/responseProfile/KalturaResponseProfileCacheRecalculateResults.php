<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunResponseProfileCacheRecalculateResults extends VidiunObject
{
	/**
	 * Last recalculated id
	 * 
	 * @var string
	 */
	public $lastObjectKey;
	
	/**
	 * Number of recalculated keys
	 * 
	 * @var int
	 */
	public $recalculated;
}