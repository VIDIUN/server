<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerCht extends VidiunUrlTokenizer {

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vChtHttpUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}
