<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunFileExistsResponse extends VidiunObject 
{
	/**
	 * Indicates if the file exists
	 * 
	 * @var bool
	 */
	public $exists;
	
	
	/**
	 * Indicates if the file size is right
	 * 
	 * @var bool
	 */
	public $sizeOk;
}