<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunUrlRecognizerAkamaiG2O extends VidiunUrlRecognizer {
	
	/**
	 * headerData
	 *
	 * @var string
	 */
	 public $headerData;
	
	/**
	 * headerSign
	 *
	 * @var string
	 */
	 public $headerSign;
	
	/**
	 * timeout
	 *
	 * @var int
	 */
	 public $timeout;
	
	/**
	 * salt
	 *
	 * @var string
	 */
	 public $salt;
	 
	 
	 private static $map_between_objects = array
	 (
	 		"headerData",
	 		"headerSign",
	 		"timeout",
	 		"salt"
	 );
	 
	 public function getMapBetweenObjects ( )
	 {
	 	return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	 }
	 
	 public function toObject($dbObject = null, $skip = array())
	 {
	 	if (is_null($dbObject))
	 		$dbObject = new vUrlRecognizerAkamaiG2O();
	 		
	 	parent::toObject($dbObject, $skip);
	 
	 	return $dbObject;
	 }
}
