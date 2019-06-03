<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunLiveReportExportJobData extends VidiunJobData
{
	/**
	 * @var time
	 */
	public $timeReference; 
	
	/**
	 * @var int
	 */
	public $timeZoneOffset;
	
	/**
	 * @var string
	 */
	public $entryIds;
	
	/**
	 * @var string
	 */
	public $outputPath;
	
	/**
	 * @var string
	 */
	public $recipientEmail;
	
	private static $map_between_objects = array
	(
			"timeReference" ,
			"timeZoneOffset",
			"entryIds" ,
			'outputPath',
			"recipientEmail",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vLiveReportExportJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
