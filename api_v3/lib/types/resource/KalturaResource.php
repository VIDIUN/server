<?php
/**
 * Used to ingest entry object, as single resource or list of resources accompanied by asset params ids.
 * 
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunResource extends VidiunObject 
{
	public function validateEntry(entry $dbEntry, $validateLocalExist = false)
	{
		
	}
	
	public function entryHandled(entry $dbEntry)
	{
		
	}

}