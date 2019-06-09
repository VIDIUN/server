<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunReportExportJobData extends VidiunJobData
{
	/**
	 * @var string
	 */
	public $recipientEmail;

	/**
	 * @var VidiunReportExportItemArray
	 */
	public $reportItems;

	/**
	 * @var string
	 */
	public $filePaths;
	
	public $timeZoneOffset;
	
	public $timeReference;

	private static $map_between_objects = array
	(
		"recipientEmail",
		"reportItems",
		"filePaths",
		"timeZoneOffset",
		"timeReference",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function toObject($jobData = null, $props_to_skip = array())
	{
		if (!$jobData)
		{
			$jobData = new vReportExportJobData();
		}

		$jobData->setReportItems($this->reportItems);

		return parent::toObject($jobData, $props_to_skip);
	}

}
