<?php
/**
 * @package plugins.playReady
 * @subpackage api.objects
 */
class VidiunPlayReadyPlayRight extends VidiunPlayReadyRight
{
    /**
	 * @var VidiunPlayReadyAnalogVideoOPL
	 */
	public $analogVideoOPL ;
	
	/**
	 * @var VidiunPlayReadyAnalogVideoOPIdHolderArray
	 */
	public $analogVideoOutputProtectionList ;
	
    /**
	 * @var VidiunPlayReadyDigitalAudioOPL
	 */
	public $compressedDigitalAudioOPL ;
	
    /**
	 * @var VidiunPlayReadyCompressedDigitalVideoOPL
	 */
	public $compressedDigitalVideoOPL ;

	/**
	 * @var VidiunPlayReadyDigitalAudioOPIdHolderArray
	 */
	public $digitalAudioOutputProtectionList; 
	
	/**
	 * @var VidiunPlayReadyDigitalAudioOPL
	 */	
	public $uncompressedDigitalAudioOPL;

    /**
	 * @var VidiunPlayReadyUncompressedDigitalVideoOPL
	 */
	public $uncompressedDigitalVideoOPL; 
	
    /**
	 * @var int
	 */
	public $firstPlayExpiration;
	
    /**
	 * @var VidiunPlayReadyPlayEnablerHolderArray
	 */
	public $playEnablers; 
	
	
	private static $map_between_objects = array(
		'analogVideoOPL',
    	'analogVideoOutputProtectionList',
    	'compressedDigitalAudioOPL',
    	'compressedDigitalVideoOPL',
		'digitalAudioOutputProtectionList',
		'uncompressedDigitalAudioOPL',
		'uncompressedDigitalVideoOPL',
		'firstPlayExpiration',
		'playEnablers',
	 );
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new PlayReadyPlayRight();
			
		parent::toObject($dbObject, $skip);
					
		return $dbObject;
	}
}


