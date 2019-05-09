<?php
/**
 * Is a unified way to add content to Vidiun whether it's an uploaded file, webcam recording, imported URL or existing file sync.
 * 
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunContentResource extends VidiunResource 
{
	public function validateAsset(asset $dbAsset)
	{
	
	}
}