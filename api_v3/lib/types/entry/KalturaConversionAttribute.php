<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunConversionAttribute extends VidiunObject
{
	/**
	 * The id of the flavor params, set to null for source flavor
	 * 
	 * @var int
	 */
	public $flavorParamsId;
	
	/**
	 * Attribute name  
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * Attribute value  
	 * 
	 * @var string
	 */
	public $value;
}
