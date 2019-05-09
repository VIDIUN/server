<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUploadResponse extends VidiunObject
{
	/**
	 * @var string
	 */
	public $uploadTokenId;

	/**
	 * @var int
	 */
	public $fileSize;
	
	/**
	 * 
	 * @var VidiunUploadErrorCode
	 */
	public $errorCode;
	
	/**
	 * 
	 * @var string
	 */
	public $errorDescription;
	
}