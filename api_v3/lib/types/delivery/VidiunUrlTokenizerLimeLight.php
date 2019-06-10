<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlTokenizerLimeLight extends VidiunUrlTokenizer {

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new vLimeLightUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}
